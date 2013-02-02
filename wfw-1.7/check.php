<?php
/*
 * Maintient la connexion d'un utilisateur
 * Rôle : Utilisateur
 * UC   : user_check_connection
 */

require_once("inc/globals.php");
global $app;

//entree
$required_fields = array(
    "cid"=>"cInputName"
);

// exemples JS
if(cInputFields::checkArray($required_fields))
{
    if(!UserModule::checkConnection($_REQUEST["cid"],$_SERVER["REMOTE_ADDR"]))
        goto end;
}

end:
/* Ajoute le résultat au champs du template */
$result = cResult::getLast();
$att = cResult::getLast()->toArray();
//traduit le nom du champ
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
            $app->showXMLView("view/user/pages/check.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/check.html",$att);

?>