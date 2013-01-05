<?php
/*
   Webframework User Module
   PHP Data-Model Implementation
*/


/**
* @author       developpement
*/
class UserConnection
{
    
    /**
    * @var      int
    */
    public $userConnectionId;
    
    /**
    * @var      std::string
    */
    public $clientIp;
    
    /**
    * @var      Date
    */
    public $lastAccess;
    
    /**
    * @var      int
    */
    public $lifeTime;
    
    /**
    * @var      std::string
    */
    public $linkPath;    

}

/*
   user_connection Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class UserConnectionMgr
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
      @param $inst UserConnection instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_connection where $cond";
       if($db->execute($query, $result)){
            $inst = new UserConnection();
          $inst->userConnectionId = $db->fetchValue($result,"user_connection_id");
          $inst->clientIp = $db->fetchValue($result,"client_ip");
          $inst->lastAccess = $db->fetchValue($result,"last_access");
          $inst->lifeTime = $db->fetchValue($result,"life_time");
          $inst->linkPath = $db->fetchValue($result,"link_path");          

          return true;
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst UserConnection instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getById(&$inst,$id,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_connection where user_connection_id=$id";
       if($db->execute($query, $result)){
            $inst = new UserConnection();
          $inst->userConnectionId = $db->fetchValue($result,"user_connection_id");
          $inst->clientIp = $db->fetchValue($result,"client_ip");
          $inst->lastAccess = $db->fetchValue($result,"last_access");
          $inst->lifeTime = $db->fetchValue($result,"life_time");
          $inst->linkPath = $db->fetchValue($result,"link_path");          

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
      @param $inst UserConnection instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = UserConnectionMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("(select user_connection_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("(select user_connection_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserConnectionMgr::getById($inst,$select,$db);
    }

}

?>