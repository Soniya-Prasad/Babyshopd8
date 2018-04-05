/**
 * @file
 * Contains the definition of the behaviour jsStoreLocator.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attaches the JS test behavior to weight div.
   */

      var stores = $('#store_data').val();
      var sanatize_country = stores.split(':');
    
      $("#store-locator-search-input").autocomplete({
        source: sanatize_country
      });

})(jQuery, Drupal);