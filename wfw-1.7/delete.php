<?php
/*
 * Supprime un compte
 * Rôle : Administrateur
 * UC   : user_delete_account
 */

require_once("inc/globals.php");
global $app;

//entree
$accountFields = array(
    "uid"=>"cInputIdentifier"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //supprime le compte utilisateur
    $result = UserModule::deleteAccount($_REQUEST["uid"]);
}

/* Ajoute le résultat au champs du template */
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
            $app->showXMLView("view/user/pages/delete.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/delete.html",$att);

?>