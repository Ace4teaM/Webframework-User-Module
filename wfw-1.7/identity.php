<?php
/*
 * Formulaire d'identité
 */

require_once("inc/globals.php");
global $app;

$accountFields = array(
    "user_id"=>"cInputInteger",
    "last_name"=>"cInputName",
    "first_name"=>"cInputName",
    "birth_day"=>"",
    "sex"=>""
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //verifie les informations de connexion
    //... $_REQUEST["user_id"]
 
    //obtient le compte utilisateur
    if(!UserModule::makeIdentity($_REQUEST["user_id"], $_REQUEST["first_name"], $_REQUEST["last_name"], $_REQUEST["birth_day"], $_REQUEST["sex"]))
            $app->processLastError();
    
    //obtient/initialise l'identité
 /*   $identity = NULL;
    if(!UserIdentityMgr::getByRelation($identity,$account)){
        echo("add");
        //crée une nouvelle entree
        $identity = new UserIdentity();
        $identity->userIdentityId = NULL;
        if(!UserIdentityMgr::create($identity))
            $app->processLastError();
        //met a jour le compte
        if(!UserAccountMgr::update($account))
            $app->processLastError();
    }
    print_r($identity);
    
    //met a jour l'identité
    $identity->firstName = $_REQUEST["first_name"];
    $identity->lastName  = $_REQUEST["last_name"];
    $identity->birthDay  = $_REQUEST["birth_day"];
    $identity->sex       = $_REQUEST["sex"];
    if(!UserIdentityMgr::update($identity))
        $app->processLastError();*/
    
}

$result = cResult::getLast();
$att = array("error_code"=>$result->code, "error_str"=>$result->info);

// accueil
$app->showXMLView("view/user/pages/identity.html",$att);

?>