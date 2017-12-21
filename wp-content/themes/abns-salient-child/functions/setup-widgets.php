<?php

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function laboratory_widgets_init() {
  register_sidebar( array(
    'name'          => 'Main Sidebar',
    'id'            => 'main-sidebar',
    'description'   => 'Default Template Sidebar',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h2 class="widget-title">',
    'after_title'   => '</h2>',
  ) );
}
add_action( 'widgets_init', 'laboratory_widgets_init' );