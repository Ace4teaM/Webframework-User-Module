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
 * Déconnect un utilisateur
 * Rôle : Administrateur
 * UC   : disconnect_user
 */

class user_module_disconnect_user_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id');
    public $op_fields = null;

    function main(iApplication $app, $app_path, $p) {

        // résultat retourné
        $result = new cResult();

        // 1,2,3. Supprime la connexion (et la session) utilisateur
        if(!UserModule::disconnectUser($p->user_account_id))
            return false;
        $result = cResult::getLast();
        
        //4. Supprime la tache de fermeture automatique
        /** todo: A FAIRE
        $taskMgr=NULL;
        $app->getTaskMgr($taskMgr);
        if($taskMgr !== null && $p->life_time > 0){
            $taskName = UserModule::disconnectTaskName($p->user_account_id);
            if(!$taskMgr->remove($taskName))
                 return false;
        } */

        return RESULT_INST($result);//UserModule::disconnectUser()
    }
};

?>