<?php
/**
 * Laboratory Theme Helper Functions.
 *
 * @package WordPress
 * @subpackage WP Starter Theme
 */

/**
 * Retrieve a list of pages via ajax
 */
function retrieve_list_of_page_urls() {
  global $wpdb;

  $pages     = get_list_of_pages();
  $page_urls = array();

  foreach ( $pages as $id => $title ) {
    array_push($page_urls, array(
      'id'    => $id,
      'title' => $title,
      'url'   => get_permalink( $id ),
    ));
  }

  echo wp_json_encode( $page_urls );
  wp_die();
}

