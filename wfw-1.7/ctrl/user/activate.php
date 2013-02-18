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
 * Active un compte utilisateur
 * Rôle : Visiteur
 * UC   : user_activate_account
 */

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

$fields = array(
    "uid"=>"cInputIdentifier",
    "pwd"=>"cInputPassword",
    "mail"=>"cInputMail",
    "token"=>"cInputName"
);

if(!empty($_REQUEST)){
    // exemples JS
    if(!cInputFields::checkArray($fields))
        goto failed;
    
    //crée le compte utilisateur
    if(!UserModule::activateAccount($_REQUEST["uid"],$_REQUEST["pwd"],$_REQUEST["mail"],$_REQUEST["token"]))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
}

goto success;
failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();

success:
;;

?>