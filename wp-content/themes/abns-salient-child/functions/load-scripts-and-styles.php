<?php
/**
 * Load theme scripts and styles.
 *
 * @package WordPress
 * @subpackage Laboratory
 */

/**
 * Load theme scripts and styles.
 * Note - the child theme stylesheet is loaded from within the Salient theme (not sure how)
 */
function salient_child_enqueue_styles() {
  // styles.
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array( 'font-awesome' ) );
  wp_enqueue_style( 'catalog-style', get_stylesheet_directory_uri() . '/css/catalog-style.css' );
  wp_enqueue_style( 'my-courses-style', get_stylesheet_directory_uri() . '/css/my-courses-style.css' );

  // scripts.
  wp_enqueue_script( 'applicationscript', get_stylesheet_directory_uri() . '/js/application.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles' );

/**
 * Load course catalog javascript.
 */
function course_catalog_js() {
    wp_enqueue_script( 'course_catalog_js', get_stylesheet_directory_uri() . '/js/tmci-catalog.js', array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'course_catalog_js' );

/**
 * CSS For the admin page
 */
function load_admin_style() {
  wp_enqueue_style( 'admin-css', get_stylesheet_directory_uri() . '/functions/admin-assets/admin.css', false, '1.0.0' );
  add_editor_style( get_stylesheet_directory_uri() . '/functions/admin-assets/admin.css' );
}
if ( is_admin() ) {
  add_action( 'admin_enqueue_scripts', 'load_admin_style' );
}
