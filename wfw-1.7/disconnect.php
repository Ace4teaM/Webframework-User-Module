<?php
/*
 * Déconnecte l'utilisateur en cours
 * Rôle : Utilisateur
 * UC   : user_disconnect
 * 
 * Projet Webframework (GNU): Module Utilisateur
 * Auteur: Thomas Auguey
 */

require_once("inc/globals.php");
global $app;

//entree
$fields = array(
    "cid"=>"cInputName"
);

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($fields,NULL,$_COOKIE))
{
    //supprime le compte utilisateur
    if(!UserModule::disconnect($_COOKIE["cid"]))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //supprime le cookie
    setcookie("cid",NULL,time()-1);

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
        echo $app->makeXMLView("view/user/pages/disconnect.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>