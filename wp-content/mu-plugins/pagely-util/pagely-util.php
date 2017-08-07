<?php
/*
Plugin Name: Pagely-Utils
Plugin URI: 
Description: Utility code that runs without a UI
Author: Joshua Eichorn
Author URI: http://pagely.com
Version: 0.1
*/


// We set a error_log path in php-fpm so if you want to see your debug log
// in wp-content, it has to be a symlink
$log = ABSPATH.'/wp-content/debug.log';
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG == true)
{
    if (file_exists($log) && !is_link($log))
        rename($log, "$log.1");

    if (!file_exists($log)) {
        $tmp = glob(ABSPATH."/../mnt/log/*-php.error.log");
        if (isset($tmp[0])) {
            $file = basename($tmp[0]);
            symlink("../../mnt/log/$file", $log);
        }
    }
}
else if (file_exists($log) && is_link($log))
{
    unlink($log);
}

// add a header to reset login rate limiting on successful login
function pagely_add_ratelimit_reset_header()
{
    header('X-Pagely-Ratelimit-Reset: login');
}
add_action('wp_login', 'pagely_add_ratelimit_reset_header');

// Default settings
// there is a good chance we aren't in a chroot if VARNISH_SERVERS isn't defined, so lets try
// to be clever in that case
if (!defined('VARNISH_SERVERS') && file_exists('/srv/pagely/conf/pool_configs.php'))
	include '/srv/pagely/conf/pool_configs.php';

if( ! defined('DISABLE_WP_CRON') ) 
	define('DISABLE_WP_CRON', true);

if( ! defined('AUTOSAVE_INTERVAL') ) 
	define( 'AUTOSAVE_INTERVAL', 300 ); // Seconds

if( ! defined('WP_CRON_LOCK_TIMEOUT') )
	define( 'WP_CRON_LOCK_TIMEOUT', 120 );

if( ! defined('AUTOMATIC_UPDATER_DISABLED') )
   define( 'AUTOMATIC_UPDATER_DISABLED', true);

if (! defined('WP_AUTO_UPDATE_CORE') ) 
   define('WP_AUTO_UPDATE_CORE', false);

if( ! defined('VARNISH_SERVERS') )
   define( 'VARNISH_SERVERS', '127.0.0.1');

if( ! defined('PMEMCACHED_SERVERS') )
   define( 'PMEMCACHED_SERVERS', '127.0.0.1:11211'); 


// backwards compat for p3 and p10   
if ( isset($_SERVER['HTTP_X_PAGELY_SSL']) && 'on' == strtolower( $_SERVER['HTTP_X_PAGELY_SSL'] ) ) {
	$_SERVER['HTTPS'] = 'on';
}
