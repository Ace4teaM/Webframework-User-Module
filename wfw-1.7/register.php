<?php
/*
 * Formulaire d'inscription
 * Rôle : Visiteur
 * UC   : user_register_account
 * 
 * Projet Webframework (GNU): Module Utilisateur
 * Auteur: Thomas Auguey
 */

require_once("inc/globals.php");
global $app;

//entree
$accountFields = array(
    "uid"=>"cInputIdentifier",
    "mail"=>"cInputMail"
);

//module mail requis ?
if(!class_exists("MailModule") && $app->getCfgValue("user_module","requires_mail_module")){
    RESULT(cResult::Failed,Application::ModuleClassNotFound,array("module_name"=>"mail"));
    $app->processLastError();
}

//résultat de la requete
$result = NULL;

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    $client_id = "none";

    //crée l'e compte utilisateur'inscription
    if(!UserModule::registerAccount($_REQUEST["uid"],$_REQUEST["mail"]))
            goto failed;

    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //envoie un mail d'activation
    if(class_exists("MailModule")){
        //--------------------------------------------
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        //--------------------------------------------
        
        //envoie le message
        if(!MailModule::send())
            goto failed;
        //ajoute un message d'avertissement
        $result->addAtt("message","USER_MSG_ACTIVATE_BY_MAIL");
        //header("Location: get_activatation.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);
        //exit;
    }
    //sinon, active le compte
    else{
        $pwd   = rand(1615,655641);
        //crée le compte utilisateur'inscription
        if(!UserModule::activateAccount($_REQUEST["uid"], $pwd, $_REQUEST["mail"], $result->getAtt("token")))
                goto failed;
        //ajoute un message d'avertissement
        $result->addAtt("pwd",$pwd);
        $result->addAtt("message","USER_MSG_AUTO_ACTIVATE");
    }

    //redirige vers la page d'activation
    //header("Location: activate.php?uid=".$_REQUEST["uid"]."&mail=".$_REQUEST["mail"]);    
    //exit;
    
    goto success;
}

failed:
// utilise le dernier resultat
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
        echo $app->makeXMLView("view/user/pages/register.html",$att);
        break;
    default:
        RESULT(cResult::Failed,Application::UnsuportedFeature);
        $app->processLastError();
        break;
}

// ok
exit($result->isOk() ? 0 : 1);

?>