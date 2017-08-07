<?php
/*
Plugin Name: Pagely App Satts
Plugin URI: http://pagely.com
Description: Show/graph stats for this domain
Version: 1.4
Author: Pagely
Author URI: http://pagely.com
License: GPL
*/

// STATS
if( ! defined('F_PAGELY_STATS') )
   define( 'F_PAGELY_STATS', TRUE); 

function pagely_app_stats_setup_scripts()
{
    // only enqueue if we are on our admin page
    if (!isset($_GET['page']) || $_GET['page'] != 'app_stats')
    {
        return true;
    }

	$src = "//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js";
	wp_enqueue_script( 'datatables', $src, array('jquery'),'' , true );

	$src = "//cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css";
	wp_enqueue_style( 'datatables', $src );

    $src = '//cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js';
	wp_enqueue_script( 'd3', $src );

    $src = '//cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.js';
	wp_enqueue_script( 'c3', $src );

    $src = '//cdnjs.cloudflare.com/ajax/libs/c3/0.4.10/c3.min.css';
	wp_enqueue_style( 'c3', $src );
}
add_action( 'admin_init', 'pagely_app_stats_setup_scripts' );
	
function pagely_app_stats_options()
{

	//add_action( 'admin_print_styles', 'load_font_awesome' );
	/* gets doamin usage stats for last 30 days */
	$app_stats_period = pagely_app_stats_app();

	
	/* returns a summary of CDN usage for 30 days. */
	$cdn_summary = pagely_app_stats_cdn();
	
	

	// load the view
	include __DIR__.'/admin_html.php';
}



function pagely_app_stats_app()
{
    //if ( false === ( $usage_by_period = get_transient( 'pagely_app_stats' ) ) )
    if (true)
    {
      $api = new PagelyApi();
		$config = $api->config();
		$app_id = $config->id;
		
		$api_call = $api->apiRequest($method = 'GET', $uri = '/domains/single', $params = array('id'	=> $app_id,'inc_usage' => true) );
		$result = json_decode($api_call);
		
		//build an object holding them
		$usage_by_period = array();
		$usage_by_period['graph_bandwidth'] = $usage_by_period['graph_cdn'] = $usage_by_period['graph_tables'] = $usage_by_period['graph_users'] = array();
		$usage_by_period['graph_comments'] = $usage_by_period['graph_file'] = $usage_by_period['graph_db'] = array();
		foreach ($result->usage as $u) {
			$usage_by_period['p'][$u->period] = new stdClass();
			$usage_by_period['p'][$u->period]->bandwidth = $u->bandwidth_in + $u->bandwidth_out;
			$usage_by_period['p'][$u->period]->requests =  $u->requests;
			$usage_by_period['p'][$u->period]->cdn_bw = $u->cdn_bw;
			$usage_by_period['p'][$u->period]->file = $u->file;
			$usage_by_period['p'][$u->period]->backup = $u->backup;
			$usage_by_period['p'][$u->period]->db = $u->db;
			$usage_by_period['p'][$u->period]->tables = $u->tables;
			$usage_by_period['p'][$u->period]->users_all = $u->users_all;
			$usage_by_period['p'][$u->period]->comments_sp = $u->comments_sp;
			$usage_by_period['p'][$u->period]->comments_all = $u->comments_all;
			$usage_by_period['p'][$u->period]->users_all = $u->users_all;
			
			// simple arrays for graphs
            $period = strtotime($u->period)*1000;
			$usage_by_period['graph_bandwidth'][] = [$period, $usage_by_period['p'][$u->period]->bandwidth];
			$usage_by_period['graph_cdn'][] =  [$period, $u->cdn_bw];
			$usage_by_period['graph_requests'][] =  [$period, $u->requests];
			$usage_by_period['graph_tables'][] = [$period, $u->tables];
			$usage_by_period['graph_users'][] = [$period, $u->users_all];
			$usage_by_period['graph_comments'][] = [$period, $u->comments_all];
			$usage_by_period['graph_file'][] =  [$period, $u->file];
			$usage_by_period['graph_db'][] = [$period, $u->db];
		}
	
		set_transient( 'pagely_app_stats', $usage_by_period, 12 * HOUR_IN_SECONDS );
	}
	return $usage_by_period;
}

function pagely_app_stats_cdn()
{

	if ( false === ( $cdn_summary = get_transient( 'pagely_app_stats_cdn' ) ) ) {
 
        $api = new PagelyApi();
		$config = $api->config();
		$app_id = $config->id;
		
		$zone_id = Pagely_CDNConfig::instance()->zone_id;
		
		$api_call = $api->apiRequest($method = 'GET', $uri = '/cdn_zone/single', $params = array('id'	=> $zone_id,'inc_stats' => true) );
		$result = json_decode($api_call);
		
		$cdn_summary =  $result->stats->summary;
	
		set_transient( 'pagely_app_stats_cdn', $cdn_summary, 13 * HOUR_IN_SECONDS );
	}
	return $cdn_summary;
}
