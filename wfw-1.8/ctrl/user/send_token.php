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
 * UC   : activate_account
 */

class user_module_send_token_ctrl extends cApplicationCtrl{
    public $fields    = array('user_mail');
    public $op_fields = null;

    function main(iApplication $app, $app_path, $p) {
        
        //--------------------------------------------
        // obtient l'inscription
        
        $reg = new UserRegistration();
        if(!UserModule::getRegisterByMail($reg,$p->user_mail))
            return false;
        
        if(empty($reg->userId))
            return RESULT(cResult::Failed,"USER_REGISTRATION_NOT_EXISTS");

        //--------------------------------------------
        //initialise le message

        //pas de module Mail ?
        if(!class_exists("MailModule"))
            return false;

        //utile pour générer le message mail
        if(!$app->getDefaultFile($default))
            return false;

        $msg = new MailMessage();
        $msg->to       = $reg->userMail;
        $msg->subject  = "Activation";

        //attributs du template
        $template_att = array();
        $template_att["USER_ID"] = $reg->userId;
        $template_att["USER_MAIL"] = $reg->userMail;
        $template_att["USER_TOKEN"] = $reg->userToken;
        $template_att["ACTIVATION_LINK"] = $app->getBaseURI()."/".$default->getIndexValue("page","user_activate")."&token=".$reg->userToken."&user_account_id=$reg->userId&user_mail=$reg->userMail";

        //depuis un template ?
        $template = $app->getCfgValue("user_module","send_token_mail");
        if(!empty($template) && file_exists($template)){
            $msg->msg      = cHTMLTemplate::transformFile($template,$template_att);
            $msg->contentType = mime_content_type($template);
        }
        //depuis le message standard ?
        else{
            $msg->msg      = cHTMLTemplate::transform($default->getResultText("messages","USER_TOKEN_MAIL"),$template_att);
            $msg->contentType = "text/plain";
        }

        //envoie le message
        if(!MailModule::sendMessage($msg))
            return false;

        //ajoute un message d'avertissement
        return RESULT(cResult::Ok,cResult::Success,array("message"=>"USER_MSG_TOKEN_BY_MAIL"));
    }
};
?>