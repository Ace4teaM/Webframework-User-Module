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
  ReSupprime un compte utilisateur
  
  Role   : Admin
  UC     : Delete
  Module : user
  Output : "text/xml"
 
  Champs:
    user_account_id : Identifiant de l'utilisateur
 */
class user_module_delete_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id');
    public $op_fields = null;

    function acceptedRole(){
        return cApplication::AdminRole;
    }

    function main(iApplication $app, $app_path, $p) {

        $client_id = "none";

        //supprime le compte utilisateur
        if(!UserModule::deleteAccount($p->user_account_id))
            return false;

        return true; // UserModule::deleteAccount
    }
};

?>