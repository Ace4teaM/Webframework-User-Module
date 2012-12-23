<?php
/*
   Webframework User Module
   PHP Data-Model Implementation
*/


/**
* @author       developpement
*/
class UserAddress
{
    
    /**
    * @var      int
    */
    public $userAddressId;
    
    /**
    * @var      String
    */
    public $zipCode;
    
    /**
    * @var      String
    */
    public $cityName;
    
    /**
    * @var      String
    */
    public $streetName;
    
    /**
    * @var      double
    */
    public $streetNumber;
    
    /**
    * @var      String
    */
    public $countryName;
    
    /**
    * @var      String
    */
    public $streetPrefix;
    
    /**
    * @var      double
    */
    public $buildingNumber;
    
    /**
    * @var      double
    */
    public $aptNumber;
    
    /**
    * @return   UserIdentity
    */
    public function getIdentity()
    {
       // TODO: implement
    }    

}

/*
   Class manager
*/
class UserAddressMgr
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
      @param $inst UserAddress instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_address where $cond";
       if($db->execute($query, $result)){
            $inst = new UserAddress();
          $inst->userAddressId = $db->fetchValue($result,"user_address_id");
          $inst->zipCode = $db->fetchValue($result,"zip_code");
          $inst->cityName = $db->fetchValue($result,"city_name");
          $inst->streetName = $db->fetchValue($result,"street_name");
          $inst->streetNumber = $db->fetchValue($result,"street_number");
          $inst->countryName = $db->fetchValue($result,"country_name");
          $inst->streetPrefix = $db->fetchValue($result,"street_prefix");
          $inst->buildingNumber = $db->fetchValue($result,"building_number");
          $inst->aptNumber = $db->fetchValue($result,"apt_number");          

          return true;
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst UserAddress instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getById(&$inst,$id,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from user_address where user_address_id=$id";
       if($db->execute($query, $result)){
            $inst = new UserAddress();
          $inst->userAddressId = $db->fetchValue($result,"user_address_id");
          $inst->zipCode = $db->fetchValue($result,"zip_code");
          $inst->cityName = $db->fetchValue($result,"city_name");
          $inst->streetName = $db->fetchValue($result,"street_name");
          $inst->streetNumber = $db->fetchValue($result,"street_number");
          $inst->countryName = $db->fetchValue($result,"country_name");
          $inst->streetPrefix = $db->fetchValue($result,"street_prefix");
          $inst->buildingNumber = $db->fetchValue($result,"building_number");
          $inst->aptNumber = $db->fetchValue($result,"apt_number");          

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
      @param $inst UserAddress instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = UserAddressMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("(select user_address_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("(select user_address_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserAddressMgr::getById($inst,$select,$db);
    }

}

?>