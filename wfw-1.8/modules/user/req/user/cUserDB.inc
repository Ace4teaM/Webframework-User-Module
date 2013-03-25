<?php
/*

	WebFrameWork(R) - Classe utilisateur du module USER basée sur la base de données PostgreSQL
	pg_user.php
	(C)2012 ID-INFORMATIK, Tout droits reserver
	PHP Code
	
	AUTHOR: Auguey Thomas
	MAIL  : contact@id-informatik.com
	PHP   : 5+
	
	Revisions:
		[05-12-2011] Implentation
*/
require_once('iUser.inc');
require_once(ROOT_PATH.'/req/client/client.inc');

class cUser/* implements iUser*/
{
	// iUser::create
	/*public static function createClient($att)
	{
            //initilise le dossier client
            $client_result = cClient::create($att,"user",true);
            if(!result_check())
                return false;
            return $client_result["id"];
        }*/
        
	// iUser::create
	public static function create($id,$mail,$pwd,$client_id)
	{
            //cree le compte dans la base de données
            global $_userDatabase;
            $row = $_userDatabase->call('wfw_user','create_user',array($id,$pwd,$client_id,$mail));
            proc_result($row[0],$row[1]);
            if(!result_check())
                return false;

            return proc_result(ERR_OK);
	}
	
	// iUser::delete
	public static function delete($id)
	{
                global $_userDatabase;
		$row = $_userDatabase->call('wfw_user','delete_user',func_get_args());
		return proc_result($row[0],$row[1]);
	}
        
	// iUser::getClientById
        public static function openById($id,&$client_id){
            if(!file_exists(USER_PATH."/byid/$id"))
                    return proc_result(ERR_FAILED, "user_not_exist");

            //lit l'identificateur du fichier
            $client_filename = basename(readlink(USER_PATH."/byid/$id"));
            $path_parts      = pathinfo($client_filename);
            $client_id       = $path_parts["filename"];

            //ouvre le fichier XML
            $doc = cClient::openXML($client_id);
            if(!result_check())
                return false;
       
            //verifie le type
            cClient::checkType($doc,"user");
            if(!result_check())
                return false;

            //ok
            proc_result(ERR_OK);
            return $doc;
        }


        public static function openByMail($mail,&$client_id){
                if(!file_exists(USER_PATH."/bymail/".$mail))
                        return proc_result(ERR_FAILED, "user_not_exist");

                //lit l'identificateur du fichier
                $client_filename = basename(readlink(USER_PATH."/bymail/".$mail));
                $path_parts = pathinfo($client_filename);
                $client_id = $path_parts["filename"];

                //ouvre le fichier XML
                $doc = cClient::openXML($client_id);
                if(!result_check())
                    return false;

                //verifie le type
                cClient::checkType($doc,"user");
                if(!result_check())
                    return false;

                //ok
                proc_result(ERR_OK);
                return $doc;
        }

        public static function openBySessionId($usid,&$client_id){
                if(!file_exists(USER_SID_PATH."/$usid"))
                        return proc_result(ERR_FAILED, "session_not_exist");

                //lit l'identificateur du fichier
                $client_filename = basename(readlink(USER_SID_PATH."/$usid"));
                $path_parts = pathinfo($client_filename);
                $client_id = $path_parts["filename"];

                //ouvre le fichier XML
                $doc = cClient::openXML($client_id);
                if(!result_check())
                    return false;

                //verifie le type
                cClient::checkType($doc,"user");
                if(!result_check())
                    return false;

                //ok
                proc_result(ERR_OK);
                return $doc;
        }

        public static function checkActivation($doc){
                $mail = $doc->getNodeValue("data/wfw_mail",false);
                if(empty($mail))
                        return proc_result(ERR_FAILED,"invalid_user");

                if(file_exists(USER_PATH."/unactived/".$mail))
                        return proc_result(ERR_FAILED,"inactive_user");
                
                //ok
                return proc_result(ERR_OK);
        }

        public static function checkExists($id,$mail){
                if(file_exists(USER_PATH."/bymail/".$mail))
                        return proc_result(ERR_FAILED, "user_mail_exist");
                if(file_exists(USER_PATH."/byid/".$id))
                        return proc_result(ERR_FAILED, "user_id_exist");
                
                //ok
                return proc_result(ERR_OK);
        }

        public static function activate($mail,$b_active){
            if(!$b_active){
                $token = cUser::genToken();
                if(FALSE === file_put_contents(USER_PATH.'/unactived/'.$mail, $token))
                      return proc_result(ERR_SYSTEM,'create_file'); 
                
                proc_result(ERR_OK); 
                return $token;
            }
            
            //active le compte
            if(file_exists(USER_PATH."/unactived/".$mail))
                unlink(USER_PATH."/unactived/".$mail);
            
            //ok
            return proc_result(ERR_OK);
        }

        public static function genToken(){
                return rand(100,900).'-'.rand(1000,9000).'-'.rand(10000,90000);
        }
}

?>
