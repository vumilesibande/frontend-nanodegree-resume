(function ($, Drupal, once, settings) {
  'use strict';

  Drupal.behaviors.dsu_engage = {
    attach: (context, settings) => {
      $(once('dsu_engage_request_type', 'input[name="request_type"]')).change(function (ev) {
        let $this = $(this);
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
          event: "contact_form_type_change",
          eventCategory: "Contact Us",
          eventAction: "Contact Us Type Changed",
          eventLabel: $this.val(),
        });
        window.dataLayer.push({
          'event' : 'contact_interaction',
          'event_name' : 'contactus_type_changed',
          'topic' : $this.val(),
          'module_name' : drupalSettings.dsu_engage?.data?.module_name,
          'module_version' : drupalSettings.dsu_engage?.data?.module_version,
        });
      });
    }
  };
})(jQuery, Drupal, once, drupalSettings);
