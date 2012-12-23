<?php
/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Connect un dossier user dans une session publique

	Arguments:
		[Name]         wfw_uid       : Identificateur de l'utilisateur
		[Password]     wfw_pwd       : Mot de passe
		[Integer]      [lifetime]    : Optionnel, Temps de vie minimal de la session en seconde (0=illimité). Par défaut USER_SID_LIFETIME est utilisé
	Retourne:
		uid        : Identificateur de l'utilisateur
		usid       : Identificateur de la session
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	Erreurs:
		"usid exists"             : L'Utilisateur est déjà connecté
		"cant_link_session" : Le compte utilisateur ne peut pas être connecté
		"add_cron_task"           : La création de la tâche de fermeture de session à échoué ("req_return" retourne le code d'erreur de la requête)
	Revisions:
		[12-01-2012] Implentation
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
	array('wfw_uid'=>'cInputName','wfw_pwd'=>'cInputPassword'),
	//optionnels
	array('lifetime'=>'cInputInteger')
);

//
//globales
//
$uid           = $_REQUEST["wfw_uid"];
$pwd           = $_REQUEST["wfw_pwd"]; 
$usid          = uniqid();  
$link_filename = USER_SID_PATH."/$usid";
$lifetime      = (isset($_REQUEST["lifetime"]) ? intval($_REQUEST["lifetime"]) : USER_SID_LIFETIME);
$remote_ip     = (getenv('HTTP_X_FORWARDED_FOR'))? getenv('HTTP_X_FORWARDED_FOR') : getenv('REMOTE_ADDR'); 
$www_dir       = ROOT_PATH;
$web_name      = basename($www_dir); 

//
// charge le fichier xml
//
$doc = userOpenById($uid,&$client_id);
$link_target   = CLIENT_DATA_PATH."/$client_id";
$task_name     = "user_session_logout_$client_id";
// post user
rpost("uid",$uid);

/*
	verifie le mot de passe
*/ 
clientCheckPassword($doc,$pwd);


//
// le compte est actif ?
//
clientCheckActivation($doc);

//
// enregistre le client
//
$session_id = $doc->getNode("data/wfw_sid",true);
$session_ip = $doc->getNode("data/wfw_session_ip",true);
$session_date = $doc->getNode("data/wfw_session_date",true);
$session_lifetime = $doc->getNode("data/wfw_session_lifetime",true);

//deja connecté ? deconnect pour un changement d'id
if(!empty($session_id->nodeValue) && file_exists(USER_SID_DIR."/".$session_id->nodeValue)){ 
	unlink(USER_SID_DIR."/".$session_id->nodeValue);
}

//initialise les variables
$session_ip->nodeValue = $remote_ip;
$session_date->nodeValue = time();
$session_lifetime->nodeValue = $lifetime;
$session_id->nodeValue = $usid;

//
// assigne la tache de fermeture (si la session est limité en temps)
//
if($lifetime){
	//cron_time = min (de 0 à 59), heure (de 0 à 23), jour du mois (de 1 à 31), numéro du mois (de 1 à 12), jour de la semaine (0 = dimanche, 1 = lundi, ...)
	$cron_time = date("i G j n *",intval($session_date->nodeValue)+intval($session_lifetime->nodeValue));
	$out = array(); 
	$cmd = "$www_dir/wfw/sh/add_cron_task.sh '$web_name' '$task_name' '$cron_time' 'php $www_dir/private/req/user/logout.php wfw_uid=\"$uid\"'";
	exec($cmd,$out,$cmd_ret); 
	if($cmd_ret != 0)
	{
		rpost("req_return", "$cmd_ret");
		rpost_result(ERR_FAILED, "add_cron_task");
	}
}

//
// lie le dossier client
//
if(file_exists($link_target) && !symlink($link_target, $link_filename)){ 
	rpost_result(ERR_FAILED, "cant_link_session");
}

// sauve
clientSave($client_id,$doc);

//
rpost("usid",$usid);
rpost_result(ERR_OK);
?>
