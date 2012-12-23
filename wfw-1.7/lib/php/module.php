<?php

class UserModule
{
    public static function test(){
        global $app;

        if(UserAccountMgr::getById($user,'AceteaM'))
                return print_r($user,true);
    }
    
    public static function makeView($name,$attributes,$template_file){ 
        global $app;

        $template = new cXMLTemplate();
        
        //charge le contenu en selection
        $select = new XMLDocument("1.0", "utf-8");
        $select->load($app->getLibPath("user_mod").'/public/view/pages/'.$name);

        //ajoute le fichier de configuration
        $template->load_xml_file($app->getLibPath("user_mod").'/default.xml',$app->getLibPath("user_mod"));
        
        //initialise la classe template 
        if(!$template->Initialise(
                    $app->root_path.'/'.$template_file,
                    NULL,
                    $select,
                    NULL,
                    array_merge($attributes,$app->template_attributes) ) )
                return false;

        //transforme le fichier
	return $template->Make();
    }
}

?>
