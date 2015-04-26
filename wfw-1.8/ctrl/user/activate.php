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
  Active un compte utilisateur
  
  Role   : Tous
  UC     : Activate
  Module : user
  Output : "text/xml"
 
  Champs:
    user_account_id : Identifiant de l'utilisateur (renseigné à l'inscription)
    user_pwd        : Mot-de-passe
    user_mail       : Adresse éléctronique (renseigné à l'inscription)
    token           : Clé d'activation
 */
class user_module_activate_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'user_pwd', 'user_mail', 'token');
    public $op_fields = null;

    function main(iApplication $app, $app_path, $p) {

        // crée le compte utilisateur
        if(!UserModule::activateAccount( $p->user_account_id, $p->user_pwd, $p->user_mail, $p->token ))
            return false;

        return true; //UserModule::activateAccount
    }
};
?>