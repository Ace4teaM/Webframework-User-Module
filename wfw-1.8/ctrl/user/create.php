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
 * Crée un compte utilisateur
 * Rôle : Administrateur
 * UC   : create_account
 */

class user_module_create_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'user_pwd', 'user_mail');
    public $op_fields = null;

    function main(iApplication $app, $app_path, $p) {

        //crée le compte utilisateur
        if(!UserModule::createAccount($p->user_account_id,$p->user_pwd,NULL,$p->user_mail))
            return false;

        //retourne le resultat de cette fonction
        return true; // UserModule::createAccount
    }
};
?>