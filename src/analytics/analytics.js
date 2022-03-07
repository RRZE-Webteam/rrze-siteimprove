(function () {
    let sz = document.createElement("script");
    sz.type = "text/javascript";
    sz.async = true;
    sz.src =
        "https://siteimproveanalytics.com/js/siteanalyze_" +
        siteanalyze.code +
        ".js";
    let s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(sz, s);
})();
