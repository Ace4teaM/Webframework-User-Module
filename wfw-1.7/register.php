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

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //crée l'e compte utilisateur'inscription
    if(!UserModule::registerAccount($_REQUEST["uid"],$_REQUEST["mail"]))
            goto failed;

    //retourne le resultat de cette fonction
    $result = cResult::getLast();

    //redirige vers la page d'activation
    //header("Location: activate.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);    
    //exit;
    
    goto success;
}

failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:
// Ajoute le résultat aux attributs du template
$att = $result->toArray();

//traduit le nom du champ
if(isset($att["field_name"]))
    $att["field_name"] = UserModule::translateAttributeName($att["field_name"]);

//traduit le code de résultat
if(isset($att["error"]))
    $att["error"] = UserModule::translateErrorCode($att["error"]);

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
        echo $app->makeXMLView("view/user/pages/register.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>