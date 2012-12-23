<?php      
/*
	(C)2011 ID-INFORMATIK, WebFrameWork(R)
	Definition des Chemin d'acces globaux

	Remarques:
		Utiliez par defaut des chemins absolues a l'aide de la constante 'ROOT_PATH' qui pointe sur la racine du site
		N'Ajoutez pas de slash en fin de chemin
*/
include(ROOT_PATH.'/req/client/path.inc');

//dossier des liens de sessions publiques
define('USER_SID_DIR','/data/usid');
define('USER_SID_PATH',ROOT_PATH.'/data/usid');

//dossier des liens de selection
define('USER_PATH',ROOT_PATH.'/private/users');

//durée de vie minimal d'une session
define('USER_SID_LIFETIME',60*60);//1H

//statut de la session
define('USER_STATUS_OPEN',"open");//ouvert
define('USER_STATUS_CLOSED',"closed");//ferme
define('USER_STATUS_ACTIVATE',"activate");//en attente d'activation

?>
