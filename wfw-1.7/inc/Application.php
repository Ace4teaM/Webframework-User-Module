<?php
require_once("inc/Error.php");

/**
 * @brief Interface principale avec l'application 
 */
class Application /*extends cApplication*/{
    //errors class
    const Configuration              = "CONFIGURATION";
    const ModuleClassNotFound        = "MODULE_NOT_FOUND";
    const DatabaseConnectionNotFound = "DATABASE_CONNECTION_NOT_FOUND";
    const UnsuportedFeature          = "UNSUPORTED_FEATURE";
    
    //
    private $template_attributes;
    private $config;
    private $root_path;
    
    /** 
     * @brief Pointeur sur la base de données par défaut
     * @var iDataBase
     */
    public $db;
    
    /** 
     * @brief Pointeur sur le gestionnaire de tache
     * @var iTaskMgr
     */
    public $task;
    
    function Application($root_path){
        $this->root_path = $root_path;
        $this->template_attributes = array();
        
        // Configuration par défaut
        /*$this->config = array(
            "TMP_DIR" => "tmp",
            "UPLOAD_DIR" => "tmp"
        );
        array_merge($this->config, parse_ini_file($this->root_path."/cfg/config.ini", true));*/
        
        // Charge la configuration
        $this->config = parse_ini_file($this->root_path."/cfg/config.ini", true);
        $this->config = array_change_key_case($this->config, CASE_UPPER);
        foreach($this->config as $section=>&$params)
            $params = array_change_key_case($params, CASE_UPPER);
        
        //ajoute les chemins d'accès aux attributs de template
        $path_section = $this->getCfgSection("path");
        if(isset($path_section)){
            foreach($path_section as $name=>$path){
                $this->template_attributes["_LIB_PATH_".strtoupper($name)."_"] = $path;
            }
        }
        
        // initialise la base de données à null
        //( la fonction getDB initialise la connexion si besoin )
        $this->db = null;
        
        // initialise le gestionnaire de tache
        //( la fonction getTaskMgr initialise l'instance si besoin )
        $this->task = null;
    }

    /**
     * @brief Obtient le gestionnaire de tache par défaut
     * @return Résultat de la procédure
     * @retval true La fonction à réussit, $iface contient un pointeur vers une interface iTaskMgr initialisée
     * @retval false La fonction à échouée, voir cResult::getLast() pour plus d'informations
     */
    function getTaskMgr(&$iface)
    {
        //initialise
        if($this->task===NULL){
            $className = $this->getCfgValue(constant("SYSTEM"), "taskmgr_class");
            $this->task = new $className();
        }
        //ok, retourne l'interface
        $iface = $this->task;
        return RESULT_OK();
    }
    
    /**
     * @brief Obtient la connexion à la base de données par défaut
     * @return Résultat de la procédure
     * @retval true La fonction à réussit, $db_iface contient un pointeur vers une interface iDataBase initialisée
     * @retval false La fonction à échouée, voir cResult::getLast() pour plus d'informations
     */
    function getDB(&$db_iface)
    {
        //obtient le nom de la classe à instancier
        $db_classname = $this->getCfgValue("database","class");
        if(!isset($db_classname) || empty($db_classname))
            return RESULT(Application::Configuration,"No database class defined");

        //initialise l'instance
        if($this->db == null){
            //initialise la connexion
            $db = new $db_classname();
            $user   = $this->getCfgValue("database","user");
            $name   = $this->getCfgValue("database","name");
            $pwd    = $this->getCfgValue("database","pwd");
            $server = $this->getCfgValue("database","server");
            $port   = $this->getCfgValue("database","port");
            if(!$db->connect($user,$name,$pwd,$server,$port))
                return false;
            
            $this->db = $db; // ok
        }
        
        //ok, retourne l'interface
        $db_iface = $this->db;
        return RESULT_OK();
    }
    
    /**
     * @brief Obtient le chemin d'accès vers l'application
     * @return string Chemin absolue vers la racine de l'application
     */
    function getRootPath(){
        return $this->root_path;
    }
    
    /**
     * @brief Obtient le chemin d'accès vers l'application
     * @return string Chemin absolue vers la racine de l'application
     */
    function getTmpPath(){
        return $this->root_path."/".$this->getCfgValue("path","tmp");
    }
    
    /**
     * @brief Obtient un chemin d'accès depuis la configuration locale
     * 
     * @param string $name Identifiant de la librairie
     * @param bool $relatif Si true retourne le chemin relatif, sinon le chemin absolue
     * 
     * @return Chemin vers le dossier désiré
     * @retval string Chemin d'accès (sans slash de fin)
     * @retval false  Chemin introuvable dans la configuration
     */
    function getLibPath($name="wfw",$relatif=false){
        $path = $this->getCfgValue("path",$name);
        if($path == NULL){
            //$this->result->set(cResult::ERR_FAILED,"config_not_found",array("desc"=>"Library path '$name' not set in configuration file"));
            return false;
        }
        
        return (!$relatif) ? $this->root_path."/".$path : $path;
    }
    
    /**
     * @brief Obtient les parametres d'une section du fichier de configuration
     * @param $section_name Nom de la section
     * @return Paramètres de configuration
     * @retval array Liste des paramètres
     * @retval null La section n'existe pas
     */
    function getCfgSection($name){
        $name = strtoupper($name);
        return (isset($this->config[$name]) ? $this->config[$name] : null);
    }
    
    /**
     * @brief Obtient les parametres d'une section du fichier de configuration
     * @param $section_name Nom de la section
     * @param $section_name Nom de l'item
     * @return Paramètres de configuration
     * @retval array Liste des paramètres
     * @retval null La section n'existe pas
     */
    function getCfgValue($section_name,$item_name){
        $item_name = strtoupper($item_name);
        $section = $this->getCfgSection($section_name);
        return ($section!==null && isset($section[$item_name]) ? $section[$item_name] : null);
    }
    
    /**
     * @brief Fabrique une vue HTML
     * @param string $filename Chemin d'accès au fichier template (relatif à la racine du site)
     * @param string $attributes Tableau associatif des champs en entrée (voir cHTMLTemplate::transform)
     * @return string Contenu du template transformé
     */
    function makeHTMLView($filename,$attributes){
	return cHTMLTemplate::transform(
           //fichier..
           file_get_contents($this->root_path.'/'.$filename),
           //champs..
           array_merge($attributes,$this->template_attributes)
	);
    }
    
    /**
     * @brief Fabrique puis affiche une vue HTML dans la sortie standard
     * @param $filename Chemin d'accès au fichier template (relatif à la racine du site)
     * @param $attributes Tableau associatif des champs en entrée (voir cHTMLTemplate::transform)
     */
    function showHTMLView($filename,$attributes){
        $content = $this->makeHTMLView($filename,$attributes);
        header("Content-type: text/html");
        echo $content;
    }
    
    /**
     * @brief Fabrique une vue XML/XHTML
     * @param $filename Chemin d'accès au fichier template (relatif à la racine du site)
     * @param $select Document XML de sélection en entrée (voir cXMLTemplate::Initialise)
     * @param $attributes Tableau associatif des champs en entrée (voir cXMLTemplate::Initialise)
     * @return string Contenu du template transformé
     */
    function makeXMLView($filename,$attributes,$template_file="view/template.html"){ 

        $template = new cXMLTemplate();
        
        //charge le contenu en selection
        $select = new XMLDocument("1.0", "utf-8");
        $select->load($this->root_path.'/'.$filename);

        //ajoute le fichier de configuration
        $template->load_xml_file('default.xml',$this->root_path);
        
        //initialise la classe template 
        if(!$template->Initialise(
                    $this->root_path.'/'.$template_file,
                    NULL,
                    $select,
                    NULL,
                    array_merge($attributes,$this->template_attributes) ) )
                return false;

        //transforme le fichier
	return $template->Make();
    }
    
    /**
     * @brief Fabrique puis affiche une vue XML/XHTML dans la sortie standard
     * @param $filename Chemin d'accès au fichier template (relatif à la racine du site)
     * @param $select Document XML de sélection en entrée (voir cXMLTemplate::Initialise)
     * @param $attributes Tableau associatif des champs en entrée (voir cXMLTemplate::Initialise)
     */
    function showXMLView($filename,$attributes,$template_file="view/template.html"){
        $content = $this->makeXMLView($filename,$attributes,$template_file);
        echo $content;
    }

    /**
     * @brief Traitement a appliquer en cas d'erreur
     */
    public static function processLastError(){
        $result = cResult::getLast();
        if($result->code != cResult::Ok){
            header("content-type: text/plain");
            echo("The application encountered a fatal error.\n");
            echo("L'application à rencontrée une erreur fatale.\n");
            echo("\nCode\t: ");
            echo(isset(Error::$code[$result->code]) ? Error::$code[$result->code] : $result->code);
            echo("\nInfos\t: ");
            echo(isset(Error::$info[$result->info]) ? Error::$info[$result->info] : $result->info);
            if(!empty($result->att)){
                echo("\n\nAdditionnal:\n");
                print_r($result->att);
            }
            exit(-1);
        }
    }

    /** 
     * Convertie un résultat de procédure en un tableau humainement lisible
     * 
     * @param string $result Instance de la classe cResult
     * @return Tableau associatif contenant les champs traduits
     */
    public static function translateResult($result)
    {
        $att = $result->toArray();
        
        $default = new cXMLDefault();
        if($default->Initialise("default.xml")){

            $att["txt_result"]  = $default->getResultText("codes",$result->code);
            $att["txt_error"]   = $default->getResultText("errors",$result->info);
            if(isset($att["message"])){
                $att["txt_message"] = $default->getResultText("messages",$att["message"]);
                $att["txt_message"] = cHTMLTemplate::transform($att["txt_message"], $att);
            }
        }
        
        return $att;
    }
    
}

?>
