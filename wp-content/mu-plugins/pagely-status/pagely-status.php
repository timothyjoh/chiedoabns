<?php
/*
Plugin Name: Pagely Status
Plugin URI: http://pagely.com
Description: Provides a simple status page, that can be used to verify that a site is up
Version: 1.6
Author: Pagely
Author URI: http://pagely.com
License: GPL
*/

// Status
if( ! defined('F_PAGELY_STATUS') )
   define( 'F_PAGELY_STATUS', TRUE); 
   
function pagely_status_init()
{
    global $wpdb, $wp_version, $wp_object_cache;

    // this will only work if you have a none default permalink structure, which is the norm but not 100%
    if (!get_option('pagely_status_inited'))
    {
        add_rewrite_rule('^pagely/status', 'index.php?pagely_status=1');
        flush_rewrite_rules();
        update_option('pagely_status_inited', true);
    }

    if (!empty($_GET['pagely_status']) || (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/pagely/status/'))
    {
        header('Cache-Control: private, max-age=0, no-cache');
        echo "<pre>";
        echo "Pagely Site Status: ".date('Y-m-d H:i:s T')."\n";
        echo "Wordpress: $wp_version\n";

        $count = $wpdb->get_var("select count(*) from $wpdb->options");
        $db = 'OK';
        if ($count < 2)
        {
            $db = 'FAIL';
        }
        echo "Database: $db\n";

        $redis = 'NA';
        if (defined('WP_REDIS_SERVER') && isset($wp_object_cache->redis))
        {
            $redis = 'FAIL';
            if (is_object($wp_object_cache->redis))
            {
                $wp_object_cache->redis->set('__status', 1);
                $r = $wp_object_cache->redis->get('__status');
                if ($r == 1)
                    $redis = 'OK';
            }
        }
        echo "Redis: $redis\n";
        if ($db == 'FAIL' || $redis == 'FAIL')
            http_response_code(520);

        exit;
    }
}
add_action('init', 'pagely_status_init');
