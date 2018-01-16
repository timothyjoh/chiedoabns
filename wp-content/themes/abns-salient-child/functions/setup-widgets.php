<?php
/**
 * Define additional widgets
 *
 * @package WordPress
 * @subpackage Salient Child theme
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function abns_extra_widgets_init() {
  register_sidebar( array(
    'name'          => 'Footer Copyright Center',
    'id'            => 'footer-copyright-center',
    'description'   => 'Footer copyright section - right column',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h2 class="widget-title">',
    'after_title'   => '</h2>',
  ) );
}
add_action( 'widgets_init', 'abns_extra_widgets_init' );
