<?php
/**
 * Theme Options Helper Functions.
 *
 * @package WordPress
 * @subpackage Laboratory Theme
 */

/**
 * Is a theme option checked or not?
 *
 * @param {string} $option_value - the option field value (in this case, checked or not).
 * @return {boolean}
 */
function is_option_checked( $option_value ) {
  if (
    ! isset( $option_value ) ||
    ! $option_value
  ) {
    return false;
  }
  return '1' === $option_value ||
    1 === $option_value ||
    true === $option_value;
}


/**
 * Retrieve a theme options value and avoid PHP "undefined index" errors
 * NOTE - this method does nothing with the database; it merely works with data already pulled from db.
 * $theme_options global var is set in functions/setup-global-variables.php
 *
 * @param {string} $option_id - the option id.
 * @param {string} $default - default value to use if option value is null.
 * @return {string|false}
 */
function get_theme_option( $option_id, $default = null ) {
  global $theme_options;
  if (
    ! isset( $theme_options ) ||
    ! is_array( $theme_options )
  ) {
    return false;
  }

  if (
    isset( $theme_options[ $option_id ] ) &&
    ! empty( $theme_options[ $option_id ] )
  ) {
    return $theme_options[ $option_id ];
  }

  if ( isset( $default ) ) {
    return $default;
  }

  return false;
}
