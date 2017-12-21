"use strict";

// The below sets up the namespace for this script to use
var application = application || {};

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
        clicked.is('#some-element-id') ||
        clicked.parents().is('#some-element-id'))
      {
        doSomething(ev);
      }
    })

    /**
     * Toggle search bar on click
     */
    function doSomething(ev) {
      ev && ev.preventDefault();
    }

  })( jQuery ); // End scripts
});
