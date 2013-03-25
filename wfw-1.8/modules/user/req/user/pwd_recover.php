<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Récuperation de mot de passe
	Le mot_de_passe en cours est envoyé par mail à l'utilisateur concerné

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur (Optionnel si wfw_mail est définit)
		[Mail]         wfw_mail      : Adresse email (Optionnel si wfw_uid est définit)
		[UNIXFileName] wfw_template  : Optionnel, template mail
	Retourne:
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec

	Revisions:
		[01-02-2012] Implentation
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
// Prepare la requete pour repondre à un formulaire
//
useFormRequest();

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	null,
	//optionnels
	array('subject'=>'cInputString','wfw_uid'=>'cInputName','wfw_mail'=>'cInputMail','wfw_template'=>'cInputUNIXFileName')
);

$uid = isset($_REQUEST["wfw_uid"])?$_REQUEST["wfw_uid"]:null;
$mail = isset($_REQUEST["wfw_mail"])?$_REQUEST["wfw_mail"]:null;
$template = (isset($_REQUEST["wfw_template"])?$_REQUEST["wfw_template"]:"_user_pwd_recover_mail.html");

//
// charge le fichier xml
//
if($uid==null && $mail==null)
	rpost_result(ERR_FAILED,"no_user_id");

if($uid!==null)
	$doc = userOpenById($uid,&$client_id);
else
	$doc = userOpenByMail($mail,&$client_id);

//
// le compte est actif ?
//
clientCheckActivation($doc);

//charge le fichier default 
$default = new cXMLDefault();
if(!$default->Initialise(ROOT_PATH."/default.xml")){
	rpost_result(ERR_FAILED, "cant_open_default_file");
}

//
//envoie un mail avec le code d'activation
//
if($default->getModuleConfigNode("mailling")===null)
	rpost_result(ERR_FAILED, "mailling_module_not_found");

$args = array();
$args["to"]            = $doc->getNodeValue("data/wfw_mail",false);
$args["from"]          = $default->getIndexValue("mail","no-reply");
$args["server"]        = $default->getIndexValue("smtp_server",$_hostname_);
$args["port"]          = $default->getIndexValue("smtp_port",$_hostname_);
$args["fromname"]      = $default->getValue("description");
$args["subject"]       = (isset($_REQUEST["subject"])?$_REQUEST["subject"]:"Rappel de votre mot-de-passe");
$args["wfw_uid"]       = $doc->getNodeValue("data/wfw_uid",false);
$args["wfw_mail"]      = $doc->getNodeValue("data/wfw_mail",false);
$args["wfw_pwd"]       = $doc->getNodeValue("data/wfw_pwd",false);
$args["html_mode"]     = "on";
$args["use_template"]  = "on";
$args["template"]      = $template;

//
//envoie le mail
//
$result = xarg_req(ROOT_PATH.'/private/req/mailling/','mail',$args);
if($result===null || $result["result"]!=ERR_OK)
{
	if(isset($result["result"])){
		rpost("req_result",$result["result"]);
		rpost("req_info",$result["info"]);
	}
	rpost_result(ERR_FAILED, "send_mail");
}

//
rpost_result(ERR_OK);
?>
