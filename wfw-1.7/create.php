<?php
/*
 * Crée un compte utilisateur
 * Rôle : Administrateur
 * UC   : user_create_account
 */

require_once("inc/globals.php");
global $app;


$accountFields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "mail"=>"cInputMail"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //crée le compte utilisateur
    $result = UserModule::createAccount($_REQUEST["uid"],$_REQUEST["pwd"],NULL,$_REQUEST["mail"]);
    //if(false === $result)
    //    $app->processLastError();
    
    //ok
    //header("Location: user_account_activation.php");
}

/* Ajoute le résultat aux champs du template */
$result = cResult::getLast();
$att = cResult::getLast()->toArray();
//traduit le nom du champs
$att["field_name"] = UserModule::translateAttributeName($att["field_name"]);


/* Ajoute les arguments reçues en entrée au template */
$att = array_merge($att,$_REQUEST);

/* Génére la sortie */
if(cInputFields::checkArray(array("output"=>"cInputIdentifier"))){
    switch($_REQUEST["output"]){
        case "xarg":
            //XArg::makeArray($att);
            RESULT(cResult::Failed,Application::UnsuportedFeature);
            RESULT_PUSH("cause","XARG output format");
            $app->processLastError();
            break;
        case "html":
        default:
            $app->showXMLView("view/user/pages/create.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/create.html",$att);

?>