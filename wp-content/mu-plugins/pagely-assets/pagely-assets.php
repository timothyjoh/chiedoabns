<?php
/**
 * Shared code for Pagely Management Plugin UI
 */

// file url for plugin assets
if( ! defined('PAGELY_ASSETS_URL') )
   define( 'PAGELY_ASSETS_URL', WP_CONTENT_URL.'/mu-plugins/pagely-assets/' );

require_once __DIR__.'/inc/PagelyGlobalOptions.php';
require_once __DIR__.'/inc/func/misc.php';
require_once __DIR__.'/inc/PagelyApi.php';
require_once __DIR__.'/inc/func/pagely_api.php';
require_once __DIR__.'/inc/func/pagely_dashboard_widget.php';
require_once __DIR__.'/inc/func/pagely_bulletins.php';
require_once __DIR__.'/inc/admin_notices.php';
require_once __DIR__.'/inc/WP_Pagely.php';
