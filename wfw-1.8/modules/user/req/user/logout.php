<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Ferme une session depuis son identificateur de session

	Arguments:
		[UNIXFileName] wfw_usid    : Identificateur de la session (usid)

	Retourne:
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
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

//
//verifie les champs obligatoires
//
rcheck(
	//requis
	array('wfw_usid'=>'cInputUNIXFileName'),
	//optionnels
	null
);

//
//globales
//     

$session_path  = USER_SID_PATH."/".$_REQUEST["wfw_usid"];
$client_id     = null;
$client_file   = null;

//
// verifie que la session existe
//
//rpost("session_path",$session_path);
if(!file_exists($session_path) || filetype($session_path)!="link")
	rpost_result(ERR_FAILED,"invalid_session");

//
// obtient le dossier client
//
$client_id     = basename(readlink($session_path));
$doc = clientOpen($client_id);

//
// verifie le type
//
clientCheckType($doc,"user");

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
// initialise les variables
//
$doc->setNodeValue("data/wfw_sid","",true);

//
//supprime le lien symbolique
//
unlink($session_path);

// sauve
clientSave($client_id,$doc);

//
rpost_result(ERR_OK);
?>
