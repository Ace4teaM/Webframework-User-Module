<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2012-2013 Thomas AUGUEY <contact@aceteam.org>
    ---------------------------------------------------------------------------------------------------------------------------------------
    This file is part of WebFrameWork.

    WebFrameWork is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebFrameWork is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebFrameWork.  If not, see <http://www.gnu.org/licenses/>.
    ---------------------------------------------------------------------------------------------------------------------------------------
*/

/*
 * Connexion d'un utilisateur
 * Rôle : Visiteur
 * UC   : user_connect
 */

require_once("inc/globals.php");
global $app;

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

//entree
$fields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "life_time"=>"cInputInteger"
);

if(!empty($_REQUEST)){

    // exemples JS
    if(!cInputFields::checkArray($fields))
        goto failed;
    
    $client_ip  = $_SERVER["REMOTE_ADDR"];
    $local_path = NULL;
    $life_time  = intval($_REQUEST["life_time"]);

    if(!UserModule::checkAuthentication($_REQUEST["uid"], $_REQUEST["pwd"]))
        goto failed;
    
    //crée une connexion
    if(!UserModule::connectUser($_REQUEST["uid"], $client_ip, $local_path, $_REQUEST["life_time"]))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //définit le cookie
    setcookie("cid",$result->getAtt("CONNECTION_ID"));
    
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
failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:

// Traduit le nom du champ concerné
if(isset($result->att["field_name"]) && $app->getDefaultFile($default))
    $result->att["field_name"] = $default->getResultText("fields",$result->att["field_name"]);

// Traduit le résultat
$att = $app->translateResult($result);

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
        echo $app->makeFormView($att,$fields,NULL,$_REQUEST);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}


// ok
exit($result->isOk() ? 0 : 1);

?>