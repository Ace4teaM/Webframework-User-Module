<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	obtient des infos sur un utilisateur

	Arguments:
		[Name]     wfw_uid  : Identificateur de l'utilisateur

	Retourne:
		uid          : Identificateur de l'utilisateur
		mail         : Mail
		session_path : Chemin d'accès aux fichiers de la session (si wfw_usid est définit et valide)
		usid         : Identificateur de session (si wfw_usid est définit)
		session      : Status de la session (si wfw_usid est définit)
		client_id    : Identificateur du dossier client (si wfw_pwd est définit)
		activation   : Etat de l'activation du compte ("active" ou "unactive")
		result       : Résultat de la requête
		info         : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[23-01-2012] Implentation
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

$uid     = $_REQUEST["wfw_uid"];

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	array('wfw_uid'=>'cInputName'),
	//optionnels
	null
);

//
// charge le fichier xml
//
$doc = userOpenById($uid,&$client_id);

//
// actif ?
//
$mail = $doc->getNodeValue("data/wfw_mail",false);
if(empty($mail))
	rpost_result(ERR_FAILED,"invalid_user");

rpost("activation",(file_exists(USER_PATH."/unactived/".$mail) ? "unactive" : "active"));

//
// test la session...
//
$sid = $doc->getNodeValue("data/wfw_sid",false);
rpost("session_status",((!empty($sid)&&file_exists(USER_SID_PATH."/$sid")) ? USER_STATUS_OPEN : USER_STATUS_CLOSED));
rpost("session_path",USER_SID_DIR);
rpost("usid",$sid);
rpost("mail",$doc->getNodeValue("data/wfw_mail",false));
rpost("session_ip",$doc->getNodeValue("data/wfw_session_ip",false));
rpost("expire","N/A");
rpost("session_date",$doc->getNodeValue("data/wfw_session_date",false));
rpost("session_lifetime",$doc->getNodeValue("data/wfw_session_lifetime",false));

//
//infos privées
//
rpost("cid",$client_id);
rpost("client_id",$client_id);

rpost("uid",$uid);
rpost_result(ERR_OK);
?>
