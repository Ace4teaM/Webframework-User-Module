<?php

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../")); //racine du site


/* Systeme */
if(PHP_OS == "WINNT"){
    define('WINDOWS',true);
    define('SYSTEM','WINDOWS');
}
else{
    define('LINUX',true);
    define('SYSTEM','LINUX');
}

require_once("inc/Application.php");

//instancie l'application
global $app;
$app = new Application(ROOT_PATH);

set_include_path(get_include_path() . PATH_SEPARATOR . $app->getLibPath('wfw'));

require_once("php/xarg.php");
require_once("php/class/bases/cResult.php");
require_once("php/templates/cHTMLTemplate.php");
require_once("php/templates/xml_template.php");

//charge la classe de la base de données
$db_classname = $app->getCfgValue("database", "class");
if(!empty($db_classname))
    require_once($app->getLibPath("wfw")."/php/$db_classname.php");

//charge la classe du gestionnaire de taches
$classname = $app->getCfgValue(constant("SYSTEM"), "taskmgr_class");
if(!empty($classname))
    require_once($app->getLibPath("wfw")."/php/system/windows/$classname.php");


//librairies...
require_path($app->getCfgValue("user_module", "lib_path")."/");
?>