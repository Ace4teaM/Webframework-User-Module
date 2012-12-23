<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Active un compte utilisateur

	Arguments:
		[Mail]         wfw_mail      : Mail de contact
		[Password]     wfw_token     : Clé d'activation
	Retourne:
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
	array('wfw_token'=>'cInputPassword','wfw_mail'=>'cInputMail'),
	//optionnels
	null
);

$mail = $_REQUEST["wfw_mail"];
$token = $_REQUEST["wfw_token"];

//
//verifie que l'utilisateur est inactif
//
if(!file_exists(USER_PATH."/unactived/".$mail))
	rpost_result(ERR_OK);

//
//verifie la clé
//
$req_token = file_get_contents(USER_PATH."/unactived/".$mail);
if($token != $req_token)
	rpost_result(ERR_FAILED,"invalid_token");

//
//active le compte
//
cUser::activate($mail, true);
result_check();

//
rpost_result(ERR_OK);
?>
