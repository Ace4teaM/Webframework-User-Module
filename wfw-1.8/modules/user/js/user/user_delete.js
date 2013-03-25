/*
(C)2012 ID-Informatik. WebFrameWork(R) All rights reserved.
---------------------------------------------------------------------------------------------------------------------------------------
Warning this script is protected by copyright, if you want to use this code you must ask permission:
Attention ce script est protege part des droits d'auteur, si vous souhaitez utiliser ce code vous devez en demander la permission:
ID-Informatik
MR AUGUEY THOMAS
contact@id-informatik.com
---------------------------------------------------------------------------------------------------------------------------------------

Script lié du document "user_delete.html"

Revisions:
    [26-09-2012] Implentation
*/


//initialise le contenu
function onInit()
{
    //Initialise le formulaire depuis l'url
    wfw.form.initFromURI("form", "form");
}

//envoie du formulaire
function onSubmit()
{
    return false;
}

// intialise les extensions
wfw.ext.initAll();

// intialise les evenements
wfw.event.SetCallback("wfw_window","load","onInit",onInit,false);

// assigne les evenements
wfw.event.ApplyTo(window, "wfw_window");
