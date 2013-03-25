<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Crée un dossier user

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur
	Retourne:
		id         : Identificateur de l'utilisteur supprimé
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[13-01-2012] Implentation
		[23-01-2012] Update
*/

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../../../")); //racine du site
include(ROOT_PATH.'/wfw/php/base.php');
include_path(ROOT_PATH.'/wfw/php/');
include_path(ROOT_PATH.'/wfw/php/class/bases/');
include_path(ROOT_PATH.'/wfw/php/inputs/');

include(ROOT_PATH.'/req/user/path.inc');
include(ROOT_PATH.'/req/user/user.inc');

//
// Prepare la requete pour repondre a un formulaire
//
useFormRequest();

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	array('wfw_uid'=>'cInputName'/*,'wfw_pwd'=>'cInputPassword'*/),
	//optionnels
	null
);

$uid = $_REQUEST["wfw_uid"];

//
// charge le fichier xml
//
$doc = userOpenById($uid,&$client_id);

//
//deconnect l'utilisateur
//
$sid = $doc->getNodeValue("data/wfw_sid",false);
if(!empty($sid)){
	$args = array();
	$args["wfw_usid"] = $sid;
	$result = xarg_req(ROOT_PATH.'/private/req/user/','logout',$args);
}

//
//supprime les assignations
//
if(file_exists(USER_PATH."/byid/$uid"))
	unlink(USER_PATH."/byid/$uid");

$mail = $doc->getNodeValue("data/wfw_mail",false);
if(file_exists(USER_PATH."/bymail/$mail"))
	unlink(USER_PATH."/bymail/$mail");

if(file_exists(USER_PATH."/unactived/$mail"))
	unlink(USER_PATH."/unactived/$mail");

//
// Supprime le dossier client
//
$args = array();
$args["wfw_id"] = $client_id;
$result = xarg_req(ROOT_PATH.'/private/req/client/','remove',$args);

if($result===NULL)
	rpost_result(ERR_FAILED, "sub_request_error (client.remove)");

if($result["result"] != ERR_OK)
	rpost_result($result["result"], $result["info"]);

//
rpost("uid",$uid);
rpost_result(ERR_OK);
?>
