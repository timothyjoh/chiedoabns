<?php
/**
 * GLOBAL VARIABLES.
 *
 * @package WordPress
 * @subpackage Laboratory
 */

global $theme_dir
, $theme_uri
, $home
, $theme_options
, $fi_tweets
, $client_name;

$theme_dir   = get_stylesheet_directory();
$theme_uri   = get_stylesheet_directory_uri();
$home        = home_url( '/' );
$client_name = 'Laboratory'; // Change this to an option.

// this is a great place for options that should be available to subsequent pages.
