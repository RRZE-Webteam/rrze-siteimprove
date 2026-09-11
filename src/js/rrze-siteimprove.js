jQuery(document).ready(function siteimproveReady($) {
    "use strict";

    function loadAnalytics() {
        var script;
        var firstScript;

        if (
            typeof siteanalyze === "undefined" ||
            !siteanalyze.baseUrl ||
            !siteanalyze.code
        ) {
            return;
        }

        script = document.createElement("script");
        script.type = "text/javascript";
        script.async = true;
        script.src = siteanalyze.baseUrl + siteanalyze.code + ".js";

        firstScript = document.getElementsByTagName("script")[0];
        firstScript.parentNode.insertBefore(script, firstScript);
    }

    loadAnalytics();
});
