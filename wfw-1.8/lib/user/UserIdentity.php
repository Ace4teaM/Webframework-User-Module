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
class UserIdentity
{
    
    /**
    * @var      int
    */
    public $userIdentityId;
    
    /**
    * @var      String
    */
    public $firstName;
    
    /**
    * @var      String
    */
    public $lastName;
    
    /**
    * @var      Date
    */
    public $birthDay;
    
    /**
    * @var      String
    */
    public $sex;    

}

/*
   user_identity Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class UserIdentityMgr
{
    /**
     * @brief Convert existing instance to XML element
     * @param $inst Entity instance (UserIdentity)
     * @param $doc Parent document
     * @return New element node
     */
    public static function toXML(&$inst,$doc) {
        $node = $doc->createElement("UserIdentity");
        
        $node->appendChild($doc->createTextElement("user_identity_id",$inst->userIdentityId));
        $node->appendChild($doc->createTextElement("first_name",$inst->firstName));
        $node->appendChild($doc->createTextElement("last_name",$inst->lastName));
        $node->appendChild($doc->createTextElement("birth_day",$inst->birthDay));
        $node->appendChild($doc->createTextElement("sex",$inst->sex));       

          
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
       $query = "SELECT * from user_identity where $cond";
       if(!$db->execute($query,$result))
          return false;
       
      //extrait les instances
       $i=0;
       while($result->seek($i)){
        $inst = new UserIdentity();
        UserIdentityMgr::bindResult($inst,$result);
        array_push($list,$inst);
        $i++;
       }
       
       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserIdentity instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function bindResult(&$inst,$result){
          $inst->userIdentityId = $result->fetchValue("user_identity_id");
          $inst->firstName = $result->fetchValue("first_name");
          $inst->lastName = $result->fetchValue("last_name");
          $inst->birthDay = $result->fetchValue("birth_day");
          $inst->sex = $result->fetchValue("sex");          

       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst UserIdentity instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_identity where $cond";
       if($db->execute($query,$result)){
            $inst = new UserIdentity();
          return UserIdentityMgr::bindResult($inst,$result);
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst UserIdentity instance pointer to initialize
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
       $query = "SELECT * from user_identity where user_identity_id=$id";
       if($db->execute($query,$result)){
            $inst = new UserIdentity();
          $inst->userIdentityId = $result->fetchValue("user_identity_id");
          $inst->firstName = $result->fetchValue("first_name");
          $inst->lastName = $result->fetchValue("last_name");
          $inst->birthDay = $result->fetchValue("birth_day");
          $inst->sex = $result->fetchValue("sex");          

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
      @param $inst UserIdentity instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = UserIdentityMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("user_identity_id = (select user_identity_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("user_identity_id = (select user_identity_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserIdentityMgr::get($inst,$select,$db);
    }

}

?>