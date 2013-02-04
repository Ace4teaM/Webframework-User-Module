/*
 *
 **/
YUI(wfw_yui_config(wfw_yui_base_path)).use('node', 'event', 'panel', 'dd-plugin', 'wfw-user', 'wfw-navigator', 'wfw-form', 'wfw-utils', 'wfw-request', function (Y)
{
    var wfw = Y.namespace("wfw");
    
    //connection status change
    var onLoad = function(e)
    {
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
    };
    
    //initialise les evenements
    Y.one('window').on('load', onLoad);
});
