<?php
require_once("inc/globals.php");
global $app;

//print_r($app);

// exemples JS
if(cInputFields::checkArray(array("page"=>"cInputName")))
{
    $param = array();
    $app->showXMLView("view/pages/".$_REQUEST["page"].".html",$param);
    exit;
}

// accueil
$app->showXMLView("view/pages/index.html",array());

?>