<?php

require_once("php/class/bases/iModule.php");
require_once("php/xml_default.php");


class UserModule implements iModule
{
    //intialise l'application
    public static function load($path){
        global $app;

        //initialise la configuration
        $modParam = parse_ini_file("$path/config.ini", true);
        $app->config = array_merge_recursive($modParam,$app->config);
        //inclue le model de données
        require_path($path."/".$app->config["user_module"]["lib_path"]);
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
}

?>
