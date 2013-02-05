/*
    (C)2012 AceTeaM, WebFrameWork(R). All rights reserved.
    ---------------------------------------------------------------------------------------------------------------------------------------
    Warning this script is protected by copyright, if you want to use this code you must ask permission:
    Attention ce script est protege part des droits d'auteur, si vous souhaitez utiliser ce code vous devez en demander la permission:
        MR AUGUEY THOMAS
        dev@aceteam.org
    ---------------------------------------------------------------------------------------------------------------------------------------

    [16-10-2012] Formulaires HTML

    JS  Dependences: base.js
    YUI Dependences: base, wfw, wfw-request, wfw-uri, wfw-navigator, wfw-style, wfw-xarg, wfw-document

    Revisions:
        [01-01-2013] Implementation
*/

YUI.add('wfw-user', function (Y) {
    var wfw = Y.namespace('wfw');
    
    wfw.User = {
        uid         : null, // user id
        cid         : null, // connexion id
        pwd         : null, // mot-de-passe (si connexion automatique)
        expireTimeoutId : null, // id setTimeout fonction
        
        //public events
        onConnectionStatusChange: function(status){},
        
        //private event
        onConnectionExpireDate: function(status){
            wfw.User.expireTimeoutId = null;
            wfw.User.checkConnection();
        },
        
        /**
         *   @brief Initialise le module
        */
        init: function() {
            wfw.puts("User: init");
            this.uid = Y.Cookie.get("wfw_user_uid");
            this.cid = Y.Cookie.get("wfw_user_cid");
            this.pwd = Y.Cookie.get("wfw_user_pwd");
        },

        /**
         *   @brief Initialise le module
        */
        checkConnection: function() {
            this.uid = Y.Cookie.get("wfw_user_uid");
            this.cid = Y.Cookie.get("wfw_user_cid");
            this.pwd = Y.Cookie.get("wfw_user_pwd");

            var checkReq = null; // 
            
            //Test la session active
            if (this.cid != null) {
                wfw.puts("wfw.User.checkConnection: Checking connection");
                
                checkReq = new wfw.Request.REQUEST(
                    {
                        name: "Maintient la connexion active",
                        url: wfw.Navigator.getURI("check"),
                        args: {
                            cid: this.cid,
                            output: "xarg"
                        },
                        callback : wfw.XArg.onCheckRequestResult_XARG,
                        user : {
                            onsuccess: function (obj, args) {
                                //wfw.puts(args.error);
                                wfw.puts("wfw.User.checkConnection: onsuccess");

                                wfw.User.onConnectionStatusChange(args.error);
                                //si l'utilisateur est connecté prépare l'événement d'expiration
                                if(args.error == "USER_CONNECTED")
                                {
                                    //calcule le delais pour la date d'expiration
                                    var expire = parseInt(args.expire);
                                    var expireDate = new Date(expire*1000);
                                    var delay = (expire*1000)-new Date().getTime();
                                    //wfw.puts("expire="+expireDate);

                                    //delais ok?
                                    if(!isNaN(expire) && delay>0)
                                    {
                                        //supprime le timer en cours si besoin
                                        if(wfw.User.expireTimeoutId != null){
                                            clearTimeout(wfw.User.expireTimeoutId);
                                            wfw.User.expireTimeoutId=null;
                                        }
                                        //cree le nouveau timer de l'evenement
                                        wfw.puts("wfw.User.checkConnection: Add expire callback to "+expireDate);
                                        wfw.User.expireTimeoutId = setTimeout(wfw.User.checkConnection,delay);
                                    }
                                    else
                                        wfw.puts("wfw.User.checkConnection: Use unlimited session life time");
                                }
                            },
                            onfailed: function (obj, args) {
                                wfw.puts("wfw.User.checkConnection: onfailed = "+args.error);
                                wfw.User.onConnectionStatusChange(args.error);
                                wfw.User.cid = null; // pas la peine d'essayer de nouveau
                            },
                            continue_if_failed: false,
                            no_msg: true,
                            no_output: true
                        }
                    }
                );
                
                wfw.Request.Insert(checkReq);
            }
            else{
                wfw.puts("User: No current connection");
                wfw.User.onConnectionStatusChange("USER_DISCONNECTED");
            }

        },

        /**
         *   @brief Enregistre les informations de connexion
         *   @param string  user_name      Nom de l'utilisateur
         *   @param string  connection_id  Token de connection
         *   @return bool Succès de la fonction
        */
        regSession: function(user_name, connection_id) {
            this.uid = Y.Cookie.set("wfw_user_uid",user_name);
            this.cid = Y.Cookie.set("wfw_user_cid",connection_id);
            wfw.puts("uid="+this.uid);
            wfw.puts("cid="+this.cid);
        }
    }
    
    /*-----------------------------------------------------------------------------------------------------------------------
     * Initialise
     -----------------------------------------------------------------------------------------------------------------------*/
    wfw.User.init();
    
}, '1.0', {
    requires:['base', 'cookie', 'wfw','wfw-request','wfw-xml','wfw-uri','wfw-navigator','wfw-style','wfw-xarg']
});










/*
(C)2012 ID-Informatik. WebFrameWork(R) All rights reserved.
---------------------------------------------------------------------------------------------------------------------------------------
Warning this script is protected by copyright, if you want to use this code you must ask permission:
Attention ce script est protege part des droits d'auteur, si vous souhaitez utiliser ce code vous devez en demander la permission:
ID-Informatik
MR AUGUEY THOMAS
contact@id-informatik.com
---------------------------------------------------------------------------------------------------------------------------------------

Module Utilisateur

Dependences: base.js, dom.js, wfw.js, wfw-extends.js

Revisions:
    [01-02-2012] Implentation
    [29-02-2012] Update
*/
