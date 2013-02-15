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

/**
 * Formulaire d'inscription
 * Rôle : Visiteur
 * UC   : user_register_account
 */

require_once("inc/globals.php");
global $app;

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

//entree
$fields = array(
    "uid"=>"cInputIdentifier",
    "mail"=>"cInputMail"
);

//module mail requis ?
if(!class_exists("MailModule") && $app->getCfgValue("user_module","requires_mail_module")){
    RESULT(cResult::Failed,Application::ModuleClassNotFound,array("module_name"=>"mail"));
    $app->processLastError();
}

if(!empty($_REQUEST))
{
    // exemples JS
    if(!cInputFields::checkArray($fields))
        goto failed;
    
    $client_id = "none";

    //crée l'e compte utilisateur'inscription
    if(!UserModule::registerAccount($_REQUEST["uid"],$_REQUEST["mail"]))
            goto failed;

    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //pas de module Mail ?
    if(!class_exists("MailModule"))
        goto activate;
    
    //utile a la generation du message
    if(!$app->getDefaultFile($default))
        goto activate;
    
    //--------------------------------------------
    //initialise le message
    
    $msg = new MailMessage();
    $msg->to       = $_REQUEST["mail"];
    $msg->subject  = "Activation";
    
    //attributs du template
    $template_att = $_REQUEST;
    $template_att["TOKEN"] = $result->getAtt("token");
    $template_att["ACTIVATION_LINK"] = $app->getBaseURI()."/".$default->getIndexValue("page","activate")."?token=".$result->getAtt("token")."&uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"];

    //depuis un template ?
    $template = $app->getCfgValue("user_module","activation_mail");
    if(!empty($template) && file_exists($template)){
        $msg->msg      = cHTMLTemplate::transformFile($template,$template_att);
        $msg->contentType = mime_content_type($template);
    }
    //depuis le message standard ?
    else{
        $msg->msg      = cHTMLTemplate::transform($default->getResultText("messages","USER_ACTIVATION_MAIL"),$template_att);
        $msg->contentType = "text/plain";
    }

    //envoie le message
    if(!MailModule::sendMessage($msg))
        goto failed;
    //ajoute un message d'avertissement
    $result->addAtt("message","USER_MSG_ACTIVATE_BY_MAIL");
    //header("Location: get_activatation.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);
    //exit;

    //redirige vers la page d'activation
    //header("Location: activate.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);    
    //exit;
}

//-------------------------------------------------------------------------------------------------------
//Active le compte en cas d'impossibilité d'envoyer un mail
goto success;
activate:
    
$pwd   = rand(1615,655641);
//crée le compte utilisateur'inscription
if(!UserModule::activateAccount($_REQUEST["uid"], $pwd, $_REQUEST["mail"], $result->getAtt("token")))
    goto failed;
//ajoute un message d'avertissement
$result->addAtt("pwd",$pwd);
$result->addAtt("message","USER_MSG_AUTO_ACTIVATE");

//-------------------------------------------------------------------------------------------------------
//En cas d'echec de la procedure
goto success;
failed:
    
// utilise le dernier resultat
$result = cResult::getLast();

//-------------------------------------------------------------------------------------------------------
//Termine
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
        echo $app->makeFormView($att,$fields,NULL,$_REQUEST,NULL);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>