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
 * Déconnecte l'utilisateur en cours
 * Rôle : Utilisateur
 * UC   : user_disconnect
 */

//résultat de la requete
RESULT(cResult::Ok,cApplication::Information,array("message"=>"WFW_MSG_POPULATE_FORM"));
$result = cResult::getLast();

//requis
if(!$app->makeFiledList(
        $fields,
        array( 'user_connection_id' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

if(!empty($_REQUEST))
{
    // vérifie la validitée des champs
    $p = array();
    if(!cInputFields::checkArray($fields,NULL,$_COOKIE,$p))
        goto failed;

    //supprime le compte utilisateur
    if(!UserModule::disconnect($p->user_connection_id))
        goto failed;
    
    //retourne le resultat de cette fonction
    $result = cResult::getLast();
    
    //supprime le cookie
    setcookie("user_connection_id",NULL,time()-1);
}

goto success;
failed:
// redefinit le resultat avec l'erreur en cours
$result = cResult::getLast();

success:

;;

?>