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
  Renseigne le mot-de-passe d'un utilisateur
  
  Role   : Admin
  UC     : Set_Password
  Module : user
  Output : "text/xml"
 
  Champs:
    user_account_id : Identifiant de l'utilisateur
    user_pwd        : Nouveau Mot-de-passe
 */
class user_module_set_password_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'user_pwd');
    public $op_fields = null;

    function acceptedRole(){
        return cApplication::AdminRole;
    }

    function __construct() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //initialise la requete SQL
        $query = "
            update user_account
                    set user_pwd = '$p->user_pwd'
                    where user_account_id = '$p->user_account_id';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        return RESULT_OK();
    }
};

?>