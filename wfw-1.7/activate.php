<?php
/*
 * Formulaire d'inscription
 */

require_once("inc/globals.php");
global $app;

$accountFields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "mail"=>"cInputMail",
    "token"=>"cInputName"
);

// exemples JS
if(cInputFields::checkArray($accountFields))
{
    //crée le compte utilisateur
    $result = UserModule::activateAccount($_REQUEST["uid"],$_REQUEST["pwd"],$_REQUEST["mail"],$_REQUEST["token"]);
    //ok
    /*if($result){
        header("Location: index.php");
    }*/
}

/* Ajoute le résultat aux champs du template */
$result = cResult::getLast();
$att = cResult::getLast()->toArray();

//traduit le nom des champs
if(isset($att["field_name"])){
    switch($att["field_name"]){
        case "mail":
            $att["field_name"] = "eMail";
            break;
        case "uid":
            $att["field_name"] = "Nom d'utilisateur";
            break;
        case "pwd":
            $att["field_name"] = "Mot-de-passe";
            break;
        case "token":
            $att["field_name"] = "Jeton";
            break;
    }
}

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
            $app->showXMLView("view/user/pages/activate.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/activate.html",$att);

?>