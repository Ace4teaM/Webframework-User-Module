<?php
/*
 * Formulaire d'inscription
 * L'inscription passe par une phase d'activation
 */

require_once("inc/globals.php");
global $app;

//entree
$accountFields = array(
    "uid"=>"cInputIdentifier",
    "mail"=>"cInputMail"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //crée l'e compte utilisateur'inscription
    $result = UserModule::registerAccount($_REQUEST["uid"],$_REQUEST["mail"]);
    //ok ?
    if($result){
        //envoie un mail de confirmation
        //MailModule::send();
        //redirige vers la page d'activation
        header("Location: activate.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);
        exit;
    }
}

/* Ajoute le résultat au champs du template */
$result = cResult::getLast();
$att = cResult::getLast()->toArray();
//traduit le nom du champs
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
            $app->showXMLView("view/user/pages/register.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/register.html",$att);

?>