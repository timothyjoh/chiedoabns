<?php
/**
 * Handles the display of the upgrades notifications and functionalities.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display notices about available upgrades.
 *
 * @since 1.2.1
 * @return void
 */
function wpum_upgrades_notices() {

  if( ! get_option( 'wpum_did_121_update' ) && get_option( 'wpum_version_upgraded_from' ) ) {

    $update_action = add_query_arg( array( 'wpum_upgrade' => 121 ), admin_url( 'index.php' ) );

    $message = sprintf( esc_html__( 'WP User Manager needs to upgrade the fields database, click the button below to start the upgrade.', 'wpum' ) );
    $button = '<a href="'. esc_url( $update_action ) .'" class="button-primary">'.esc_html__( 'Start upgrade', 'wpum' ).'</a>';

    ?>
    <div class="updated">
        <p><?php echo $message; ?> <?php echo $button; ?></p>
    </div>
    <?php

  }

}
add_action( 'admin_notices', 'wpum_upgrades_notices' );

/**
 * Dislays the upgrade window.
 *
 * @since 1.2.1
 * @return void
 */
function wpum_upgrades_window() {

  if( isset( $_GET['wpum_upgrade'] ) && is_numeric( $_GET['wpum_upgrade'] ) ) {

    $get_upgrade = absint( $_GET['wpum_upgrade'] );
    $upgrade_function = 'wpum_upgrade_function_v'.$get_upgrade;

    $page_title = esc_html__( 'WP User Manager Upgrade', 'wpum' );
    $upgrade_not_available = esc_html__( 'No upgrade has been found.', 'wpum' );
    $upgrade_completed = esc_html__( 'Upgrade successfully completed.', 'wpum' );
    $return_button = '<a href="' . admin_url( 'index.php' ) . '">'. esc_html__( 'Return to dashboard', 'wpum' ) .'</a>';

    if( function_exists( $upgrade_function ) ) {

      call_user_func( $upgrade_function );

      wp_die( $upgrade_completed . ' ' . $return_button , $page_title );

    } else {

      wp_die( $upgrade_not_available, $page_title );

    }

  }

}
add_action( 'admin_init', 'wpum_upgrades_window' );

/**
 * Upgrade function required to fix bug in v1.2.0
 * The bug caused certain fields to set themselves as not required anymore.
 * This function fixes the must-required fields and sets them as required again.
 *
 * @since 1.2.1
 * @return void
 */
function wpum_upgrade_function_v121() {

  if( get_option( 'wpum_did_121_update' ) )
    return;

  // Get fields from the database
  $primary_group = WPUM()->field_groups->get_group_by('primary');

  $args = array(
		'id'           => $primary_group->id,
		'array'        => true,
		'number'       => -1,
	);

	$data = WPUM()->fields->get_by_group( $args );

  foreach ( $data as $key => $field ) {

    if( $field['meta'] == 'username' || $field['meta'] == 'user_email' || $field['meta'] == 'password' || $field['meta'] == 'nickname' || $field['meta'] == 'display_name' || $field['meta'] == 'display_name' ) {
      WPUM()->fields->update( $field['id'], array( 'is_required' => true ) );
    }

  }

  update_option( 'wpum_did_121_update', true );

}
