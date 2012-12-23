<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	UC1: Crée un utilisateur

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur
		[Password]     wfw_pwd       : Mot de passe
		[Mail]         wfw_mail      : Mail de contact
	Retourne:
		uid        : Identificateur de l'utilisateur
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[12-01-2012] Implentation
		[21-01-2012] Refonte
		[23-01-2012] Update
		[21-07-2012] Refonte, utilisation de la classe cUser
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
//verifie les paramètres
//
rcheck(
	//requis
	array('wfw_uid'=>'cInputName','wfw_pwd'=>'cInputPassword','wfw_mail'=>'cInputMail'),
	//optionnels
	null
);

//
//globales
//

//cree un dossier client
$client_result = cClient::create($_REQUEST,"user",true);
result_check();

//cree l'utilisateur
cUser::create($_REQUEST["wfw_uid"],$_REQUEST["wfw_mail"],$_REQUEST["wfw_pwd"],$client_result["id"]);
result_check();

//
//crée les liens dans le systeme de fichier
//
$client_filename = CLIENT_DATA_PATH."/".$client_result["id"].".xml";

if(!symlink($client_filename, USER_PATH."/bymail/$mail"))
        return proc_result(ERR_SYSTEM,"cant_link_user");
if(!symlink($client_filename, USER_PATH."/byid/$id"))
        return proc_result(ERR_SYSTEM,"cant_link_user");

//
//desactive le compte
//
cUser::activate($_REQUEST["wfw_mail"], false);
result_check();

//
// Envoie un mail d'activation
//
$mail_args = array();
$mail_args["to"]            = $mail;
$mail_args["token"]         = file_get_contents(USER_PATH."/unactived/".$mail);

cMailling.sendClient("_user_activation_mail.html",$mail,$mail_args);
result_check();

//
rpost("cid",$client_result["id"]);
rpost("uid",$_REQUEST["wfw_uid"]);
rpost_result(ERR_OK);
?>
