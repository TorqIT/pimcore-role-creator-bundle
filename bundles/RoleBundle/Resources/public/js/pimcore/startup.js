pimcore.registerNS("pimcore.plugin.TorqITPimcoreRoleCreatorBundle");

pimcore.plugin.TorqITPimcoreRoleCreatorBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.TorqITPimcoreRoleCreatorBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("TorqITPimcoreRoleCreatorBundle ready!");
    }
});

var TorqITPimcoreRoleCreatorBundlePlugin = new pimcore.plugin.TorqITPimcoreRoleCreatorBundle();
