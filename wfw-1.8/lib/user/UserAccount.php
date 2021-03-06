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

/**
 *  Webframework Module
 *  PHP Data-Model Implementation
*/


/**
* @author       AceTeaM
*/
class UserAccount
{
    
    /**
    * @var      String
    */
    public $userAccountId;
    
    /**
    * @var      String
    */
    public $clientId;
    
    /**
    * @var      String
    */
    public $userMail;
    
    /**
    * @var      String
    */
    public $userPwd;    

}

/*
   user_account Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class UserAccountMgr
{
    /**
     * @brief Convert existing instance to XML element
     * @param $inst Entity instance (UserAccount)
     * @param $doc Parent document
     * @return New element node
     */
    public static function toXML(&$inst,$doc) {
        $node = $doc->createElement("UserAccount");
        
        $node->appendChild($doc->createTextElement("user_account_id",$inst->userAccountId));
        $node->appendChild($doc->createTextElement("client_id",$inst->clientId));
        $node->appendChild($doc->createTextElement("user_mail",$inst->userMail));
        $node->appendChild($doc->createTextElement("user_pwd",$inst->userPwd));       

          
        return $node;
    }
    
    
    /*
      @brief Get entry list
      @param $list Array to receive new instances
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function getAll(&$list,$cond,$db=null){
       $list = array();
      
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_account where $cond";
       if(!$db->execute($query,$result))
          return false;
       
      //extrait les instances
       $i=0;
       while($result->seek($i)){
        $inst = new UserAccount();
        UserAccountMgr::bindResult($inst,$result);
        array_push($list,$inst);
        $i++;
       }
       
       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserAccount instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function bindResult(&$inst,$result){
          $inst->userAccountId = $result->fetchValue("user_account_id");
          $inst->clientId = $result->fetchValue("client_id");
          $inst->userMail = $result->fetchValue("user_mail");
          $inst->userPwd = $result->fetchValue("user_pwd");          

       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserAccount instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_account where $cond";
       if($db->execute($query,$result)){
            $inst = new UserAccount();
          return UserAccountMgr::bindResult($inst,$result);
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst UserAccount instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getById(&$inst,$id,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
       if(is_string($id))
           $id = "'$id'";
           
      //execute la requete
       $query = "SELECT * from user_account where user_account_id=$id";
       if($db->execute($query,$result)){
            $inst = new UserAccount();
          $inst->userAccountId = $result->fetchValue("user_account_id");
          $inst->clientId = $result->fetchValue("client_id");
          $inst->userMail = $result->fetchValue("user_mail");
          $inst->userPwd = $result->fetchValue("user_pwd");          

          return true;
       }
       return false;
    }

   /** @brief Convert name to code */
    public static function nameToCode($name){
        for($i=strlen($name)-1;$i>=0;$i--){
            $c = substr($name, $i, 1);
            if(strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ",$c) !== FALSE){
                $name = substr_replace($name,($i?"_":"").strtolower($c), $i, 1);
            }
        }
        return $name;
    }
    
    /**
      @brief Get entry by id's relation table
      @param $inst UserAccount instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = UserAccountMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("user_account_id = (select user_account_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("user_account_id = (select user_account_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserAccountMgr::get($inst,$select,$db);
    }

}

?>