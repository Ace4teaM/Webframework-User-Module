<?php
/*

	WebFrameWork(R) - Classe utilisateur du module USER basée sur le module client
	client_user.php
	(C)2012 ID-INFORMATIK, Tout droits reserver
	PHP Code
	
	AUTHOR: Auguey Thomas
	MAIL  : contact@id-informatik.com
	PHP   : 5+
	
	Revisions:
		[05-12-2011] Implentation
*/
require_once 'path.inc';
require_once 'iUser.inc';

class cUser implements iUser
{
	// iUser::create
	public static function create($id,$pwd,$client_id)
	{
		//
		//verifie que l'utilisateur n'existe pas
		//
		/*if(file_exists(USER_PATH."/bymail/".$_REQUEST["wfw_mail"]))
			rpost_result(ERR_FAILED, "user_mail_exist");*/
		if(file_exists(USER_PATH."/byid/$id"))
			rpost_result(ERR_FAILED, "user_exists");

		//
		//assigne l'utilisateur
		//
		$client_filename = CLIENT_DATA_PATH."/$client_id.xml";
	/*	symlink($client_filename, USER_PATH."/bymail/$mail");*/
		symlink($client_filename, USER_PATH."/byid/$id");
		
		//
		//définit l'utilisateur en attente d'activation
		//
	/*	$token = userGenToken();
		file_put_contents(USER_PATH."/unactived/$mail", $token);*/
	}
	
	// iUser::delete
	public static function delete($id)
	{
		$row = $this->call('wfw_user','delete_user',func_get_args());
		return proc_result($row[0],$row[1]);
	}

    public static function activate($mail, $b_active) {
        
    }

    public static function checkActivation($doc) {
        
    }

    public static function genToken() {
        
    }

    public static function openById($id, &$client_id) {
        
    }

    public static function openByMail($mail, &$client_id) {
        
    }

    public static function openBySessionId($usid, &$client_id) {
        
    }
}

?>
