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


/*
-----------------------------------------------------------------------------------------------
Extended object
event:
    wfw.ext.module.user:login   // l'utilisateur se connecte
    wfw.ext.module.user:logout  // l'utilisateur se déconnecte
    wfw.ext.module.user:check   // test la connection de l'utilisateur
-----------------------------------------------------------------------------------------------
*/
wfw.ext.module.user = {
    uid: null,
    sid: null,
    pwd: null,
    client_id: null,
    session_status: "close",
    /*
    init
    */
    init: function () {
        wfw.event.SetCallback("wfw_window", "load", "onInitProductPage",
            function (e) {
                wfw.ext.module.user.uid = docGetCookie(document, "wfw_user_uid");
                wfw.ext.module.user.sid = docGetCookie(document, "wfw_user_sid");
                wfw.ext.module.user.pwd = docGetCookie(document, "wfw_user_pwd");
                //var auto_log = cInputBool.toBool(docGetCookie(document, "wfw_user_auto_log"));

                var request_list = [];

                if (wfw.ext.module.user.uid) {

                    //Test la session active
                    if (wfw.ext.module.user.sid || wfw.ext.module.user.pwd) {
                        request_list.push(
                            {
                                name: "Test la session active",
                                url: "req/user/check.php",
                                args: { wfw_uid: wfw.ext.module.user.uid, wfw_usid: wfw.ext.module.user.sid, wfw_pwd: wfw.ext.module.user.pwd },
                                onsuccess: function (obj, args) {
                                    wfw.ext.module.user.session_status = args.session_status;
                                    if(typeof(args["cid"])=="string")
                                        wfw.ext.module.user.client_id = args.cid;

                                    wfw.event.callEvent(wfw.ext.module.user, "wfw.ext.module.user", "check");
                                },
                                continue_if_failed: false,
                                no_msg: true
                            }
                        );
                    }
                    else
                        wfw.event.callEvent(wfw.ext.module.user, "wfw.ext.module.user", "check");

                    //Ouvre la session ?
                    if (wfw.ext.module.user.pwd) {
                        request_list.push(
                            {
                                name: "Connection automatique de l'utilisateur",
                                url: "req/user/login.php",
                                args: { wfw_uid: wfw.ext.module.user.uid, wfw_pwd: wfw.ext.module.user.pwd },
                                onsuccess: function (obj, args) {
                                    docSetCookie(document, "wfw_user_sid", args.usid);

                                    wfw.ext.module.user.sid = args.usid;
                                    wfw.ext.module.user.session_status = "open";

                                    wfw.event.callEvent(wfw.ext.module.user, "wfw.ext.module.user", "login");
                                },
                                check_execution: function (list) {
                                    if (wfw.ext.module.user.session_status == "close")
                                        return true;
                                    return false;
                                },
                                continue_if_failed: false,
                                no_msg: true
                            }
                        );
                    }
                }

                wfw.ext.utils.callRequestListXARG(request_list, null);
            },
            false// apres l'initialisation des modules
        );
    },
    /*
    Verifie si la session est ouverte
    */
    isConnected: function () {
        if (this.session_status == "open")
            return true;
        return false;
    },
    /*
    Ouvre une session
    */
    login: function (uid, pwd, auto, param) {

        auto = cInputBool.toBool(auto);

        var request_list = [];

        request_list.push(
            object_merge(
            {
                name: "Ouvre la session utilisateur",
                url: "req/user/login.php",
                args: { wfw_uid: uid, wfw_pwd: pwd },
                onsuccess: function (obj, args) {
                    docSetCookie(document, "wfw_user_uid", uid, "360");
                    docSetCookie(document, "wfw_user_sid", args.usid, ((auto == true) ? "360" : null));
                    docSetCookie(document, "wfw_user_pwd", pwd, ((auto == true) ? "360" : null));

                    wfw.ext.module.user.uid = uid;
                    wfw.ext.module.user.sid = args.usid;
                    wfw.ext.module.user.pwd = pwd;
                    wfw.ext.module.user.session_status = "open";

                    wfw.event.callEvent(wfw.ext.module.user, "wfw.ext.module.user", "login");
                },
                continue_if_failed: false
            }, param)
        );

        wfw.ext.utils.callRequestListXARG(request_list, null);
        return false;
    },
    /*
    Ferme la session active
    */
    logout: function (param) {
        var request_list = [];

        request_list.push(
            object_merge(
            {
                name: "Ferme la session utilisateur",
                url: "req/user/logout.php",
                args: { wfw_usid: wfw.ext.module.user.sid },
                onsuccess: function (obj, args) {
                    docDelCookie(document, "wfw_user_sid");
                    docDelCookie(document, "wfw_user_pwd");
                    wfw.ext.module.user.sid = null;
                    wfw.ext.module.user.pwd = null;
                    wfw.ext.module.user.client_id = null;
                    wfw.ext.module.user.session_status = "close";

                    wfw.event.callEvent(wfw.ext.module.user, "wfw.ext.module.user", "logout");
                },
                continue_if_failed: false
            }, param)
        );

        wfw.ext.utils.callRequestListXARG(request_list, null);
        return false;
    }
};
