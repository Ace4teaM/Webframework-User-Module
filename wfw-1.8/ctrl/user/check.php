<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2012-2013 Thomas AUGUEY <contact@aceteam.org>
    ---------------------------------------------------------------------------------------------------------------------------------------
    This file is part of WebFrameWork.

    WebFrameWork is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebFrameWork is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebFrameWork.  If not, see <http://www.gnu.org/licenses/>.
    ---------------------------------------------------------------------------------------------------------------------------------------
*/

/*
 * Maintient la connexion d'un utilisateur
 * Rôle : Utilisateur
 * UC   : user_check_connection
 */

class Ctrl extends cApplicationCtrl{
    public $fields    = array('user_connection_id');
    public $op_fields = null;

    function main(iApplication $app, $app_path, $p) {

        if(!UserModule::checkConnection($p->user_connection_id,$_SERVER["REMOTE_ADDR"]))
            return false;
        $result = cResult::getLast();

        //actualise la tache de fermeture
        $taskMgr=NULL;
        $app->getTaskMgr($taskMgr);
        if($taskMgr!==NULL){
            $expire  = new DateTime();
            $expire->setTimestamp(intval($result->getAtt("EXPIRE")));
            $current = new DateTime();
            if($expire->getTimestamp() > $current->getTimestamp()){
                $taskName = UserModule::disconnectTaskName($result->getAtt("UID"));
                $taskCmd  = UserModule::disconnectTaskCmd($result->getAtt("UID"));
                if(!$taskMgr->create($taskName,$expire,$taskCmd))
                     return false;
            }
        }

        return RESULT_INST($result); //UserModule::checkConnection
    }
};
?>