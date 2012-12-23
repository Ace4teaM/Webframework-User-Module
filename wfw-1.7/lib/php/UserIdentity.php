<?php
/*
   Webframework User Module
   PHP Data-Model Implementation
*/


/**
* @author       developpement
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
   Class manager
*/
class UserIdentityMgr
{
    /*
      @brief Get entry list
      @param $list Array to receive new instances
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function getAll(&$list,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       //...
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
       if($db->execute($query, $result)){
            $inst = new UserIdentity();
          $inst->userIdentityId = $db->fetchValue($result,"user_identity_id");
          $inst->firstName = $db->fetchValue($result,"first_name");
          $inst->lastName = $db->fetchValue($result,"last_name");
          $inst->birthDay = $db->fetchValue($result,"birth_day");
          $inst->sex = $db->fetchValue($result,"sex");          

          return true;
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
      
      //execute la requete
       $query = "SELECT * from user_identity where user_identity_id=$id";
       if($db->execute($query, $result)){
            $inst = new UserIdentity();
          $inst->userIdentityId = $db->fetchValue($result,"user_identity_id");
          $inst->firstName = $db->fetchValue($result,"first_name");
          $inst->lastName = $db->fetchValue($result,"last_name");
          $inst->birthDay = $db->fetchValue($result,"birth_day");
          $inst->sex = $db->fetchValue($result,"sex");          

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
            $select = ("(select user_identity_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("(select user_identity_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserIdentityMgr::getById($inst,$select,$db);
    }

}

?>