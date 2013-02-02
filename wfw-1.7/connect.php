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

// exemples JS
if(cInputFields::checkArray($required_fields))
{
    $client_ip  = $_SERVER["REMOTE_ADDR"];
    $local_path = NULL;

    if(!UserModule::checkAuthentication($_REQUEST["uid"], $_REQUEST["pwd"]))
        goto end;
    
    //crée une connexion
    if(UserModule::connectUser($_REQUEST["uid"], $client_ip, $local_path, $_REQUEST["life_time"])){
        $result = cResult::getLast();
        //setcookie("wfw_user_uid",$_REQUEST["uid"]);
        setcookie("wfw_user_cid",$result->att["CONNECTION_ID"]);
    }
}

end:
/* Ajoute le résultat au champs du template */
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
            header("content-type: text/xarg");
            echo xarg_encode_array($att);
            exit;
        case "html":
        default:
            $app->showXMLView("view/user/pages/connect.html",$att);
            break;
    }
}

// accueil
$app->showXMLView("view/user/pages/connect.html",$att);

?>