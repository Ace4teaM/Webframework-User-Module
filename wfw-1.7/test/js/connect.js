/*
(C)2012 ID-Informatik. WebFrameWork(R) All rights reserved.
---------------------------------------------------------------------------------------------------------------------------------------
Warning this script is protected by copyright, if you want to use this code you must ask permission:
Attention ce script est protege part des droits d'auteur, si vous souhaitez utiliser ce code vous devez en demander la permission:
ID-Informatik
MR AUGUEY THOMAS
contact@id-informatik.com
---------------------------------------------------------------------------------------------------------------------------------------

Script lié du document "connect.html"

Revisions:
    [26-09-2012] Implentation
*/

//initialise le contenu
YUI(wfw_yui_config(wfw_yui_base_path)).use('node', 'event', 'wfw-navigator', 'wfw-form', 'wfw-utils', 'wfw-request', function (Y)
{
    var wfw = Y.namespace("wfw");
    
    var baseModulePath = wfw.Navigator.getIndexValue("path","user_mod");
    
    var onLoad = function(e){
        //Initialise le formulaire depuis l'url
        wfw.Form.initFromURI("form", "form");
            
        //+------------------------------------------------
        // Envoie du formulaire
        //+------------------------------------------------
        Y.Node.one("#submitBtn").on("click",function(e,p)
        {
            //envoie la requete
            var param = {
                "onsuccess": function (obj, args) {
                    wfw.Document.confirm(
                        "Le compte '"+arg.uid+"' est créé, un eMail de confirmation vous à été envoyé.\nMerci de consulter votre boite mail et de confirmer l'activation de votre compte utilisateur.",
                        //ok
                        function(){
                            wfw.Navigation.openPage("user:activation","_self",{
                                uid:args.uid
                                });
                        },
                        //annuler
                        null,
                        //options
                        null
                        );
                }
            };

            wfw.Request.Add("connect", baseModulePath+"/req/login.php", wfw.Form.get_fields("form"), wfw.Utils.onCheckRequestResult_XARG, param, false);

            return false;
        });

    };
    Y.one('window').on('load', onLoad);
});
