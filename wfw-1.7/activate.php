<?php
/*
 * Formulaire d'inscription
 */

require_once("inc/globals.php");
global $app;

$accountFields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "mail"=>"cInputMail",
    "token"=>"cInputName"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //crée le compte utilisateur
    $result = UserModule::activateAccount($_REQUEST["uid"],$_REQUEST["pwd"],$_REQUEST["mail"],$_REQUEST["token"]);
    //ok
    /*if($result){
        header("Location: index.php");
    }*/
}

/* Ajoute le résultat aux champs du template */
$result = cResult::getLast();
$att = cResult::getLast()->toArray();
//traduit le nom du champ
if(isset($att["field_name"]))
    $att["field_name"] = UserModule::translateAttributeName($att["field_name"]);

/* Ajoute les arguments reçues en entrée au template */
$att = array_merge($att,$_REQUEST);

/* Génére la sortie */
if(cInputFields::checkArray(array("output"=>"cInputIdentifier"))){
    switch($_REQUEST["output"]){
        case "xarg":
            header("content-type: text/xarg");
            echo xarg_encode_array($att);
            exit;
        case "html":
        default:
            $app->showXMLView("view/user/pages/activate.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/activate.html",$att);

?>