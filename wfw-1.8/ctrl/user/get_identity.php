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
 * UC   : get_identity
 */

class user_module_get_identity_ctrl extends cApplicationCtrl{
    public $fields    = array('user_connection_id');
    public $op_fields = null;

    private $identity = array();
    
    function __construct() {
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);
    }
    
    function main(iApplication $app, $app_path, $p) {
        
        if(!$app->getDB($db))
            return false;
        
        $query = "
            select i.* from user_identity i
                inner join user_account a on a.user_identity_id = i.user_identity_id
                inner join user_connection c on c.user_account_id = a.user_account_id
                where c.user_connection_id = '$p->user_connection_id';
        ";

        if(!$db->execute($query,$result))
            return false;
        
        if(!$result->rowCount())
            return RESULT_OK(); //RESULT(cResult::Failed,iDatabaseQuery::EmptyResult);
        
        $this->identity = $result->fetchRow();
        
        return RESULT_OK();
    }
    
    function output(iApplication $app, $format, $att, $result)
    {
        if(!$result->isOK())
            return parent::output($app, $format, $att, $result);
        
        switch($format){
            case "xml":
                $doc = new XMLDocument("1.0", "utf-8");
                $doc->appendChild( $doc->createElement('data') );
                $doc->appendAssocArray($doc->documentElement,$this->identity);
                $doc->appendAssocArray($doc->documentElement,$result->toArray());
                return '<?xml version="1.0" encoding="UTF-8" ?>'.$doc->saveXML( $doc->documentElement );
            case "xarg":
                return xarg_encode_array($this->identity) . xarg_encode_array($result->toArray());
        }
        return parent::output($app, $format, $att, $result);
    }
};

?>