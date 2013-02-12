/*
 *
 **/
YUI(wfw_yui_config(wfw_yui_base_path)).use('node', 'event', function (Y)
{
    var wfw = Y.namespace("wfw");

    //connection status change
    var onLoad = function(e)
    {
        //cache le contenu
        Y.Node.all("body > *").hide();
        
        //initialise l'interface
        var statusPanel = Ext.create('Ext.Panel', {
                header :false,
                //title: 'Status',
                region: 'north',     // position for region
                split: true,         // enable resizing
                margins: '0 5 5 5',
                html: Y.Node.one("#header").get("innerHTML")
            });
            
        var contentPanel = Ext.create('Ext.Panel', {
                header :false,
                //title: 'Content',
                region: 'center',     // position for region
                height: 100,
                split: true,         // enable resizing
                margins: '0 5 5 5',
                html: Y.Node.one("#content").get("innerHTML")
            });
            
        var menuPanel = Ext.create('Ext.Panel', {
                title: 'Menu',
                layout: {
                    // layout-specific configs go here
                    type: 'accordion',
                    titleCollapse: false,
                    animate: true,
                    activeOnTop: true
                },
                region: 'west',     // position for region
                width: 200,
                split: true,         // enable resizing
                margins: '0 5 5 5',
                /*html: Y.Node.one("#menu").get("innerHTML")*/
                items: [{
                    title: 'Administrateur',
                    html: Y.Node.one("#menu1").get("innerHTML")
                },{
                    title: 'Visiteur',
                    html: Y.Node.one("#menu2").get("innerHTML")
                },{
                    title: 'Utilisateur',
                    html: Y.Node.one("#menu3").get("innerHTML")
                }],
                renderTo: Ext.getBody()
            });
            
        var footerPanel = Ext.create('Ext.Panel', {
                header :false,
                //title: 'Pied de page',
                region: 'south',     // position for region
                split: true,         // enable resizing
                margins: '0 5 5 5',
                html: Y.Node.one("#footer").get("innerHTML")
            });
            
        var viewport = Ext.create('Ext.Viewport', {
            layout: 'border',
            items: [contentPanel,menuPanel,statusPanel,footerPanel]
        });
        
    };
    
    //initialise les evenements
    Y.one('window').on('load', onLoad);
});
