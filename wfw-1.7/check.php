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