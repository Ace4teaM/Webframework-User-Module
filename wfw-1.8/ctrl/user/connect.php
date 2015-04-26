<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2012-2013,2015 Thomas AUGUEY <contact@aceteam.org>
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
  Connexion d'un utilisateur
  
  Role   : Tous
  UC     : Connect
  Module : user
 
  Champs:
    user_account_id : Identifiant de l'utilisateur
    user_pwd        : Mot-de-passe de l'utilisateur

  Champs complémentaires:
    life_time : Temps de vie de la session en minutes
 */
class user_module_connect_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'user_pwd');
    public $op_fields = array('life_time','no_cookie');

    function main(iApplication $app, $app_path, $p) {

        // initialise les arguments
        $client_ip  = $_SERVER["REMOTE_ADDR"];
        $session_path = NULL;
        
        if(!$p->life_time)
            $p->life_time = $app->getCfgValue("user_module","default_connection_life_time");

        // 1. Vérifie si le compte utilisateur existe
        if(!UserModule::checkAuthentication($p->user_account_id, $p->user_pwd))
            return false;
        
        // 2. Vérifie si le compte utilisateur est inactif
        // ...

        // 3,4,5,6 Crée une connexion avec session automatique
        if(!UserModule::connectUser($p->user_account_id, $client_ip, $session_path, $p->life_time))
            return false;
        // retourne le resultat de cette fonction
        $result = cResult::getLast();

        // 9. Définit l’identificateur de connexion dans le cookie de navigateur
        // Note: Ecrit le cookie dans l'ensemble du domaine '/'. Permet aux autres applications d'accèder à ce cookie
        //if($app->getCfgValue("user_module","set_cookie_enabled") == true)
        //if($p->no_cookie == null)
        {
            //setcookie("user_connection_id",$result->getAtt("CONNECTION_ID"),0,'/');
            setcookie("user_connection_id",$result->getAtt("CONNECTION_ID"),0);
        }

        // 8. Crée la tâche de fermeture automatique de connexion 
        $taskMgr=NULL;
        $app->getTaskMgr($taskMgr);
        if($taskMgr !== null && $p->life_time > 0){
            $taskName = UserModule::disconnectTaskName($p->user_account_id);
            $taskCmd  = UserModule::disconnectTaskCmd($p->user_account_id);
            $expire   = new DateTime();
            $expire->add(new DateInterval('P0Y0DT0H'.$p->life_time.'M'));
            if(!$taskMgr->create($taskName,$expire,$taskCmd))
                 return false;
        }
        
        return RESULT_INST($result); //UserModule::connectUser
    }
};

?>