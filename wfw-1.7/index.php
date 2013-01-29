<?php
require_once("inc/globals.php");
global $app;

//print_r($app);

// charge une vue
if(cInputFields::checkArray(array("page"=>"cInputName")))
{
    $param = array();
    $app->showXMLView("view/user/pages/".$_REQUEST["page"].".html",$param);
    exit;
}

$att = array(
    "bdd_status"=>"Indisponible, vérifiez la configuration de l'application et l'installation de votre SGBD"
);

if($app->getDB($db_iface)){
    $att["bdd_status"] = $db_iface->getServiceProviderName();
    $att["bdd_status"] .= " ( ".$app->getCfgValue("database", "name")." @ ".$app->getCfgValue("database", "server")." : ".$app->getCfgValue("database", "port")." )";
}

// accueil
$app->showXMLView("view/user/pages/index.html",$att);

?>