<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	UC1P: Crée un utilisateur

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur
		[Password]     wfw_pwd       : Mot de passe
		[Mail]         wfw_mail      : Mail de contact
		[Bool]         wfw_active    : Active le compte ?
	Retourne:
		uid        : Identificateur de l'utilisateur
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
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

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	array('wfw_uid'=>'cInputName','wfw_pwd'=>'cInputPassword','wfw_mail'=>'cInputMail','wfw_active'=>'cInputBool'),
	//optionnels
	null
);

//
//verifie que l'utilisateur n'existe pas
//
cUser::checkExists($_REQUEST["wfw_uid"],$_REQUEST["wfw_mail"]);
result_check();

//
//cree le dossier client
//
$client_result = cClient::create($_REQUEST,"user",true);
result_check();

//
//cree l'utilisateur
//
cUser::create($_REQUEST["wfw_uid"],$_REQUEST["wfw_mail"],$_REQUEST["wfw_pwd"],$client_result["id"]);
result_check();

//
//définit le compte en attente d'activation ?
//
cUser::activate($_REQUEST["wfw_mail"], cInputBool::toBool($_REQUEST["wfw_active"]));
result_check();

//
rpost("cid",$client_result["id"]);
rpost("uid",$_REQUEST["wfw_uid"]);
rpost_result(ERR_OK);
?>
