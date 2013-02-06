<?php

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
     * Traduit un nom d'attribut
     * 
     * @param string $name Nom de l'attribut
     * @return Texte de remplacement. Si le nom d'attribut est inconnu, l'identifiant est retourné
     */
    public static function translateAttributeName($name){ 
        switch($name){
            case "uid":
                return "Nom d'utilisateur";
            case "pwd":
                return "Mot-de-passe";
            case "mail":
                return "Adresse eMail";
            case "token":
                return "Jeton";
            case "life_time":
                return "Temps d'inactivité avant déconnexion";
            case "cid":
                return "Identifiant de connexion";
            case "firstname":
                return "Prénom";
            case "lastname":
                return "Nom";
            case "birthday":
                return "Date de naissance";
        }
        return $name;
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_create_account", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
    }
    
    /** 
     * Supprime un utilisateur existant
     */
    public static function deleteAccount($uid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        $result = $db->call($app->getCfgValue("database","schema"), "user_delete_account", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_connect", func_get_args());
        if($result){
            //tache de deconnexion
            if($life_time > 0)
                ;;
        }
        
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
    }
    
    /** 
     * Deconnect un utilisateur
     */
    public static function disconnectUser($uid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        $result = $db->call($app->getCfgValue("database","schema"), "user_disconnect", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_activate_account", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_register_account", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_account_exists", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_make_identity", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
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

        $result = $db->call($app->getCfgValue("database","schema"), "user_check_authentication", func_get_args());
        //return $result;
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
    }
    
    /** 
     * Vérifie l'autentification d'un tilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @param type $pwd Mot de passe
     */
    public static function checkConnection($cid){ 
        global $app;
        $db=null;
        
        if(!$app->getDB($db))
            return RESULT(cResult::Failed, Application::DatabaseConnectionNotFound);

        $result = $db->call($app->getCfgValue("database","schema"), "user_check_connection", func_get_args());

        //print_r($result);     
        return RESULT($result[0], $result[1], stra_to_array($result[2]));
    }
    
    /** 
     * Génére le nom pour la tâche de déconnection d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @return Nom de la tâche système
     */
    public static function disconnectTaskName($uid){ 
        global $app;
        return "wfwUserConnectionExpire_".$uid;
    }

    /** 
     * Génére la commande pour la tâche de déconnection d'un utilisateur
     * 
     * @param type $uid Nom d'utilisateur
     * @return Commande de la tâche système
     */
    public static function disconnectTaskCmd($uid){
        global $app;
        if(defined("WINDOWS"))
            return '"'.$app->getRootPath().'/sh/disconnect_task.bat" "'.$uid.'" > NUL';
        //UNIX
        return '"'.$app->getRootPath().'/sh/disconnect_task.sh" "'.$uid.'" > NUL';
    }
}

?>
