"use strict";

/**
 * AJAX IN WORDPRESS
 * Below is an ajax call skeleton making it easy for you to add your own ajax to WordPress.
 * Each function can be attached to the ajaxFunctions namespace and then used in later files!
 */

var ajaxFunctions = ajaxFunctions || {};

jQuery(document).ready(function() {
  // Scripts. Encapsulated in an anonymous function to avoid conflicts.
  (function($) {

    /**
     * add this button to your page to see this in action!
     * <button id="ajax-example-test-button">Get List of Pages</button>
     */
    $('#ajax-example-test-button').click(getPageList);

    /**
     * Get a list of published posts from the WordPress backend.
     */
    function getPageList(ev) {
      ev && ev.preventDefault();
      $.ajax({
        url: yourAwesomeVars.ajaxUrl,
        type: 'get',
        data: {
          action: 'retrieve_list_of_page_urls',
          // you can add other data here.
          // the easiest way to grab data from the page is to grab ev.target.id, ev.target.dataset.someField, or ev.target.name
          // or, you could utilize query vars. (see getQueryVar in old-js-functions.js)
        },
        success: handleRetrievedPages,
      });
    }

    /**
     * Get a list of published posts from the WordPress backend.
     */
    function handleRetrievedPages(response) {
      if (!response) { return; }

      var posts = JSON.parse(response);

      if (!posts || !posts.length) { return; }

      // prepare ajax results ul
      if (!$('#ajax-results').length) {
        $('#ajax-example-test-button').after('<ul id="ajax-results"></ul>');
      }
      $('#ajax-results').empty();

      posts.map(function(post, index) {
        var postMarkup = '<li><a href="' + post.url + '">' + post.title + '</a></li>'
        $('#ajax-results').append(postMarkup);
      })
    }


  })( jQuery ); // End scripts
});


