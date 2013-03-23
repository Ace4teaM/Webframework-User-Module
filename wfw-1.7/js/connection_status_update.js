
MyApp.onConnectionStatusChange = function(Y)
{
    var wfw = Y.namespace("wfw");

    wfw.User.onConnectionStatusChange = function(status){

        var status_msg = "Non-Connecté";
        switch(status){
            case "USER_CONNECTED":
                status_msg = "Connecté";
                break;
            case "USER_CONNECTION_NOT_EXISTS":
                break;
            case "USER_CONNECTION_IP_REFUSED":
                break;
            case "USER_DISCONNECTED":
                break;
        }
        Y.one("#connection_status").set("text",status_msg);
    }

    //
    wfw.User.checkConnection();
    
}

MyApp.Loading.callback_list.push(MyApp.onConnectionStatusChange);