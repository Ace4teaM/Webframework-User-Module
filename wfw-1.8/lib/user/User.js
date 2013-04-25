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
 * Gestionnaire d'utilisateur
 * Librairie Javascript
 *
 * WFW Dependences: base.js
 * YUI Dependences: base, wfw, wfw-request, wfw-uri, wfw-navigator, wfw-style, wfw-xarg, wfw-document
*/

YUI.add('wfw-user', function (Y) {
    var wfw = Y.namespace('wfw');
    
    wfw.User = {
        uid         : null, // user id
        cid         : null, // connexion id
        pwd         : null, // mot-de-passe (si connexion automatique)
        expireTimeoutId : null, // id setTimeout fonction
        status      : null, //statut de connexion
        //
        //-------------------------------------------------------------
        // public events
        //-------------------------------------------------------------
        
        /**
         *   @brief Callback appelé lors d'un changement d'état de la connexion
         *   @param string status Un des codes de résultat retourné par le cas d'utilisation 'user_check_connection'
        */
        onConnectionStatusChange: function(status){},
        
        //-------------------------------------------------------------
        // private events
        //-------------------------------------------------------------
        
        /**
         *   @brief Callback appelé lors de l'expiration (estimé) de la connexion
         *   @param string status Un des codes de résultat retourné par le cas d'utilisation 'user_check_connection'
        */
        onConnectionExpireDate: function(status){
            wfw.User.expireTimeoutId = null;
            wfw.User.checkConnection();
        },
        
        //-------------------------------------------------------------
        // methodes
        //-------------------------------------------------------------
        
        /**
         *   @brief Initialise le module
        */
        init: function() {
            wfw.puts("User: init");
            this.uid = Y.Cookie.get("user_account_id");
            this.cid = Y.Cookie.get("user_connection_id");
            this.pwd = Y.Cookie.get("user_pwd");
        },

        /**
         *   @brief Initialise le module
        */
        isConnected: function() {
            wfw.puts("status="+wfw.User.status);
            return (wfw.User.status == "USER_CONNECTED") ? true : false;
        },

        /**
         *   @brief Vérifie et maintient l'état de la connexion
        */
        checkConnection: function() {
            this.uid = Y.Cookie.get("user_account_id");
            this.cid = Y.Cookie.get("user_connection_id");
            this.pwd = Y.Cookie.get("user_pwd");

            var checkReq = null; // 
            
            //Test la session active
            if (this.cid != null) {
                wfw.puts("wfw.User.checkConnection: Checking connection");
                
                checkReq = new wfw.Request.REQUEST(
                    {
                        name: "Maintient la connexion active",
                        url: wfw.Navigator.getURI("user_check"),
                        args: {
                            user_connection_id: this.cid,
                            output: "xarg"
                        },
                        callback : wfw.XArg.onCheckRequestResult_XARG,
                        user : {
                            onsuccess: function (obj, args) {
                                //wfw.puts(args.error);
                                wfw.puts("wfw.User.checkConnection: onsuccess");

                                wfw.User.onConnectionStatusChange(args.error);
                    
                                //enregistre l'etat
                                wfw.User.status = args.error;
                                wfw.puts("status="+wfw.User.status);
                                //si l'utilisateur est connecté prépare l'événement d'expiration
                                if(args.error == "USER_CONNECTED")
                                {
                                    //calcule le delais pour la date d'expiration
                                    var expire = parseInt(args.expire);
                                    var expireDate = new Date(expire*1000);
                                    var delay = (expire*1000)-(new Date().getTime());
//                                    wfw.puts("expire="+expireDate);

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
//                                        wfw.puts("delay="+delay);
                                        wfw.User.expireTimeoutId = setTimeout(wfw.User.checkConnection,delay);
                                    }
                                    else
                                        wfw.puts("wfw.User.checkConnection: Use unlimited session life time");
                                }
                            },
                            onfailed: function (obj, args) {
                                //enregistre l'etat
                                wfw.User.status = args.error;
                                wfw.puts("wfw.User.checkConnection: "+wfw.Result.fromXArg(args).toString());
                                //wfw.puts("wfw.User.checkConnection: "+args.result+" = "+args.error);
                                wfw.User.onConnectionStatusChange(args.error);
                                wfw.User.cid = null; // pas la peine d'essayer de nouveau
                                Y.Cookie.remove("user_connection_id"); // supprime le cookie obselete
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
        */
        regSession: function(user_name, connection_id) {
            this.uid = Y.Cookie.set("user_account_id",user_name);
            this.cid = Y.Cookie.set("user_connection_id",connection_id);
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
