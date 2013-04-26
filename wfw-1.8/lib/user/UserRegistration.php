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
class UserRegistration
{
    
    /**
    * @var      int
    */
    public $userRegistrationId;
    
    /**
    * @var      String
    */
    public $userToken;
    
    /**
    * @var      String
    */
    public $userMail;
    
    /**
    * @var      String
    */
    public $userId;    

}

/*
   user_registration Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class UserRegistrationMgr
{
    /**
     * @brief Convert existing instance to XML element
     * @param $inst Entity instance (UserRegistration)
     * @param $doc Parent document
     * @return New element node
     */
    public static function toXML(&$inst,$doc) {
        $node = $doc->createElement("UserRegistration");
        
        $node->appendChild($doc->createTextElement("user_registration_id",$inst->userRegistrationId));
        $node->appendChild($doc->createTextElement("user_token",$inst->userToken));
        $node->appendChild($doc->createTextElement("user_mail",$inst->userMail));
        $node->appendChild($doc->createTextElement("user_id",$inst->userId));       

          
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
       $query = "SELECT * from user_registration where $cond";
       if(!$db->execute($query,$result))
          return false;
       
      //extrait les instances
       $i=0;
       while($result->seek($i)){
        $inst = new UserRegistration();
        UserRegistrationMgr::bindResult($inst,$result);
        array_push($list,$inst);
        $i++;
       }
       
       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserRegistration instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function bindResult(&$inst,$result){
          $inst->userRegistrationId = $result->fetchValue("user_registration_id");
          $inst->userToken = $result->fetchValue("user_token");
          $inst->userMail = $result->fetchValue("user_mail");
          $inst->userId = $result->fetchValue("user_id");          

       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserRegistration instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_registration where $cond";
       if($db->execute($query,$result)){
            $inst = new UserRegistration();
          return UserRegistrationMgr::bindResult($inst,$result);
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst UserRegistration instance pointer to initialize
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
       $query = "SELECT * from user_registration where user_registration_id=$id";
       if($db->execute($query,$result)){
            $inst = new UserRegistration();
          $inst->userRegistrationId = $result->fetchValue("user_registration_id");
          $inst->userToken = $result->fetchValue("user_token");
          $inst->userMail = $result->fetchValue("user_mail");
          $inst->userId = $result->fetchValue("user_id");          

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
      @param $inst UserRegistration instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = UserRegistrationMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("user_registration_id = (select user_registration_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("user_registration_id = (select user_registration_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserRegistrationMgr::get($inst,$select,$db);
    }

}

?>