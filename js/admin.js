(function ($) {
  'use strict';

  $(function () {

    // Ajax for request new token.
    jQuery('#siteimprove-integration-token-request').on('click', function () {
      jQuery(this).prop('disabled', true);
      jQuery(this).parent().find('.spinner').remove();
      jQuery(this).parent().append('<span class="spinner is-active no-float"></span>');
      jQuery.post(
          ajaxurl,
          {
            'action': 'siteimprove_request_token'
          },
          function (response) {
            var el = jQuery('#siteimprove-integration-token-request');
            el.parent().find('.spinner').remove();
            el.prop('disabled', false);
            jQuery('#siteimprove-integration-token').val(response);
          }
      );
    });
});

})(jQuery);
