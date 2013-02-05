<?php
/*
 * Crée un compte utilisateur
 * Rôle : Administrateur
 * UC   : user_create_account
 */

require_once("inc/globals.php");
global $app;


$accountFields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "mail"=>"cInputMail"
);

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //crée le compte utilisateur
    if(!UserModule::createAccount($_REQUEST["uid"],$_REQUEST["pwd"],NULL,$_REQUEST["mail"]))
            goto failed;
 
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //ok
    //header("Location: user_account_activation.php");
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
        echo $app->makeXMLView("view/user/pages/create.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}


// ok
exit($result->isOk() ? 0 : 1);

?>