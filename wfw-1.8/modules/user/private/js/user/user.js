
var selection = new Object();       // selection des dossiers ("name"=>element)

/*
OnLoad Event
*/
wfw.event.SetCallback( // window
    "wfw_window",
    "load",
    "onInit",
    function () {
        ListUser();
    },
    false
);

// [change la selection des utilisateurs]
function onChangeSelection(e, name) {
    if (e.checked)
        selection[name] = e;
    else if (typeof (selection[name]) != "undefined")
        delete (selection[name]);
}

// supprime la selection
function removeSelection()
{
    if(empty(selection)){
        wfw.ext.document.messageBox("Merci de sélectionner les utilisateurs à supprimer");
        return;
    }
    
    //confirme
    var msg="Les utilisateurs suivant seront définitivements supprimés :\n";
    for(var name in selection)
        msg+="   "+name+"\n";

    wfw.ext.document.confirm(msg,
        //ok
        function(){
            for(name in selection){
                var param = {
                    "onsuccess" : function(obj,args)
                    {
                        var element = $doc(name+"_item");
                        nodeRemoveNode(element);
                    }
                };
                var fields = {
                    wfw_uid:name
                };
                //envoie la requete
                wfw.request.Add(null,"req/user/delete.php",fields,wfw.utils.onCheckRequestResult_XARG,param,false);
            }
    
            //actualise
            selection = [];
        }
    );
}

// ajoute / actualise un template d'utilisateur a la liste
function insert_user_in_list(check_args,replacement) {
    check_args.session_date_fmt = date(DATE_DEFAULT, check_args.session_date); //format la date
    wfw.ext.listElement.insertFields($doc("user_template"), $doc("user_list"), check_args, null, null, replacement);
}

// charge la liste des utilisateurs
function ListUser() {
    var param = {
        "onsuccess": function (obj, args) {
            objRemoveChildNode($doc("user_list"), null, REMOVENODE_ALL);

            //ok ?
            if (empty(args.uid))
                return;

            //initialise la liste
            var uidList = strToArray(args.uid, ";");
            //var clientList = strToArray(args.client_id, ";");
            for (var i = 0; i < uidList.length; i++) {
                var param2 = {
                    "onsuccess": function (obj, args2) {
                        insert_user_in_list(args2,null);
                    }
                };
                wfw.request.Add(null, "req/user/check.php", { wfw_uid : uidList[i] }, wfw.utils.onCheckRequestResult_XARG, param2, false);
            }

            //tri par date
            sort_by('user_list', 'id', true);
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/list.php", null, wfw.utils.onCheckRequestResult_XARG, param, false);
}


// tri une liste
function sort_by(listId, field_name, bReverse) {
    var list = docGetElement(document, listId);
    //scan les articles a la recherche de criteres de selections
    var fields = wfw.ext.sort.fieldsFromElement(objGetChild(list, null));
    //   alert(wfw.toString(fields));
    fields = wfw.ext.sort.sortFields(fields, field_name);
    //   alert(wfw.toString(fields));
    reArrange(listId, fields, bReverse);
}

function reArrange(listId, fields, bReverse) {
    var list = docGetElement(document, listId);
    var key;
    if (bReverse) {
        for (key = 0; key < fields.length; key++) {
            objInsertNode(fields[key].node, list, null, INSERTNODE_BEGIN);
        }
    }
    else {
        for (key = fields.length - 1; key >= 0; key--) {
            objInsertNode(fields[key].node, list, null, INSERTNODE_BEGIN);
        }
    }
}

function user_connect(uid) {
    var param = {
        "onsuccess": function (obj, args) {
            var param2 = {
                "onsuccess": function (obj, args2) {
                    insert_user_in_list(args2,$doc(uid + "_item"));
                }
            };
            wfw.request.Add(null, "req/user/check.php", { wfw_uid: uid }, wfw.utils.onCheckRequestResult_XARG, param2, false);
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/login.php", { wfw_uid: uid }, wfw.utils.onCheckRequestResult_XARG, param, false);
}

function user_disconnect(uid,usid) {
    var param = {
        "onsuccess": function (obj, args) {
            var param2 = {
                "onsuccess": function (obj, args2) {
                    insert_user_in_list(args2,$doc(uid + "_item"));
                }
            };
            wfw.request.Add(null, "req/user/check.php", { wfw_uid: uid }, wfw.utils.onCheckRequestResult_XARG, param2, false);
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/logout.php", { wfw_usid: usid }, wfw.utils.onCheckRequestResult_XARG, param, false);
}

function user_active(uid,mail) {
    var param = {
        "onsuccess": function (obj, args) {
            var param2 = {
                "onsuccess": function (obj, args2) {
                    insert_user_in_list(args2,$doc(uid + "_item"));
                }
            };
            wfw.request.Add(null, "req/user/check.php", { wfw_uid: uid }, wfw.utils.onCheckRequestResult_XARG, param2, false);
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/activate.php", { wfw_mail: mail, wfw_active : "1" }, wfw.utils.onCheckRequestResult_XARG, param, false);
}

function user_unactive(uid,mail) {
    var param = {
        "onsuccess": function (obj, args) {
            var param2 = {
                "onsuccess": function (obj, args2) {
                    insert_user_in_list(args2,$doc(uid + "_item"));
                }
            };
            wfw.request.Add(null, "req/user/check.php", { wfw_uid: uid }, wfw.utils.onCheckRequestResult_XARG, param2, false);
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/activate.php", { wfw_mail: mail, wfw_active : "0" }, wfw.utils.onCheckRequestResult_XARG, param, false);
}

// charge la liste des fichiers
function openFileUpload(id) {
    wfw.ext.document.lockFrame(
        "client_file_upload.html?id=" + id,
        {
            onOK : function (doc) {
                return false;
            }
        }
    );
}

// charge la liste des fichiers
function openClientEditor(id) {
    wfw.ext.navigator.openPage("client_dossier","_self",{"id":id});
}

// ajoute un utilisateur
function add_user() {
    wfw.ext.document.lockElement(
        $doc("new_user_dialog"),
        {
            title:"Nouvel utilisateur...",
            //ok
            onOK : function (element) {
                var fields = wfw.form.get_fields("new_user_dialog");
                objAlertMembers(fields);
                var param = {
                    "onsuccess": function (obj, args2) {
                        var param2 = {
                            "onsuccess": function (obj, args2) {
                                insert_user_in_list(args2,$doc(args2.uid + "_item"));
                            }
                        };
                        wfw.request.Add(null, "req/user/check.php", { wfw_uid: args2.uid }, wfw.utils.onCheckRequestResult_XARG, param2, false);
                    }
                };
                //envoie la requete
                wfw.request.Add(null, "req/user/create.php", fields, wfw.utils.onCheckRequestResult_XARG, param, false);
            },
            onCancel : function () { }
        }
    );
}

// Déconnecte toutes les sessions en cours
function disconnectAll(){
    var param = {
        "onsuccess": function (obj, args) {
            window.location.reload();
        }
    };
    //envoie la requete
    wfw.request.Add(null, "req/user/clear.php", null, wfw.utils.onCheckRequestResult_XARG, param, false);
}
