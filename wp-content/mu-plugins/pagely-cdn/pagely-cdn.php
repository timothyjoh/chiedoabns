<?php
/*
Plugin Name: Pagely CDN
Plugin URI: http://pagely.com
Description: Rewrites Images/CSS/JS files on your blog to use a CDN
Version: 1.4
Author: Pagely
Author URI: http://pagely.com
License: GPL
*/

// CDN
if( ! defined('F_PAGELY_CDN') )
   define( 'F_PAGELY_CDN', TRUE); 
   
// needs to be included for is_plugin_active_for_network
require_once ABSPATH . '/wp-admin/includes/plugin.php';
require_once __DIR__.'/library.php';

function pagely_cdn_network_wide()
{
    return is_multisite();
}

// WP-API support
add_action( 'wp_json_server_before_serve', 'do_pagely_cdn_wp_api', 99, 1 );
function do_pagely_cdn_wp_api()
{
    if (defined('JSON_API_VERSION'))
    {
        $rewriter = new Pagely_CDNLinksRewriterWordpress(Pagely_CDNConfig::instance(pagely_cdn_network_wide()));
        add_filter( 'json_serve_request', array($rewriter, 'rewriteJson'), 10, 5 );
    }
}

// rewrite action
add_action('template_redirect', 'do_pagely_cdn_ob_start');
function do_pagely_cdn_ob_start()
{
	$rewriter = new Pagely_CDNLinksRewriterWordpress(Pagely_CDNConfig::instance(pagely_cdn_network_wide()));
	$rewriter->registerOutputBuffer();
}

// admin hooks
function pagely_cdn_activate($network_wide)
{
	$config = new Pagely_CDNConfig(false);
	$config->registerOptions($network_wide);
}
register_activation_hook( __FILE__, 'pagely_cdn_activate');

// This function deletes all settings if the plugin gets deactivated.
function pagely_cdn_deactivate($network_wide)
{
	$config = new Pagely_CDNConfig(false);
	$config->deleteOptions($network_wide);
}
register_deactivation_hook( __FILE__, 'pagely_cdn_deactivate');

// Menu Moved to
// admin menu
/* $hook = pagely_cdn_network_wide() ? 'network_admin_menu' : 'admin_menu';
add_action($hook, 'pagely_cdn_menu');

function pagely_cdn_menu()
{
// Check for MS dashboard
    if (pagely_cdn_network_wide())
        $parent = "settings.php";
    else
        $parent = "options-general.php";


  add_submenu_page(
        $parent,
        'Pagely CDN',
        'Pagely CDN',
        "manage_options",
        __FILE__,
        'pagely_cdn_options'
    );
}*/

// admin interface
function pagely_cdn_options()
{
	$config = Pagely_CDNConfig::instance(pagely_cdn_network_wide());
	if ( isset($_POST['action']) && ( $_POST['action'] == 'update_pagely_cdn' ))
	{
		
	
		
		check_admin_referer('pagely_cdn_options');
		
		$config->setFromArray($_POST);
		$config->save();
		
			// relay the response.
		$alert = Pagely_Alert::instance();
		$alert->setAlert('CDN Options Saved',true);
	}

	

	$example_cdn_uri = str_replace('http://', 'http://', str_replace('.', '', rtrim(get_option('siteurl'),'/')).".c.presscdn.com");
	$example_https_cdn_uri = 'https://xxx-presscdn-xx-xx-pagely.netdna-ssl.com';

    $wpapi_is_installed = defined('JSON_API_VERSION');

	include __DIR__.'/admin_html.php';
}


add_action('pagely_purge_cdn_all', 'pagely_purge_cdn');
function pagely_purge_cdn()
{
	$zone_id = Pagely_CDNConfig::instance()->zone_id;
	
	$api_call = pagely_api_request($method = 'POST', $uri = '/cdn_zone/purge', $params = array('id'	=> $zone_id) );
	$result = json_decode($api_call);
	
	// relay the response.
	$alert = Pagely_Alert::instance();
	if (isset($result->error)) {
		$alert->setAlert('CDN Purge: '.$result->error, false);
	} else {
		$alert->setAlert('CDN Purge: Success');
	}

	
}
