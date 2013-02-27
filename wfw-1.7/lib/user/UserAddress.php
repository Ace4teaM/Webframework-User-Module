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

}

/*
   user_address Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
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
       if($db->execute($query,$result)){
            $inst = new UserAddress();
          $inst->userAddressId = $result->fetchValue("user_address_id");
          $inst->zipCode = $result->fetchValue("zip_code");
          $inst->cityName = $result->fetchValue("city_name");
          $inst->streetName = $result->fetchValue("street_name");
          $inst->streetNumber = $result->fetchValue("street_number");
          $inst->countryName = $result->fetchValue("country_name");
          $inst->streetPrefix = $result->fetchValue("street_prefix");
          $inst->buildingNumber = $result->fetchValue("building_number");
          $inst->aptNumber = $result->fetchValue("apt_number");          

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
      
       if(is_string($id))
           $id = "'$id'";
           
      //execute la requete
       $query = "SELECT * from user_address where user_address_id=$id";
       if($db->execute($query,$result)){
            $inst = new UserAddress();
          $inst->userAddressId = $result->fetchValue("user_address_id");
          $inst->zipCode = $result->fetchValue("zip_code");
          $inst->cityName = $result->fetchValue("city_name");
          $inst->streetName = $result->fetchValue("street_name");
          $inst->streetNumber = $result->fetchValue("street_number");
          $inst->countryName = $result->fetchValue("country_name");
          $inst->streetPrefix = $result->fetchValue("street_prefix");
          $inst->buildingNumber = $result->fetchValue("building_number");
          $inst->aptNumber = $result->fetchValue("apt_number");          

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
            $select = ("user_address_id = (select user_address_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("user_address_id = (select user_address_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return UserAddressMgr::get($inst,$select,$db);
    }

}

?>