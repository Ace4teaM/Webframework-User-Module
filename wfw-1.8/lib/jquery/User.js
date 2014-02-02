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
 * Gestionnaire d'utilisateur
 * Librairie Javascript
 *
 * WFW Dependences: base.js
 * JQUERY Dependences: jquery.cookie.js
*/
(function ($) {
    var uid         = null; // user id
    var cid         = null; // connexion id
    var pwd         = null; // mot-de-passe (si connexion automatique)
    var expireTimeoutId = null; // id setTimeout fonction
    var status      = null; //statut de connexion

    //-------------------------------------------------------------
    // public events
    //-------------------------------------------------------------

    /**
     *   @brief Callback appelé lors d'un changement d'état de la connexion
     *   @param string status Un des codes de résultat retourné par le cas d'utilisation 'user_check_connection'
    */
    var onConnectionStatusChange = function(status){};

    //-------------------------------------------------------------
    // private events
    //-------------------------------------------------------------

    /**
     *   @brief Callback appelé lors de l'expiration (estimé) de la connexion
     *   @param string status Un des codes de résultat retourné par le cas d'utilisation 'user_check_connection'
    */
    var onConnectionExpireDate = function(status){
        expireTimeoutId = null;
        $.user.checkConnection();
    };

    //-------------------------------------------------------------
    // methodes
    //-------------------------------------------------------------
    $.user = {
        /**
         *   @brief Initialise le module
        */
        init: function() {
            uid = $.cookie("user_account_id");
            cid = $.cookie("user_connection_id");
            pwd = $.cookie("user_pwd");
            console.log("$.user.init: user_account_id="+uid+" user_connexion_id="+cid);
        },

        /**
         *   @brief Initialise le module
        */
        setConnectionStatusChange: function(callback) {
            onConnectionStatusChange = callback;
        },
        
        /**
         *   @brief Retourne l'identifiant de connexion
        */
        getConnectionId: function() {
            return cid;
        },

        /**
         *   @brief Initialise le module
        */
        isConnected: function() {
            console.log("status="+status);
            return (status == "USER_CONNECTED") ? true : false;
        },

        /**
         *   @brief Vérifie et maintient l'état de la connexion
        */
        checkConnection: function() {
            uid = $.cookie("user_account_id");
            cid = $.cookie("user_connection_id");
            pwd = $.cookie("user_pwd");

            var checkReq = null; // 
            var me = this; // 

            //Test la session active
            if (cid != null) {
                console.log("$.user.checkConnection: Checking connection");

                $(window).request("xarg","user_check",{user_connection_id: cid},
                    {
                        onsuccess: function (obj, args) {
                            console.log("$.user.checkConnection: onsuccess");

                            onConnectionStatusChange(args.error);

                            //enregistre l'etat
                            status = args.error;
                            console.log("status="+status);
                            //si l'utilisateur est connecté prépare l'événement d'expiration
                            if(args.error == "USER_CONNECTED")
                            {
                                //calcule le delais pour la date d'expiration
                                var expire = parseInt(args.expire);
                                var expireDate = new Date(expire*1000);
                                var delay = (expire*1000)-(new Date().getTime());
//                                    console.log("expire="+expireDate);

                                //delais ok?
                                if(!isNaN(expire) && delay>0)
                                {
                                    //supprime le timer en cours si besoin
                                    if(expireTimeoutId != null){
                                        clearTimeout(expireTimeoutId);
                                        expireTimeoutId=null;
                                    }
                                    //cree le nouveau timer de l'evenement
                                    console.log("wfw.User.checkConnection: Add expire callback to "+expireDate);
//                                        wfw.puts("delay="+delay);
                                    expireTimeoutId = setTimeout(me.checkConnection,delay);
                                }
                                else
                                    console.log("$.user.checkConnection: Use unlimited session life time");
                            }
                        },
                        onfailed: function (obj, args) {
                            //enregistre l'etat
                            status = args.error;
                            console.log("$.user.checkConnection: ");
                            console.log(args);
                            //wfw.puts("$.user.checkConnection: "+args.result+" = "+args.error);
                            onConnectionStatusChange(args.error);
                            cid = null; // pas la peine d'essayer de nouveau
                            $.removeCookie("user_connection_id"); // supprime le cookie obselete
                        },
                        continue_if_failed: false,
                        no_msg: true,
                        no_output: true
                    }
                );
            }
            else{
                console.log("$.user.checkConnection: No current connection");
                onConnectionStatusChange("USER_DISCONNECTED");
            }

        },

        /**
         *   @brief Enregistre les informations de connexion
         *   @param user_name      Nom de l'utilisateur
         *   @param connection_id  Token de connection
        */
        regSession: function(user_name, connection_id) {
            this.uid = $.cookie("user_account_id",user_name);
            this.cid = $.cookie("user_connection_id",connection_id);
            console.log("uid="+uid);
            console.log("cid="+cid);
        }
    };
    
    /*-----------------------------------------------------------------------------------------------------------------------
     * Initialise
     -----------------------------------------------------------------------------------------------------------------------*/
    $.user.init();
})(jQuery);
