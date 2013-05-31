<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2013 Thomas AUGUEY <contact@aceteam.org>
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
 * Renseigne l'adresse d'identité
 * Rôle : Utilisateur
 * UC   : user_address
 */

class user_module_address_ctrl extends cApplicationCtrl{
    public $fields    = array('user_connection_id', 'zip_code', 'city_name', 'street_name', 'street_number', 'country_name');
    public $op_fields = array('street_prefix', 'building_number', 'apt_number');

    function __construct() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {

        if(!UserModule::checkConnection($p->user_connection_id,$_SERVER["REMOTE_ADDR"]))
            return false;
        
        $user_account_id = cResult::getLast()->getAtt("UID");
        
        //obtient le compte utilisateur
        if(!UserModule::makeAddress($user_account_id, $p->zip_code, $p->city_name, $p->street_name, $p->street_number, $p->street_number, $p->country_name, $p->street_prefix, $p->building_number, $p->apt_number))
            return false;

        return true;//UserModule::makeAddress
    }
};

?>