jQuery(document).ready(function ($) {
    "use strict";

    $(function () {
        // Ajax for request new token.
        $("#siteimprove-integration-token-request").on("click", function () {
            $(this).prop("disabled", true);
            $(this).parent().find(".spinner").remove();
            $(this)
                .parent()
                .append('<span class="spinner is-active no-float"></span>');
            $.post(
                ajaxurl,
                {
                    action: "siteimproveRequestToken",
                },
                function (response) {
                    var el = $("#siteimprove-integration-token-request");
                    el.parent().find(".spinner").remove();
                    el.prop("disabled", false);
                    $("#siteimprove-integration-token").val(response);
                }
            );
        });
    });
});
