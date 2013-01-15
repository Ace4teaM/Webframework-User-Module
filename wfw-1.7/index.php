<?php
require_once("inc/globals.php");
global $app;

//print_r($app);

// exemples JS
if(cInputFields::checkArray(array("page"=>"cInputName")))
{
    $param = array();
    $app->showXMLView("view/user/pages/".$_REQUEST["page"].".html",$param);
    exit;
}

// accueil
$app->showXMLView("view/user/pages/index.html",array());

?>