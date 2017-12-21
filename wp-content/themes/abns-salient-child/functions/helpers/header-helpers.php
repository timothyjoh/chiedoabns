<?php
/**
 * Laboratory Header functions.
 *
 * @package WordPress
 * @subpackage Laboratory Theme
 */

/**
 * Display the main menu for ava care
 * Note - the main nav container markup can be found in templates/nav-main.php
 *
 * @param {string} $menu_slug - defaults to 'header-menu'.
 */
function laboratory_nav_menu( $menu_slug = 'header-menu' ) {
  wp_nav_menu(array(
    'theme_location' => $menu_slug,
    'items_wrap'     => get_laboratory_nav_wrap(),
  ));
}

/**
 * Get normal nav wrap with a search icon appended to the end
 */
function get_laboratory_nav_wrap() {
  // default value of 'items_wrap' is <ul id="%1$s" class="%2$s">%3$s</ul>'.
  $wrap  = '<ul id="%1$s" class="%2$s">';
  $wrap .= '%3$s';
  // append the search icon.
  $wrap .= load_template_part( 'templates/header', 'search-toggle-icon' );
  $wrap .= load_template_part( 'templates/header', 'social-icons' );
  $wrap .= '</ul>';
  return $wrap;
}

/**
 * Display the mobile menu for ava care
 * Note - the mobile nav container markup can be found in templates/nav-mobile.php
 *
 * @param {string} $menu_slug - the slug for the menu.
 */
function laboratory_mobile_nav_menu( $menu_slug = 'header-menu' ) {
  wp_nav_menu(array(
    'menu'            => $menu_slug,
    'theme_location'  => $menu_slug,
    'depth'           => 2,
    'container'       => 'div',
    'container_class' => 'navbar-collapse',
    'container_id'    => 'bs-example-navbar-collapse-1',
    'menu_class'      => 'menu nav navbar-nav',
    'walker'          => new WP_Bootstrap_Navwalker(),
  ));
}
