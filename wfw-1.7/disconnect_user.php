<?php
/*
 * Déconnect un utilisateur
 * Rôle : Administrateur
 * UC   : user_delete_account
 */

require_once("inc/globals.php");
global $app;

//entree
$accountFields = array(
    "uid"=>"cInputIdentifier"
);

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //supprime le compte utilisateur
    if(!UserModule::disconnectUser($_REQUEST["uid"]))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    goto success;
}

failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:
// Ajoute le résultat aux attributs du template
$att = $result->toArray();
//traduit le nom du champs
if(isset($att["field_name"]))
    $att["field_name"] = UserModule::translateAttributeName($att["field_name"]);
/* Ajoute les arguments reçues en entrée au template */
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
        echo $app->makeXMLView("view/user/pages/disconnect_user.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}


// ok
exit($result->isOk() ? 0 : 1);

?>