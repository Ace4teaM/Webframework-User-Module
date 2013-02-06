<?php
/*
 * Déconnecte tous les utilisateurs
 * Rôle : Administrateur
 * UC   : user_disconnect_all
 * 
 * Projet Webframework (GNU): Module Utilisateur
 * Auteur: Thomas Auguey
 */

require_once("inc/globals.php");
global $app;

//résultat de la requete
$result = NULL;

//supprime le compte utilisateur
if(!UserModule::disconnectAll())
    goto failed;

//retourne le resultat de cette fonction
$result = cResult::getLast();

goto success;

failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:

// Traduit le résultat
$att = Application::translateResult($result);

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
        echo $app->makeXMLView("view/user/pages/disconnect_all.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>