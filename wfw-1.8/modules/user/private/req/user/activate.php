<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Active/Desactive un compte utilisateur

	Arguments:
		[Mail]     wfw_mail      : Mail de contact
		[Bool]     wfw_active    : Active?
	Retourne:
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
	array('wfw_active'=>'cInputBool','wfw_mail'=>'cInputMail'),
	//optionnels
	null
);

$mail = $_REQUEST["wfw_mail"];
$active = cInputBool::toBool($_REQUEST["wfw_active"]);

//
//test le compte
//
$doc = userOpenByMail($mail,&$client_id);

//
//globales
//
if($active)
{
	if(file_exists(USER_PATH."/unactived/$mail"))
		unlink(USER_PATH."/unactived/$mail");
}
else{
	$token = userGenToken();
	file_put_contents(USER_PATH."/unactived/$mail", $token);
}

//
rpost_result(ERR_OK);
?>
