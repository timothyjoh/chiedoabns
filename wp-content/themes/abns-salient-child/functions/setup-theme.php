<?php
/**
 * Generic theme setup functions
 *
 * @package WordPress
 * @subpackage Salient Child theme
 */

/*
 * Image link to none by default
 */
update_option( 'image_default_link_type','none' );

/*
 * Add thumbnail support
 */
add_theme_support( 'post-thumbnails' );

/**
 * Hide WordPress Version meta tag - security setting
 */
function wpstarter_remove_version() {
  return '';
}
add_filter( 'the_generator', 'wpstarter_remove_version' );



/***********************************************************
 * Functions originally found in Salient Child functions.php
 ***********************************************************/

if( !defined( 'PROPEL_CSR_ADMIN_EMAIL' ) ) {
  define( 'PROPEL_CSR_ADMIN_EMAIL', 'purchase.orders@scitent.com' );
}


//sku product page redirects
function redirect_sku_slugs() {
  global $wpdb;
  $uri = explode('/', $_SERVER["REQUEST_URI"]);
  if (count($uri)>1 && $uri[1] == 'sku') {
    error_log("sku slugs loaded: ".$uri[2]);
    $product_query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1";
    $product_id = $wpdb->get_var( 
      $wpdb->prepare( 
        $product_query, 
        $uri[2] 
      ) );
    error_log($product_id);
    error_log(get_permalink( $product_id ));
    if ($product_id){
      wp_redirect(get_permalink( $product_id )); 
      exit;
    }
  }
}
add_action( 'init', 'redirect_sku_slugs' );


//////////////////////////


function prefix_reset_metabox_positions(){
  //delete_user_meta( wp_get_current_user()->ID, 'meta-box-order_post' );
  //delete_user_meta( wp_get_current_user()->ID, 'meta-box-order_page' );
  //delete_user_meta( wp_get_current_user()->ID, 'meta-box-order_product' );
  delete_user_meta( wp_get_current_user()->ID, 'meta-box-order_custom_post_type' );
}
add_action( 'admin_init', 'prefix_reset_metabox_positions' );

// WP User Manager Integrations for ABNS:

add_action('wpum/form/register/after/field=wpum_abns_pledge',
  array( 'Propel_User_Reg', 'generic_required_warning' ), 10 );

add_filter( 'wpum/form/validate=register', function( $passed, $fields, $values ) {
  if( '' === $values['register']['wpum_abns_pledge'] ) {
    return new WP_Error( 'registration-validation-error', __('You must agree to the pledge to register.','propel'));
  }
  if( '' === $values['register']['wpum_abns_terms'] ) {
    return new WP_Error( 'registration-validation-error', __('You must agree to the terms and conditions to register.','propel'));
  }
  return $passed;
}, 10, 3 );

// Don't require nickname or display_name
add_filter( 'wpum_form_field', function( $field ) {
  if( in_array($field['meta'],array('nickname','display_name')) ) {
    $field['required'] = '0';
  }
  return $field;
});