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
 * Déconnecte l'utilisateur en cours
 * Rôle : Utilisateur
 * UC   : disconnect
 */

class user_module_disconnect_ctrl extends cApplicationCtrl{
    public $fields    = array('user_connection_id');
    public $op_fields = null;
    public $att       = null;

    function __construct() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {

        // 1. supprime la connexion utilisateur
        if(!UserModule::disconnect($p->user_connection_id))
            return false;
        $user_account_id = cResult::getLast()->getAtt("user_account_id");

        // supprime le cookie
        setcookie("user_connection_id",NULL,time()-1);
        
        //2. Supprime la tache de fermeture automatique
        $taskMgr=NULL;
        $app->getTaskMgr($taskMgr);
        if($taskMgr !== null){
            $taskName = UserModule::disconnectTaskName($user_account_id);
            if(!$taskMgr->delete($taskName))
                 return false;
        }

        return true;//UserModule::disconnect
    }
};
?>