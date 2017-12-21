"use strict";

// The below sets up the namespace for this script to use
var application = application || {};

if ('serviceWorker' in navigator  && themedir) {
  navigator.serviceWorker.register(themedir + '/js/serviceworker.js', {scope: '/'});
}

jQuery(document).ready(function() {
  // Scripts. Encapsulated in an anonymous function to avoid conflicts.
  (function($) {

    /**
     * Bind click events
     */
    $(document).click(function(ev) {
      var clicked = $(ev.target);
      // if user clicked search tool toggle
      if (
        clicked.is('#search-tool-toggle') ||
        clicked.parents().is('#search-tool-toggle'))
      {
        showOrHideSearchBar(ev);
      }
      // if user clicked mobile menu toggle or mobile menu close
      if (
        clicked.is('#mobile-menu-toggle') ||
        clicked.parents().is('#mobile-menu-toggle') ||
        clicked.is('#mobile-menu-close') ||
        clicked.parents().is('#mobile-menu-close')
      )
      {
        toggleMobileNavMenu(ev);
      }
    })

    /**
     * Toggle search bar on click
     */
    function showOrHideSearchBar(ev) {
      ev && ev.preventDefault();
      var searchBarDropdown = $('#searchform-dropdown');
      searchBarDropdown.toggleClass('showing');
    }

    /**
     * Show / hide mobile nav menu
     */
    var $mobileNavContainer = $('#mobile-menu-container');
    var $body = $('body');
    //$(document).on('click', '#mobile-menu-toggle, #mobile-menu-close', toggleMobileNavMenu);
    function toggleMobileNavMenu(ev) {
      ev && ev.preventDefault();
      $mobileNavContainer.toggleClass('active');
      $body.toggleClass('mobile-nav-showing');
    }

  })( jQuery ); // End scripts
});
