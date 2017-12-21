<?php
/**
 * Ajax in WordPress
 *
 * Below is the work preparing ajax to be used on the backside, front-side, or both.
 * Note that page-specific work could be placed within its own directory for complete modularity.
 *
 * @package WordPress
 * @subpackage Laboratory
 */

/**
 * ENQUEUE AND LOCALIZE SCRIPTS
 */
function laboratory_register_ajax_scripts() {
  global $theme_uri;
  // you could add conditionals here to only load this on certain pages.
  wp_enqueue_script(
    'ajax-example-script',
    $theme_uri . '/js/ajax-example.js',
    array( 'jquery', 'bootstrapjs', 'applicationscript' ),
    '1.0.0',
    true
  );

  // add in data that will get passed to the php function when the ajax call fires
  // e.g. yourAwesomeVars.ajaxUrl
  // note - the ajax call could also get info from the browser via data passed by element props (e.g. id, name, or dataset) or query vars.
  wp_localize_script(
    'ajax-example-script',
    'yourAwesomeVars',
    array(
      // ajax url is required for ajax calls to work within WordPress.
      'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
      // any other data could get passed in here. e.g.
      'something' => 'cool',
      'someNum'   => 89352,
      'obj'       => array(
        'name' => 'Bob',
        'age'  => 33,
      ),
    )
  );
}
add_action( 'wp_enqueue_scripts', 'laboratory_register_ajax_scripts' );


/**
 * AJAX FUNCTIONS (DEFINE ACTIONS TO FIRE)
 */

// these ajax functions can be broken up into modular parts and placed in different files.
require_once $theme_dir . '/functions/ajax/general-ajax-functions.php';


/**
 * ACTION HOOKS
 */

// to set up an ajax script to fire ONLY back-end (wp dashboard), add the following.
// you could even wrap this in is_admin() but that seems like overkill.
add_action( 'wp_ajax_my_action', 'my_action' );

// to set up an ajax script to fire on the front-end and back-end (wp dashboard), add the following.
add_action( 'wp_ajax_retrieve_list_of_page_urls', 'retrieve_list_of_page_urls' );
add_action( 'wp_ajax_nopriv_retrieve_list_of_page_urls', 'retrieve_list_of_page_urls' );
