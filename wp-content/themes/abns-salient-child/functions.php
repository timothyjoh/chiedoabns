<?php
/**
 * Salient Child Theme Functions.
 * Note - functions have been modularized.
 *
 * @package WordPress
 * @subpackage Salient Child Theme
 */

/**
 * GLOBAL VARIABLES.
 */
require_once 'functions/setup-global-variables.php';


/**
 * Required plugins
 * Uses TGM Activation to Give Alerts as to which plugins are needed for this theme.
 * You can add or remove required plugins in the file below.
 */
require_once 'functions/setup-required-plugins.php';


/**
 * Helper functions
 */
require_once 'functions/helpers/application-helpers.php';
require_once 'functions/helpers/header-helpers.php';
require_once 'functions/helpers/post-helpers.php';
require_once 'functions/helpers/theme-options-helpers.php';


/**
 * Theme setup
 * all the usual random tidbits of code in functions.php can be found here
 */
require_once 'functions/setup-theme.php';

/**
 * Styles and Scripts
 */
require_once 'functions/load-scripts-and-styles.php';

/**
 * Widgets
 */
require_once 'functions/setup-widgets.php';

/**
 * Custom Post Types
 * Include custom post type registration and CPT-specific taxonomies, user roles, etc. in a CPT sub-folder for each CPT.
 */

/**
 * AJAX
 */
require_once 'functions/setup-ajax.php';

