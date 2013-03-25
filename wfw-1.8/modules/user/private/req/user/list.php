<?php

/*
	(C)2012 ID-INFORMATIK. WebFrameWork(R)
	Retourne la liste des utilisateurs
  
	Arguments:
		Aucun
    
	Retourne:        
		uid       : Identificateurs, separés par des points virgules ';'
		client_id : Identificateurs client, separés par des points virgules ';'
		result    : résultat de la requête.
		info      : details sur l'erreur en cas d'échec.
	
	Revisions
		[08-12-2011] Update, ROOT_PATH
		[09-01-2012] Update
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
//globales
//        
$file_dir = USER_PATH."/byid/";

$uid = "";
$client_id = "";

//
if(is_dir($file_dir)) {
    if($dh = opendir($file_dir)) {
        while (($file = readdir($dh)) !== false) {
			if(filetype($file_dir.$file)=='link'){  
				$uid .= "$file;";
				//obtient l'identificateur du dossier client
				$client_filename = basename(readlink($file_dir.$file));
				$path_parts = pathinfo($client_filename);
				$client_id .= $path_parts["filename"].";";
            }
        }
        closedir($dh);
    }
}
          
rpost("uid",$uid);
rpost("client_id",$client_id);

//
rpost_result(ERR_OK);
?>
