<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2012-2013 Thomas AUGUEY <contact@aceteam.org>
    ---------------------------------------------------------------------------------------------------------------------------------------
    This file is part of WebFrameWork.

    WebFrameWork is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebFrameWork is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebFrameWork.  If not, see <http://www.gnu.org/licenses/>.
    ---------------------------------------------------------------------------------------------------------------------------------------
*/

/*
 * Connexion d'un utilisateur
 * Rôle : Visiteur
 * UC   : user_connect
 */

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

//requis
if(!$app->makeFiledList(
        $fields,
        array( 'user_account_id', 'user_pwd', 'life_time' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

if(!empty($_REQUEST))
{
    // vérifie la validitée des champs
    $p = array();
    if(!cInputFields::checkArray($fields,NULL,$_REQUEST,$p))
        goto failed;
    
    $client_ip  = $_SERVER["REMOTE_ADDR"];
    $local_path = NULL;

    if(!UserModule::checkAuthentication($p->user_account_id, $p->user_pwd))
        goto failed;
    
    //crée une connexion
    if(!UserModule::connectUser($p->user_account_id, $client_ip, $local_path, $p->life_time))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //définit le cookie
    setcookie("user_connection_id",$result->getAtt("CONNECTION_ID"));
    
    //initialise la tache de fermeture
    $taskMgr=NULL;
    $app->getTaskMgr($taskMgr);
    if($taskMgr !== null && $p->life_time > 0){
        $taskName = UserModule::disconnectTaskName($p->user_account_id);
        $taskCmd  = UserModule::disconnectTaskCmd($p->user_account_id);
        $expire   = new DateTime();
        $expire->add(new DateInterval('P0Y0DT0H'.$p->life_time.'M'));
        if(!$taskMgr->create($taskName,$expire,$taskCmd))
             goto failed;
    }
}


goto success;
failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:
;;

?>