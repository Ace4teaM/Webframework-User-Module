<?php
/*

	WebFrameWork(R) - Interface de base utilisateur
	(C)2012 ID-INFORMATIK, Tout droits reserver
	PHP Code
	
	AUTHOR: Auguey Thomas
	MAIL  : contact@id-informatik.com
	PHP   : 5+
	
  Revisions:  
	[21-07-2012] Implentation

*/

interface IUser
{
	public static function create($id,$pwd,$client_id);
	public static function delete($id);
        public static function openById($id,&$client_id);
        public static function openByMail($mail,&$client_id);
        public static function openBySessionId($usid,&$client_id);
        public static function checkActivation($doc);
        public static function activate($mail,$b_active);
        public static function genToken();
}

?>
