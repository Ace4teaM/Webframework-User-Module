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

require_once("class/bases/iModule.php");
require_once("xml_default.php");

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
    
/**
 * @brief Module utilisateur 
 */
class UserModule implements iModule
{
    //--------------------------------------------------------
    // Constantes des erreurs
    // @class UserModule
    //--------------------------------------------------------
    
    //erreurs
    const Disconnected = "USER_DISCONNECTED";
    
    //--------------------------------------------------------
    // Méthodes
    // @class UserModule
    //--------------------------------------------------------
    
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
     * @brief Cree un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function createAccount($uid, $pwd, $client_id, $mail){ 
        global $app;
        return $app->callStoredProc("user_create_account", $uid, $pwd, $client_id, $mail);
    }
    
    /** 
     * @brief Supprime un utilisateur existant
     */
    public static function deleteAccount($uid){ 
        global $app;
        return $app->callStoredProc("user_delete_account", $uid);
    }
    
    /** 
     * @brief Connect un utilisateur
     * 
     * @param type $uid         Nom d'utilisateur
     * @param type $client_ip   IP du client. Si NULL, $_SERVER["..."] est utilisé
     * @param type $local_path  Chemin d'accées local pour le paratage de données
     * @param type $life_time   Durée de vide de la session en secondes
     */
    public static function connectUser($uid, $client_ip, $local_path, $life_time){ 
        global $app;
        return $app->callStoredProc("user_connect", $uid, $client_ip, $local_path, $life_time);
    }
    
    /** 
     * @brief Déconnecte un utilisateur
     */
    public static function disconnectUser($uid){ 
        global $app;
        return $app->callStoredProc("user_disconnect_account", $uid);
    }
    
    /** 
     * @brief Déconnecte une connexion
     */
    public static function disconnect($cid){ 
        global $app;
        return $app->callStoredProc("user_disconnect", $cid);
    }
    
    /** 
     * @brief Déconnecte un utilisateur
     */
    public static function disconnectAll(){ 
        global $app;
        return $app->callStoredProc("user_disconnect_all");
    }
    
    /** 
     * @brief Crée un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function activateAccount($uid, $pwd, $mail, $token){ 
        global $app;
        return $app->callStoredProc("user_activate_account", $uid, $pwd, $mail, $token);
    }
    
    /** 
     * @brief Inscrit un nouvel utilisateur
     * 
     * @param type $uid Identifiant
     * @param type $mail Adresse Mail
     */
    public static function registerAccount($uid, $mail){ 
        global $app;
        return $app->callStoredProc("user_register_account", $uid, $mail);
    }
    

    /**
      @brief Get single entry by mail
      @param $inst UserRegistration instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getRegisterByMail(&$inst,$user_mail,$db=null){
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //initialise la requete SQL
        $query = "
            select * from user_registration
                    where user_mail = '$user_mail';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        $inst = new UserRegistration();
        UserRegistrationMgr::bindResult($inst,$result);

        return RESULT_OK();
    }
    
    /** 
     * @brief Vérifie si un utilisateur existe utilisateur
     * 
     * @param type $uid Identifiant
     * @param type $mail Adresse Mail
     */
    public static function accountExists($uid, $mail){ 
        global $app;
        return $app->callStoredProc("user_account_exists", $uid, $mail);
    }
    
    /** 
     * @brief Cree un nouvel utilisateur
     * 
     * @param type $name
     * @param type $attributes
     * @param type $template_file
     */
    public static function makeIdentity($user_connection_id, $first_name, $last_name, DateTime $birth_day, $sex){ 
        global $app;
        return $app->callStoredProc("user_make_identity", $user_connection_id, $first_name, $last_name, $birth_day, $sex);
    }
    
    /** 
     * @brief Obtient l'identité lie à un compte utilisateur
     * 
     * @param mixed       $user_account Identifiant/Instance du compte (UserAccount)
     * @param UserAddress $identity     Pointeur recevant l'identité (UserIdentity)
     * 
     */
    public static function getIdentity($user_account,&$identity){
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //obtient l'id
        $user_account_id = ($user_account instanceof UserAccount) ? $user_account->getId() : $user_account;
        
        //initialise la requete SQL
        $query = "
                select i.* from user_identity i
                    inner join user_account a on a.user_identity_id = i.user_identity_id
                    where a.user_account_id = '$user_account_id';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        $identity = new UserIdentity();
        UserIdentityMgr::bindResult($identity,$result);

        return RESULT_OK();
    }
    
    /** 
     * @brief Obtient Le nom d'un utilisateur
     * 
     * @param mixed       $user_account Identifiant/Instance du compte (UserAccount)
     * @return User name
     */
    public static function getUserName($user_account){
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //obtient l'id
        $user_account_id = ($user_account instanceof UserAccount) ? $user_account->getId() : $user_account;
        
        //initialise la requete SQL
        $query = "
                select initcap(i.first_name) as name from user_identity i
                    inner join user_account a on a.user_identity_id = i.user_identity_id
                    where a.user_account_id = '$user_account_id';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        $name = $result->fetchValue("name");
        if(empty($name))
            $name = $user_account_id;
        
        RESULT_OK();
        return $name;
    }
    
    /** 
     * @brief Obtient l'adresse lie à un compte utilisateur
     * 
     * @param mixed       $user_account Identifiant/Instance du compte (UserAccount)
     * @param UserAddress $address      Pointeur recevant l'adresse (UserAddress)
     * 
     */
    public static function getAddress($user_account,&$address){
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //obtient l'id
        $user_account_id = ($user_account instanceof UserAccount) ? $user_account->getId() : $user_account;
        
        //initialise la requete SQL
        $query = "
            select d.* from user_address d
                    inner join user_identity i on i.user_address_id = d.user_address_id
                    inner join user_account a on a.user_identity_id = i.user_identity_id
                    where a.user_account_id = '$user_account_id';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        $address = new UserAddress();
        UserAddressMgr::bindResult($address,$result);

        return RESULT_OK();
    }
    
    /** 
     * @brief Fabrique une adresse
     * 
     * @param mixed       $src Element source, un des types suivants: UserAccount, UserConnection, UserIdentity
     * @param UserAddress $adr Adresse à initialiser 
     * 
     */
    public static function makeAddress($user_account_id, $zip_code, $city_name, $street_name, $street_number, $street_number, $country_name, $street_prefix, $building_number, $apt_number){
        global $app;
        return $app->callStoredProc("user_make_address", $user_account_id, $zip_code, $city_name, $street_name, $street_number, $country_name, $street_prefix, $building_number, $apt_number);
    }
    
    /** 
     * @brief Vérifie l'autentification d'un tilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @param type $pwd Mot de passe
     */
    public static function checkAuthentication($uid, $pwd){ 
        global $app;
        return $app->callStoredProc("user_check_authentication", $uid, $pwd);
    }
    
    /** 
     * @brief Vérifie et maintient une connexion utilisateur
     * 
     * @param type $cid Identifiant de connexion
     * @param type $ip Adresse IP du client, utilisez $_SERVER["REMOTE_ADDR"]
     * @return bool Résultat de procédure
     * 
     * # Résultat
     * Si la fonction réussie le code USER_CONNECTED est retourné
     * ## Codes d'erreur:
     *  - USER_CONNECTED             L'utilisateur est connecté
     *  - USER_CONNECTION_NOT_EXISTS La connexion n'existe pas
     *  - USER_CONNECTION_IP_REFUSED L'adresse IP différe
     * ## Paramètres de retour (si succès):
     *  - EXPIRE                     Date de la prochaine expiration
     *  - UID                        Identifiant du compte utilisateur
     */
    public static function checkConnection($cid,$ip){ 
        global $app;
        return $app->callStoredProc("user_check_connection", $cid,$ip);
    }
    
    /** 
     * @brief Génére le nom pour la tâche de déconnection d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @return string Nom de la tâche système
     */
    public static function disconnectTaskName($uid){ 
        global $app;
        return "wfwUserConnectionExpire_".$uid;
    }

    /** 
     * @brief Génére la commande pour la tâche de déconnection d'un utilisateur
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
        if(!isset($_COOKIE["user_connection_id"]))
            return RESULT(cResult::Failed,UserModule::Disconnected);
        $cid = $_COOKIE["user_connection_id"];
        
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

    /** 
     * @brief Obtient un compte utilisateur lié à une adresse mail
     * 
     * @param string      $user_mail    Adresse mail
     * @param UserAccount $user_account Pointeur recevant l'instance du compte (UserAccount)
     * 
     * @return bool Résultat de procédure
     */
    public static function getByMail($user_mail,&$user_account){
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        //initialise la requete SQL
        $query = "
            select * from user_account
                    where user_mail = '$user_mail';
        ";
        if(!$db->execute($query,$result))
            return false;
        
        $user_account = new UserAccount();
        UserAccountMgr::bindResult($user_account,$result);

        return RESULT_OK();
    }
    
}

?>