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

/**
 * Formulaire d'inscription
 * Rôle : Visiteur
 * UC   : user_register_account
 */

class Ctrl extends cApplicationCtrl{
    public $fields    = array('user_account_id', 'user_mail');
    public $op_fields = null;

    //Active le compte en cas d'impossibilité d'envoyer un mail
    function activate(iApplication $app, $app_path, $p) {
        $result = cResult::getLast();

        $pwd   = rand(1615,655641);
        
        //crée le compte utilisateur'inscription
        if(!UserModule::activateAccount($p->user_account_id, $pwd, $p->user_mail, $result->getAtt("token")))
            return false;
        
        //ajoute un message d'avertissement
        $result->addAtt("pwd",$pwd);
        $result->addAtt("message","USER_MSG_AUTO_ACTIVATE");
        
        return RESULT_INST($result);
    }
    
    function main(iApplication $app, $app_path, $p) {

        //module mail requis ?
        if(!class_exists("MailModule") && $app->getCfgValue("user_module","requires_mail_module"))
            return RESULT(cResult::Failed,Application::ModuleClassNotFound,array("module_name"=>"mail"));

        $client_id = "none";

        //crée l'inscription
        if(!UserModule::registerAccount($p->user_account_id, $p->user_mail))
            return false;

        //retourne le resultat de cette fonction
        $result = cResult::getLast();

        //pas de module Mail ?
        if(!class_exists("MailModule"))
            return $this->activate($app, $app_path, $p);

        //utile a la generation du message
        if(!$app->getDefaultFile($default))
            return $this->activate($app, $app_path, $p);

        //--------------------------------------------
        //initialise le message

        $msg = new MailMessage();
        $msg->to       = $p->user_mail;
        $msg->subject  = "Activation";

        //attributs du template
        $template_att = objectToArray($p);
        $template_att["TOKEN"] = $result->getAtt("token");
        $template_att["ACTIVATION_LINK"] = $app->getBaseURI()."/".$default->getIndexValue("page","user_activate")."&token=".$result->getAtt("token")."&user_account_id=$p->user_account_id&user_mail=$p->user_mail";

        //depuis un template ?
        $template = $app->getCfgValue("user_module","activation_mail");
        if(!empty($template) && file_exists($template)){
            $msg->msg      = cHTMLTemplate::transformFile($template,$template_att);
            $msg->contentType = mime_content_type($template);
        }
        //depuis le message standard ?
        else{
            $msg->msg      = cHTMLTemplate::transform($default->getResultText("messages","USER_ACTIVATION_MAIL"),$template_att);
            $msg->contentType = "text/plain";
        }

        //envoie le message
        if(!MailModule::sendMessage($msg))
            return false;

        //ajoute un message d'avertissement
        $result->addAtt("message","USER_MSG_ACTIVATE_BY_MAIL");
        return RESULT_INST($result);
    }
};

?>