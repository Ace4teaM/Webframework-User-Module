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

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($required_fields))
{
    if(!UserModule::checkConnection($_REQUEST["cid"],$_SERVER["REMOTE_ADDR"]))
        goto failed;
    //retourne le resultat de cette fonction
    $result = cResult::getLast();

    //actualise la tache de fermeture
    $taskMgr=NULL;
    $app->getTaskMgr($taskMgr);
    if($taskMgr!==NULL){
        $expire  = new DateTime();
        $expire->setTimestamp(intval($result->getAtt("EXPIRE")));
        $current = new DateTime();
        if($expire->getTimestamp() > $current->getTimestamp()){
            $taskName = UserModule::disconnectTaskName($result->getAtt("UID"));
            $taskCmd  = UserModule::disconnectTaskCmd($result->getAtt("UID"));
            if(!$taskMgr->create($taskName,$expire,$taskCmd))
                 goto failed;
        }
    }

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
        echo $app->makeXMLView("view/user/pages/check.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}


// ok
exit($result->isOk() ? 0 : 1);

?>