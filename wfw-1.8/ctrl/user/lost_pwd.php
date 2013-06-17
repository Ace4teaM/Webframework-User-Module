<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2013 Thomas AUGUEY <contact@aceteam.org>
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

/**
 * Récupérer un mot-de-passe
 * L'Utilisateur souhaite récupérer son mot-de-passe et son nom d’utilisateur oublié
 * 
 * Rôle : Visiteur
 * UC   : lost_pwd
 */

class user_module_lost_pwd_ctrl extends cApplicationCtrl{
    public $fields      = array('user_mail');
    public $op_fields   = null;
    public $out_fields  = array('user_account_id','message');

    /* Point d'entrée */
    function main(iApplication $app, $app_path, $p) {
        
        //--------------------------------------------
        // 1. Vérifie si le compte utilisateur lié à l’adresse mail existe

        // module mail requis ?
        if(!class_exists("MailModule"))
            return RESULT(cResult::Failed,Application::ModuleClassNotFound,array("module_name"=>"mail"));

        // recupere les informations sur l'utilisateur
        if(!UserModule::getByMail($p->user_mail,$user_account))
            return false;

        //--------------------------------------------
        // 2. Envoie un mail contenant : le nom d'utilisateur (USER_ACCOUNT_ID) et le mot-de-passe (USER_PWD)

        //utile pour générer le message mail
        if(!$app->getDefaultFile($default))
            return $this->activate($app, $app_path, $p);

        $msg = new MailMessage();
        $msg->to       = $p->user_mail;
        $msg->subject  = "Activation";

        //attributs du template
        $template_att = objectToArray($p);
        $template_att["USER_PWD"]        = $user_account->userPwd;
        $template_att["USER_ACCOUNT_ID"] = $user_account->userAccountId;

        //depuis un template ?
        $template = $app->getCfgValue("user_module","pwd_recovery_mail");
        if(!empty($template) && file_exists($template)){
            $msg->msg      = cHTMLTemplate::transformFile($template,$template_att);
            $msg->contentType = mime_content_type($template);
        }
        //depuis le message texte ?
        else{
            $msg->msg      = cHTMLTemplate::transform($default->getResultText("messages","USER_PWD_LOST_MAIL"),$template_att);
            $msg->contentType = "text/plain";
        }

        //envoie le message
        if(!MailModule::sendMessage($msg))
            return false;

        //--------------------------------------------
        // 3. Retourne un message de confirmation à l’utilisateur 
        return RESULT(cResult::Ok, cResult::Success, array("message"=>"USER_PWD_LOST_MAIL_SENT"));
    }
};

?>