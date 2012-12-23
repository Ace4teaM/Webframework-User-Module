<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Déconnecte toutes les sessions en cours

	Retourne:
		usid       : Identificateur de la session
		result     : Résultat de la requête
		info       : Détails sur l'erreur en cas d'echec
	
	Revisions:
		[20-01-2012] Implentation
		[23-01-2012] Update
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

//libere les sessions
if(is_dir(USER_SID_PATH)) {
	if($dh = opendir(USER_SID_PATH)) {
		while (($file = readdir($dh)) !== false) {
			$filepath = USER_SID_PATH."/$file";
//			echo("filepath: $filepath; ".filetype($filepath)."\n");
			if(filetype($filepath)=='link'){  
				//$client_id = basename(readlink($filepath));
				//$client_file = CLIENT_DATA_PATH."/$client_id.xml";
//				echo("client_id: $client_id\n");
//				echo("client_file: $client_file\n");

				//ferme la session si possible
				//if(file_exists($client_file)){
					//cree un dossier client
					$args = array();
					$args["wfw_usid"] = $file;
					$result = xarg_req(ROOT_PATH.'/private/req/user/','logout',$args);
				//}
			}
		}
		closedir($dh);
	}
}

//
// supprime tout les liens de sessions
//
rrmdir(USER_SID_PATH,false);

//
rpost_result(ERR_OK);
?>
