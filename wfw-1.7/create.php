<?php
/*
 * Formulaire d'inscription
 */

require_once("inc/globals.php");
global $app;

$accountFields = array(
    "mail"=>"cInputMail",
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //crée le compte utilisateur
    $result = UserModule::createAccount($_REQUEST["uid"],$_REQUEST["pwd"],$client_id,$_REQUEST["mail"]);
    if(false === $result)
        $app->processLastError();
    
    //ok
    //header("Location: user_account_activation.php");
    print_r($result);
    exit;
}

$result = cResult::getLast();
$att = array("error_code"=>$result->code, "error_str"=>$result->info);

// accueil
$app->showXMLView("view/user/pages/create.html",$att);

?>