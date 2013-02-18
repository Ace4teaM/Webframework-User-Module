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
 * Renseigne l'identité d'un utilisateur
 * Rôle : Utilisateur
 * UC   : user_identity
 */

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

$fields = array(
    "user_id"=>"cInputInteger",
    "last_name"=>"cInputName",
    "first_name"=>"cInputName",
    "birth_day"=>"",
    "sex"=>""
);

if(!empty($_REQUEST))
{
    // exemples JS
    if(!cInputFields::checkArray($fields))
        goto failed;

    //verifie les informations de connexion
    //... $_REQUEST["user_id"]
 
    //obtient le compte utilisateur
    if(!UserModule::makeIdentity($_REQUEST["user_id"], $_REQUEST["first_name"], $_REQUEST["last_name"], $_REQUEST["birth_day"], $_REQUEST["sex"]))
            goto failed;

    //retourne le resultat de cette fonction
    $result = cResult::getLast();
      
    //obtient/initialise l'identité
 /*   $identity = NULL;
    if(!UserIdentityMgr::getByRelation($identity,$account)){
        echo("add");
        //crée une nouvelle entree
        $identity = new UserIdentity();
        $identity->userIdentityId = NULL;
        if(!UserIdentityMgr::create($identity))
            $app->processLastError();
        //met a jour le compte
        if(!UserAccountMgr::update($account))
            $app->processLastError();
    }
    print_r($identity);
    
    //met a jour l'identité
    $identity->firstName = $_REQUEST["first_name"];
    $identity->lastName  = $_REQUEST["last_name"];
    $identity->birthDay  = $_REQUEST["birth_day"];
    $identity->sex       = $_REQUEST["sex"];
    if(!UserIdentityMgr::update($identity))
        $app->processLastError();*/
}

goto success;
failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();


success:
;;

?>