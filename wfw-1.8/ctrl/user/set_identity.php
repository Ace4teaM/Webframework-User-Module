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
  Renseigne l'identité d'un utilisateur
  
  Role : Admin
  UC   : Set_Identity
  Module : user
  Output : "text/xml"
 
  Champs:
    user_account_id : Identifiant de l'utilisateur
    last_name       : Nom
    first_name      : Prénom
    birth_day       : Date de naissance
    sex             : Genre
 */
class user_module_set_identity_ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'last_name', 'first_name', 'birth_day', 'sex');
    public $op_fields = null;

    function acceptedRole(){
        return cApplication::AdminRole;
    }

    function __construct() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {
        //1. Actualise les données de l'identité
        if(!UserModule::makeIdentity($p->user_account_id, $p->first_name, $p->last_name, $p->birth_day, $p->sex))
            return false;

        return true;//UserModule::makeIdentity
    }
};

?>