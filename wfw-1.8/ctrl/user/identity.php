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
 * Renseigne l'identité d'un utilisateur
 * Rôle : Utilisateur
 * UC   : user_identity
 */

class Ctrl extends cApplicationCtrl{
    public $fields    = array('user_connection_id', 'last_name', 'first_name', 'birth_day', 'sex');
    public $op_fields = null;

    function Ctrl() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {
        
        if(!UserModule::checkConnection($p->user_connection_id,$_SERVER["REMOTE_ADDR"]))
            return false;
        
        $user_account_id = cResult::getLast()->getAtt("UID");
        
        //obtient le compte utilisateur
        if(!UserModule::makeIdentity($user_account_id, $p->first_name, $p->last_name, $p->birth_day, $p->sex))
            return false;

        return true;//UserModule::makeIdentity
    }
};

?>