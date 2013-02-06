<?php
/*
 * Renseigne l'identité d'un utilisateur
 * Rôle : Utilisateur
 * UC   : user_identity
 * 
 * Projet Webframework (GNU): Module Utilisateur
 * Auteur: Thomas Auguey
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

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //verifie les informations de connexion
    //... $_REQUEST["user_id"]
 
    //obtient le compte utilisateur
    if(!UserModule::makeIdentity($_REQUEST["user_id"], $_REQUEST["first_name"], $_REQUEST["last_name"], $_REQUEST["birth_day"], $_REQUEST["sex"]))
            goto failed;

    //retourne le resultat de cette fonction
    $result = cResult::getLast();
      
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
    
    goto success;  
}

failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:

// Traduit le nom du champ concerné
if(isset($result->att["field_name"]))
    $result->att["field_name"] = UserModule::translateAttributeName($result->att["field_name"]);

// Traduit le résultat
$att = Application::translateResult($result);

// Ajoute les arguments reçues en entrée au template
$att = array_merge($att,$_REQUEST);

/* Génére la sortie */
$format = "html";
if(cInputFields::checkArray(array("output"=>"cInputIdentifier")))
    $format = $_REQUEST["output"] ;

switch($format){
    case "xarg":
        header("content-type: text/xarg");
        echo xarg_encode_array($att);
        break;
    case "html":
        echo $app->makeXMLView("view/user/pages/identity.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>