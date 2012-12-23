<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Supprime un utilisateur

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur (Optionnel si wfw_mail est définit)
		[Mail]         wfw_mail      : Mail de contact (Optionnel si wfw_uid est définit)
		[Password]     wfw_pwd       : Mot de passe
	Retourne:
		uid        : Identificateur de l'utilisateur supprimé
		cid        : Identificateur du dossier client supprimé
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[12-01-2012] Implentation
		[21-01-2012] Refonte
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
	array('wfw_pwd'=>'cInputPassword'),
	//optionnels
	array('wfw_uid'=>'cInputName','wfw_mail'=>'cInputMail')
);

//
//globales
//
if(isset($_REQUEST["wfw_uid"]))
	$doc=userOpenById($_REQUEST["wfw_uid"],$client_id);
else if (isset($_REQUEST["wfw_mail"]))
	$doc=userOpenByMail($_REQUEST["wfw_mail"],$client_id);
else
	rpost_result(ERR_FAILED, "no_user_identifier");

$mail = $doc->getNodeValue("data/wfw_mail",false);
$uid  = $doc->getNodeValue("data/wfw_uid",false);
$sid  = $doc->getNodeValue("data/wfw_sid",false);

//
//supprime les liens
//
if(!empty($mail) && file_exists(USER_PATH."/bymail/$mail"))
	unlink(USER_PATH."/bymail/$mail");
if(!empty($uid) && file_exists(USER_PATH."/byid/$uid"))
	unlink(USER_PATH."/byid/$uid");
if(!empty($mail) && file_exists(USER_PATH."/unactived/$mail"))
	unlink(USER_PATH."/unactived/$mail");
if(!empty($sid) && file_exists(USER_SID_PATH."/$sid"))
	unlink(USER_SID_PATH."/$sid");

//supprime le dossier client
$args = array();
$args["wfw_id"] = $client_id;
$result = xarg_req(ROOT_PATH.'/private/req/client/','remove',$args);

if($result===NULL)
	rpost_result(ERR_FAILED, "client_remove");

if($result["result"] != ERR_OK)
	rpost_result($result["result"], $result["info"]);

//
// supprime la tache de fermeture (si elle existe)
//
$www_dir       = ROOT_PATH;
$web_name      = basename($www_dir); 
$task_name     = "user_session_logout_$client_id";

$out = array(); 
$cmd = "$www_dir/wfw/sh/rem_cron_task.sh '$web_name' '$task_name'";
exec($cmd,$out,$cmd_ret);

//
rpost("cid",$client_id);
rpost("uid",$uid);
rpost_result(ERR_OK);
?>
