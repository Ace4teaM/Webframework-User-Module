<?php      
/*
	(C)2011 ID-INFORMATIK, WebFrameWork(R)
	Definition des Chemin d'acces globaux

	Remarques:
		Utiliez par défaut des chemins absolues a l'aide de la constante 'ROOT_PATH' qui pointe sur la racine du site
		N'Ajoutez pas de slash en fin de chemin
*/
require_once(ROOT_PATH.'/req/client/client.inc');
require_once('cUserDB.inc');
global $_userDatabase;

// ouvre la connexion avec la base de données Utilisateur
if(!isset($_userDatabase)){
    //obtient les informations de connexion
    $default_doc = new cXMLDefault();
    $default_doc->Initialise(ROOT_PATH.'/private/default.xml');
    result_check();

    $db_name   = nvl($default_doc->getIndexValue('database',"name"),"postgres");
    $db_user   = nvl($default_doc->getIndexValue('database',"user"),"postgres");
    $db_pwd    = nvl($default_doc->getIndexValue('database',"pwd"),"admin");
    $db_server = nvl($default_doc->getIndexValue('database',"server"),"localhost");
    $db_port   = nvl($default_doc->getIndexValue('database',"port"),"5432");

    //connexion à la base de données
    $_userDatabase = new cPostgreSQLConnection();
    $_userDatabase->connect($db_user,$db_name,$db_pwd,$db_server,$db_port);
    result_check();
}

//erreurs strings
global $_req_err_str;
$_req_err_str = array_merge($_req_err_str,array(
	"add_cron_task"=>"Impossible d'ajouter la tâche planifiée",
	"inactive_user"=> "Votre compte utilisateur n'est pas encore actif",
	"user_not_exist"=> "Le compte utilisateur n'existe pas",
	"invalid_user"=> "Le compte utilisateur n'est pas valide",
	"cant_link_session"=> "La session ne peut pas être ouverte",
	"session_not_exist"=> "La session n'existe pas",
        "cant_link_user"=>"L'Utilisateur ne peut pas être lié au système de fichier"
	));



?>