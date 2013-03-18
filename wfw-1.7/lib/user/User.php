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
 * Gestionnaire d'utilisateur
 * Librairie PHP5
 */

require_once("php/class/bases/iModule.php");
require_once("php/xml_default.php");

function relativePath( $path, $compareTo ) {
    // clean arguments by removing trailing and prefixing slashes
    if ( substr( $path, -1 ) == '/' ) {
        $path = substr( $path, 0, -1 );
    }
    if ( substr( $path, 0, 1 ) == '/' ) {
        $path = substr( $path, 1 );
    }

    if ( substr( $compareTo, -1 ) == '/' ) {
        $compareTo = substr( $compareTo, 0, -1 );
    }
    if ( substr( $compareTo, 0, 1 ) == '/' ) {
        $compareTo = substr( $compareTo, 1 );
    }

    // simple case: $compareTo is in $path
    if ( strpos( $path, $compareTo ) === 0 ) {
        $offset = strlen( $compareTo ) + 1;
        return substr( $path, $offset );
    }

    $relative  = array(  );
    $pathParts = explode( '/', $path );
    $compareToParts = explode( '/', $compareTo );

    foreach( $compareToParts as $index => $part ) {
        if ( isset( $pathParts[$index] ) && $pathParts[$index] == $part ) {
            continue;
        }

        $relative[] = '..';
    }

    foreach( $pathParts as $index => $part ) {
        if ( isset( $compareToParts[$index] ) && $compareToParts[$index] == $part ) {
            continue;
        }

        $relative[] = $part;
    }

    return implode( '/', $relative );
}
    
class UserModule implements iModule
{
    //erreurs
    const Disconnected = "USER_DISCONNECTED";
    
    /**
     * @brief Initialise le module
     * @param $local_path Chemin d'accès local vers ce dossier
     */
    public static function load($local_path){
        global $app;
        
        //chemins d'acces 
        //$this_path = dirname(__FILE__);
        //$this_relative_path = relativePath($this_path,$local_path);
        
        //print_r($this_path);
        
        //initialise la configuration
        $modParam = parse_ini_file("$local_path/config.ini", true);
        $app->config = array_merge_recursive($modParam,$app->config);
        
        //inclue le model de données
        require_path($local_path."/".$app->config["user_module"]["lib_path"]);
    }
    
    public static function libPath(){
        global $app;
        return $app->getLibPath("user_mod").$app->config["user_module"]["lib_path"];
    }
    
    public static function makeView($name,$attributes,$template_file){ 
        global $app;
        
        $base_path = $app->getLibPath("user_mod",true);
        $app_path = $base_path."/".$app->config["user_module"]["app_path"];
        $lib_path = $base_path."/".$app->config["user_module"]["lib_path"];
        
        $default_file = new cXMLDefault();
        if(!$default_file->Initialise("$app_path/default.xml")){
            return RESULT(iModule::CantLoadDefaultFile);
        }

        $template = new cXMLTemplate();
        
        //chemin d'accès vers ce module
        $attributes["_LIB_PATH_USER_MODULE_"] = $app_path;
        
        //chemin vers le template
        $select_filename = $default_file->getIndexValue("template",$name);
        if(empty($select_filename)){
            return RESULT(iModule::CantFindTemplateFile);
        }

        //charge le contenu en selection
        $select = new XMLDocument("1.0", "utf-8");
        $select->load("$app_path/".$select_filename);

        //ajoute le fichier de configuration
        $template->load_xml_file("$app_path/default.xml",$base_path);
        //$template->add_xml_file($default_file);
        
        //initialise la classe template 
        if(!$template->Initialise(
                    $app->root_path.'/'.$template_file,
                    NULL,
                    $select,
                    NULL,
                    array_merge($attributes,$app->template_attributes) ) )
                return false;

        RESULT_OK();
        
        //transforme le fichier
	return $template->Make();
    }
    
    /** 
     * Cree un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function createAccount($uid, $pwd, $client_id, $mail){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_create_account", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Supprime un utilisateur existant
     */
    public static function deleteAccount($uid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_delete_account", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Connect un utilisateur
     * 
     * @param type $uid         Nom d'utilisateur
     * @param type $client_ip   IP du client. Si NULL, $_SERVER["..."] est utilisé
     * @param type $local_path  Chemin d'accées local pour le paratage de données
     * @param type $life_time   Durée de vide de la session en secondes
     */
    public static function connectUser($uid, $client_ip, $local_path, $life_time){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_connect", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Deconnect un utilisateur
     */
    public static function disconnectUser($uid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_disconnect_account", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Deconnect une connexion
     */
    public static function disconnect($cid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_disconnect", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Deconnect un utilisateur
     */
    public static function disconnectAll(){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_disconnect_all", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Cree un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function activateAccount($uid, $pwd, $mail, $token){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_activate_account", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Inscrit un nouvel utilisateur
     * 
     * @param type $uid Identifiant
     * @param type $mail Adresse Mail
     */
    public static function registerAccount($uid, $mail){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_register_account", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Vérifie si un utilisateur existe utilisateur
     * 
     * @param type $uid Identifiant
     * @param type $mail Adresse Mail
     */
    public static function accountExists($uid, $mail){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_account_exists", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Cree un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function makeIdentity($uid, $first_name, $last_name, $birth_day, $sex){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_make_identity", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Vérifie l'autentification d'un tilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @param type $pwd Mot de passe
     */
    public static function checkAuthentication($uid, $pwd){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_check_authentication", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Vérifie l'autentification d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @param type $pwd Mot de passe
     */
    public static function checkConnection($cid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        if(!$db->call($app->getCfgValue("database","schema"), "user_check_connection", func_get_args(), $result))
            return false;
        
        $row = $result->fetchRow();

        //return $result;
        return RESULT($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));
    }
    
    /** 
     * Génére le nom pour la tâche de déconnection d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @return string Nom de la tâche système
     */
    public static function disconnectTaskName($uid){ 
        global $app;
        return "wfwUserConnectionExpire_".$uid;
    }

    /** 
     * Génére la commande pour la tâche de déconnection d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @return string Commande de la tâche système
     */
    public static function disconnectTaskCmd($uid){
        global $app;
        if(defined("WINDOWS"))
            return '"'.$app->getRootPath().'/sh/disconnect_task.bat" "'.$uid.'" > NUL';
        //UNIX
        return '"'.$app->getRootPath().'/sh/disconnect_task.sh" "'.$uid.'" > NUL';
    }
    
    /** 
     * @brief Obtient l'utilisateur connecté
     * 
     * @param UserAccount $user Reçoi l'instance du compte utilisateur connecté
     * @return bool Résultat de procédure
     */
    public static function getCurrent(&$user){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //obtient l'identifiant de connexion
        if(!isset($_COOKIE["cid"]))
            return RESULT(cResult::Failed,UserModule::Disconnected);
        $cid = $_COOKIE["cid"];
        
        //vérifie la validité de la connexion
        if(!UserModule::checkConnection($cid,$_SERVER["REMOTE_ADDR"]))
            return false;
        
        //obtient l'identifiant de l'utilisateur
        $query = "select user_account_id from user_connection where user_connection_id = '$cid';";
        if(!$db->execute($query, $result))
            return false;
        $uid = $result->fetchValue("user_account_id");
        
        //obtient l'utilisateur
        return UserAccountMgr::getById($user, $uid);
    }

}

?>
