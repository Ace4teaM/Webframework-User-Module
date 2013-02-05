<?php
/*
 * Connexion d'un utilisateur
 * Rôle : Visiteur
 * UC   : user_connect
 */

require_once("inc/globals.php");
global $app;

//entree
$required_fields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "life_time"=>"cInputInteger"
);

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($required_fields))
{
    $client_ip  = $_SERVER["REMOTE_ADDR"];
    $local_path = NULL;
    $life_time  = intval($_REQUEST["life_time"]);

    if(!UserModule::checkAuthentication($_REQUEST["uid"], $_REQUEST["pwd"]))
        goto failed;
    
    //crée une connexion
    if(UserModule::connectUser($_REQUEST["uid"], $client_ip, $local_path, $_REQUEST["life_time"])){
        //retourne le resultat de cette fonction
        $result = cResult::getLast();
        //setcookie("wfw_user_uid",$_REQUEST["uid"]);
        //définit le cookie
        setcookie("wfw_user_cid",$result->getAtt("CONNECTION_ID"));
        //initialise la tache de fermeture
        $taskMgr=NULL;
        $app->getTaskMgr($taskMgr);
        if($taskMgr !== null && $life_time > 0){
            $taskName = UserModule::disconnectTaskName($_REQUEST["uid"]);
            $taskCmd  = UserModule::disconnectTaskCmd($_REQUEST["uid"]);
            $expire   = new DateTime();
            $expire->add(new DateInterval('P0Y0DT0H'.$life_time.'M'));
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

//traduit le nom du champs
if(isset($att["field_name"]))
    $att["field_name"] = UserModule::translateAttributeName($att["field_name"]);

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
        echo $app->makeXMLView("view/user/pages/connect.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}


// ok
exit($result->isOk() ? 0 : 1);

?>