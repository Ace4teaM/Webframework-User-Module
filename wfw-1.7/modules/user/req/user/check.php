<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	obtient des infos sur un utilisateur

	Arguments:
		[Name]     wfw_uid  : Identificateur de l'utilisateur
		[Name]     wfw_usid : Optionnel, Identificateur de session (obtient des informations sur l'état de la session)
		[Password] wfw_pwd  : Optionnel, Mot de passe (obtient des informations sur l'état de la session)

	Retourne:
		session_status : Status de la session (USER_STATUS_OPEN, USER_STATUS_CLOSE, USER_STATUS_ACTIVATE)
		session_path   : Si 'wfw_usid' ou 'wfw_pwd' est définit, chemin d'accès aux fichiers de la session
		usid           : Si 'wfw_usid' ou 'wfw_pwd' est définit, identificateur de session
		cid            : Si 'wfw_usid' ou 'wfw_pwd' est définit, identificateur du dossier client
		result         : Résultat de la requête
		info           : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[20-01-2012] Implentation
		[21-01-2012] Refonte
		[23-01-2012] Update
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

$uid     = $_REQUEST["wfw_uid"];

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	array('wfw_uid'=>'cInputName'),
	//optionnels
	array('wfw_pwd'=>'cInputPassword','wfw_usid'=>'cInputName')
);

//
// charge le fichier xml
//
$doc = cUser::openById($uid,&$client_id);
result_check();

//
// actif ?
//
$mail = $doc->getNodeValue("data/wfw_mail",false);
if(empty($mail))
	rpost_result(ERR_FAILED,"invalid_user");

// le compte est actif ?
if(file_exists(USER_PATH."/unactived/".$mail))
	rpost("session_status",USER_STATUS_ACTIVATE);
else{
	// test la session...
	$sid = $doc->getNodeValue("data/wfw_sid",false);
	$pwd = $doc->getNodeValue("data/wfw_pwd",false);
	
	if(file_exists(USER_SID_PATH."/$sid")){
		rpost("session_status",USER_STATUS_OPEN);
	}
	else{
		rpost("session_status",USER_STATUS_CLOSED);
	}
	
	if((isset($_REQUEST["wfw_usid"]) && ($_REQUEST["wfw_usid"] == $sid)) || (isset($_REQUEST["wfw_pwd"]) && ($_REQUEST["wfw_pwd"] == $pwd))){
		rpost("session_path",USER_SID_DIR);
		rpost("usid",$sid);
		rpost("cid",$client_id);
	}
}

rpost("uid",$uid);
rpost_result(ERR_OK);
?>
