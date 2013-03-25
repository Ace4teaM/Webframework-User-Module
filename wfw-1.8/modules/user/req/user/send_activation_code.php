<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Active un compte utilisateur

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur
		[UNIXFileName] wfw_template  : Optionnel, template mail
	Retourne:
		uid        : Identificateur de l'utilisateur
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec

	Revisions:
		[21-01-2012] Implentation
*/

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../../")); //racine du site
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
	array('wfw_uid'=>'cInputName'),
	//optionnels
	array('wfw_template'=>'cInputUNIXFileName')
	);

$uid = $_REQUEST["wfw_uid"];
$template = (isset($_REQUEST["wfw_template"])?$_REQUEST["wfw_template"]:"_user_activation_mail.html");
	
//
// charge l'user
//
$doc = userOpenById($uid,&$client_id);
$mail = $doc->getNodeValue("data/wfw_mail",false);
if(empty($mail))
	rpost_result(ERR_FAILED,"invalid_user");

//
//globales
//
if(!file_exists(USER_PATH."/unactived/".$mail))
	rpost_result(ERR_FAILED,"account_already_activated");

//
//envoie un mail avec le code d'activation
//
if(!file_exists(ROOT_PATH."/private/req/mailling/send_client.php"))
	rpost_result(ERR_FAILED, "mail_module_not_found");

$args = array();
$args["to"]            = $mail;
$args["token"]         = file_get_contents(USER_PATH."/unactived/".$mail);
$result = xarg_req(ROOT_PATH.'/private/req/mailling/','send_client',$args);

if($result===null || $result["result"]!=ERR_OK)
{
	if(isset($args["result"])){
		foreach($args as $index=>$value)
			rpost($index,$value);
		rpost_result($args["result"],$args["info"]);
	}
	rpost("info", "send_client");
	rpost_result(ERR_FAILED, "sub_request");
}
/*
if(!file_exists(ROOT_PATH."/private/req/mailling/mail.php"))
	rpost_result(ERR_FAILED, "mail_module_not_found");
$token = file_get_contents(USER_PATH."/unactived/".$mail);



$args = array();
$args["to"]            = $mail;
$args["subject"]       = "Activation de votre compte client";
$args["msg"]           = "Votre code d'activation est le suivant: $token";

//
//verifie le template
//
if(file_exists(ROOT_PATH."/private/$template"))
{
	$args["html_mode"]    = "on";
	$args["use_template"] = "on";
	$args["template"]     = $template;
	$args["token"]        = $token;
}

$result = xarg_req(ROOT_PATH.'/private/req/mailling/','mail',$args);

if($result===null || $result["result"]!=ERR_OK)
{
	if(isset($result["result"])){
		rpost("req_result",$result["result"]);
		rpost("req_info",$result["info"]);
	}
	rpost_result(ERR_FAILED, "send_mail");
}*/

//
rpost("uid",$uid);
rpost_result(ERR_OK);
?>
