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
  Liste les comptes utilisateur
  
  Role : Admin
  UC   : List
  Module : user
  Output : "text/xml"
 */
class user_module_list_ctrl extends cApplicationCtrl
{
    public $fields    = null;
    public $op_fields = null;

    private $data = null; // XMLDocument
   
    function acceptedRole(){
        return cApplication::AdminRole;
    }

    function __construct()
    {
        //ajoute les cookie aux données d'entrées
        parent::__construct();
        $this->att = array_merge($this->att,$_COOKIE);

        //prepare la sortie
        $this->data = new XMLDocument("1.0", "utf-8");
        $this->data->appendChild($this->data->createElement('data'));
    }
    
    function main(iApplication $app, $app_path, $p)
    {
        $doc = $this->data;

        $query = "
            select a.user_account_id, i.birth_day, i.sex, i.last_name, i.first_name, a.user_mail, c.user_session_id from user_account a
                    full join user_identity i on i.user_identity_id = a.user_identity_id
                    full join user_connection c on c.user_account_id = a.user_account_id
                    where a.user_account_id!='admin'
";

        //obtient la bdd
        if(!$app->getDB($db))
            return false;
        
        if(!$db->execute($query, $result))
            return false;
        
        //extrait les instances
        while( $row = $result->fetchRow() ){
            if(!is_array($row))
                return RESULT(cResult::Failed, iDatabaseQuery::EmptyResult);

            $node = $doc->createElement('user');
            $doc->documentElement->appendChild($node);
        
            foreach($row as $id=>$value)
                $node->appendChild( $doc->createtextElement($id,$value) );
        }
        
        return RESULT_OK();
    }
    
    function output(iApplication $app, $format, $att, $result)
    {
        if(!$result->isOK())
            return parent::output($app, $format, $att, $result);

        switch($format){
            case "text/xml":
                $doc = $this->data;
                return '<?xml version="1.0" encoding="UTF-8" ?>'.$doc->saveXML( $doc->documentElement );
        }
        return parent::output($app, $format, $att, $result);
    }
};

?>