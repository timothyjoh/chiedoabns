<?php
 /**
  *  Collect learner analytics.  Expose learner data on REST endpoints.
  *  This data can be used for analysis, customization, adaptivity ...
  *  @author PMalcolm, Scitent
  *  @date created July 2017
  */

class Propel_Learner_Data {
	private $attempt_table = 'propel_attempt_table';

	function __construct() {
		// Register the endpoints with WP API
		add_action( 'rest_api_init', function () {
			// GET data
		    register_rest_route( 'scitent/v1', '/learner-stats/(?P<id>\d+)', array(
				'methods' => 'GET,POST',
				'callback' => array($this,'learner_stats'),
				'permission_callback' => array($this,'harden_endpoint')
		    ) );
		    register_rest_route( 'scitent/v1', 'learner-first-tries/(?P<id>\d+)', array(
				'methods' => 'GET,POST',
				'callback' => array($this,'learner_first_tries'),
				'permission_callback' => array($this,'harden_endpoint')
		    ) );
		    register_rest_route( 'scitent/v1', 'learner-latest-first-tries/(?P<id>\d+)', array(
				'methods' => 'GET,POST',
				'callback' => array($this,'learner_latest_first_tries'),
				'permission_callback' => array($this,'harden_endpoint')
		    ) );

		    // POST data
		    register_rest_route( 'scitent/v1', '/learner-post-attempt', array(
				'methods' => 'POST',
				'callback' => array($this,'learner_post_attempt'),
				'permission_callback' => array($this,'harden_endpoint')
		    ) );
		} );

		// Settings page
		if ( is_admin() ) {
			add_action( 'admin_menu',
				array( $this, 'add_settings_menu' ) );
		}

		// JavaScript includes
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_quiz_js' ) ); 

		// Dashboard for learner data
		add_shortcode( 'propel-learner-analytics', array( $this, 'learner_dashboard' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_learner_dashboard_scripts') );

	}

	public function register_wpapi() {
		wp_register_script( 'wpapi', plugins_url('../vendor/wpapi/wpapi.min.js', 
			__FILE__), array(), '', true );
	}

	public function enqueue_quiz_js() {
		global $post_type;
		if ( $post_type !== 'sfwd-quiz' ) {
			return;
		}
		$this->register_wpapi();
		wp_enqueue_script( 'wpapi' );
		wp_enqueue_script( 'propel-learner-data-js', plugins_url( '../js/propel-learner-data.js', 
		    __FILE__), array('jquery','wpapi'), '', true ); // "TRUE" - ADDS JS TO FOOTER
		$this->localize_wpapi('propel-learner-data-js');
	}

	public function localize_wpapi( $context ) {
		wp_localize_script( $context, 'WP_PROPEL_API_Settings', array(
				'endpoint' => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'user_id' => get_current_user_id()
			) );
	}

	////////////////////////
	//// WP ADMIN BACKEND UI

	/**
	 * Adds the PROPEL Learner Data Settings menu to the 'Settings' admin
	 * @author pmalcolm, Scitent
	 * @action admin_menu
	 */
	function add_settings_menu() {
		add_options_page(
			'PROPEL Learner Data Settings',
			'PROPEL Learner Data Settings',
			'manage_options',
			'propel-learnerdata-settings',
			array( $this, 'render_settings_page' )
		);
	}

	function render_settings_page() {
		?>
			<h2>Manage Learner Data Table(s)</h2>
		<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists('_wpnonce', $_POST) ) { 
			if( wp_verify_nonce($_POST['_wpnonce'],'propel-create-attempt-table') ) { 
				$this->create_attempt_table();
			} 
		} ?>

		<?php if( $this->table_exists_already($this->attempt_table) ) { ?>
			<p>The Attempts table is already set up.</p>
		<?php } else { ?>
			<form method="post" action="options-general.php?page=propel-learnerdata-settings">
				<?php wp_nonce_field( 'propel-create-attempt-table' ); ?>
				<input type="submit" value="Create Attempts Table in Database">
			</form>
		<?php }
	}
    
	////////////////////////
	//// API ENDPOINTS

	function learner_stats( $request ) {
		$user_id = $request['id'];
		global $wpdb;
		$sql = "
SELECT stat.*, stat_ref.user_id, stat_ref.create_time FROM `{$wpdb->prefix}wp_pro_quiz_statistic` AS stat
INNER JOIN `{$wpdb->prefix}wp_pro_quiz_statistic_ref` AS stat_ref
ON stat.statistic_ref_id = stat_ref.statistic_ref_id
WHERE stat_ref.user_id = $user_id;
";
		$att_results = $wpdb->get_results(
          $sql,
          ARRAY_A
        );
        if( false === $att_results ) {
          return false;
        }

		return array($att_results);
	}

	function learner_first_tries( $request, $latest = false ) {
		$user_id = $request['id'];
		$att_results = $this->db_get_attempts( $user_id );
		if( false === $att_results ) {
			return false;
		}

		$filter_results = $this->filter_first_tries( $att_results );
		if( $latest ) {
			$filter_results = $this->filter_most_recent( $filter_results );
		}
		$filter_by = $_GET + (array) json_decode( $request->get_body() );
		return $this->filter_requested_params( 
			$filter_results, 
			$filter_by
		);
	}

	function learner_latest_first_tries( $request ) {
		return $this->learner_first_tries( $request, true );
	}

	function learner_post_attempt( $request ) {
		global $wpdb;
		$r = json_decode($request->get_body()); // TODO - sanitize this!
		$response_content = json_encode($r->response_content);
		$session_nonce = $this->get_nonce();
		$user_id = get_current_user_id();
		$question_hash = md5( $this->db_get_question_data($r->question_id) . $this->db_get_answer_data($r->question_id) );
		$points = $this->grade( $r->question_id, $r->response_content );
		$timestamp = date("Y-m-d H:i:s");
		$database_says = $wpdb->query($wpdb->prepare("
INSERT INTO `{$wpdb->prefix}{$this->attempt_table}`
(`session_nonce`, `user_id`, `quiz_id`,      `question_id`,     `question_hash`,  `context`,       `response_content`, `points`, `response_time`, `recorded_timestamp`) VALUES
('$session_nonce',  $user_id, {$r->quiz_id},  {$r->question_id}, '$question_hash', '{$r->context}', '$response_content',  $points, {$r->response_time}, '$timestamp');
", NULL ) );
		if( !$database_says || is_wp_error( $database_says ) ){
			return new WP_Error( 'propel_store_learner_attept_failed', __( 'Store learner data failed.' ), array( 'status' => 422 ) );
		}
		return '{ successfully stored attempt }';
	}

	////////////////////////
	//// API SECURITY

	private function get_nonce() {
		$nonce = null;
		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];
		} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = $_SERVER['HTTP_X_WP_NONCE'];
		}
		return $nonce;
	}

	function harden_endpoint() {
		// Determine if there is a nonce.
		if ( null === $this->get_nonce() ) {
			// No nonce at all -- no data for you.
			return false;
		}
		return true;
	}

	////////////////////////
	//// RESPONSE EVALUATION

	/**
	 *  Returns int, the point value scored by the user
	 */
	function grade( $question_id, $response_content ) {
		$answer_data = unserialize( $this->db_get_answer_data( $question_id ) );
		$answer_array = array_map( function( WpProQuiz_Model_AnswerTypes $mat, $boolval) {
			return $mat->isCorrect() && $boolval === 1 ? 1 : 0;
		}, $answer_data, $response_content );
		return array_sum( $answer_array );
	}

	/////////////////////////////////////////////////////////////////////////
	//// LEARNER ANALYTICS DASHBOARD SHORTCODE
	function learner_dashboard( $given_atts ) {
		wp_enqueue_script( 'react-chartist-js' );
		wp_enqueue_script( 'wpapi' );
		$this->localize_wpapi( 'react-chartist-js' );
		// wp_enqueue_script( 'propel-learner-data-dashboard' );
		ob_start();
		?>
			<style>
				g .ct-bar {
					stroke-width: 20px;
				}
				g .ct-series-b .ct-bar { 
					stroke: #A50000; /* red */
				}
				g .ct-series-c .ct-bar { 
					stroke: #22AC00; /* green */
				}
			</style>
			<link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
			<div id="propel-learner-container" style="width: 75%;">
			</div>
		<?php
		return ob_get_clean();
	}

	function register_learner_dashboard_scripts() {
		$this->register_wpapi();
		wp_register_script( 'react-chartist-js', plugins_url( '../vendor/react-chartist-js/Chart.bundle.js', 
		    __FILE__), array( 'wpapi' ), '', true );
		wp_register_script( 'propel-learner-data-dashboard', plugins_url( '../js/shortcodes/propel-learner-data-dashboard.js', 
		    __FILE__), array('jquery', 'react-chartist-js'), '', true );
	}
	/////////////////////////////////////////////////////////////////////////
	//// DATABASE INTERFACE

	/**
	 *  Returns string, the LearnDash/WpProQuiz question data
	 */
	function db_get_question_data( $question_id ) {
		global $wpdb;
		return $wpdb->get_var(
"
SELECT `question` from {$wpdb->prefix}wp_pro_quiz_question
WHERE id = $question_id;
");
	}

	/**
	 *  Returns string, the LearnDash/WpProQuiz answer data
	 */
	function db_get_answer_data( $question_id ) {
		global $wpdb;
		return $wpdb->get_var(
"
SELECT `answer_data` from {$wpdb->prefix}wp_pro_quiz_question
WHERE id = $question_id;
");
	}

	function db_get_attempts( $user_id ) {
		global $wpdb;
		$sql = "
SELECT * from `{$wpdb->prefix}{$this->attempt_table}`
WHERE user_id = $user_id;
";
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	////////////////////////
	//// DATABASE SETUP, HELPERS

	protected function create_attempt_table() {
		global $wpdb;
		$table_suffix = $this->attempt_table;
		if( $this->table_exists_already( $table_suffix ) ) {
		  return;
		}
		$table_name = $wpdb->prefix . $table_suffix;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
		  attempt_id bigint(20)           NOT NULL AUTO_INCREMENT,
		  session_nonce varchar(10)       NOT NULL,
		  user_id bigint(20)              NOT NULL,
		  quiz_id bigint(20)              NOT NULL,
		  question_id bigint(20)          NOT NULL,
		  question_hash varchar(32)       NOT NULL,
		  context varchar(40)             NOT NULL,
		  response_content text           NOT NULL,
		  points int(10)                  NOT NULL,
		  response_time bigint(8)         NOT NULL,
		  recorded_timestamp datetime     NOT NULL,
		  PRIMARY KEY  attempt_id (attempt_id)
		) $charset_collate;";
		$this->db_delta( $sql );  
	}

    protected function table_exists_already( $table_suffix ) {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_suffix;
        $result_already_exists = $wpdb->query( $wpdb->prepare(
            "
                SHOW TABLES LIKE %s
            ",
            $table_name
        ) );
        if( !empty( $result_already_exists ) ) { // if the table already exists
            return true;
        }
        return false;
    }

    protected function db_delta( $sql ) {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        try {
            dbDelta( $sql );
        } catch (Exception $e) {
            // handle exception
        }         
    }

	/**
	 * filter only first attempt at each question.
	 * return a filtered version of the array
	 */
    protected function filter_first_tries( $rows ) {
		$filter_results = array();
		$previous_question_id = -1;
		foreach ($rows as $idx => $row) {
			$current_question_id = intval( $row['question_id'] );
			if( $previous_question_id !== $current_question_id ) {
				array_push( $filter_results, $row );
				$previous_question_id = $current_question_id;
			}
		}
		return $filter_results;
    }

	/**
	 * filter only first "Check" types.
	 * return a filtered version of the array
	 */
    protected function filter_most_recent( $rows ) {
    	$rows = array_reverse( $rows );
    	$seen_ids = array();
		$filter_results = array();
		foreach ($rows as $idx => $row) {
			if( !in_array( $row['question_id'], $seen_ids ) ) {
				array_push( $filter_results, $row );
				array_push( $seen_ids, $row['question_id'] );
			} 
		}
		return array_reverse($filter_results);
    }

    protected function filter_requested_params( $to_filter, $filter_by ) {
		if( empty($filter_by) ) {
			return $to_filter;
		} else { // user-specified stats, eg., /wp-json/scitent/v1/learner-first-tries/20?points&question_id
			return array_map( function( $row ) use ($filter_by) { 
				return array_filter( $row, function( $field, $field_name ) use ($filter_by) {
					return array_key_exists( $field_name, $filter_by );
				}, ARRAY_FILTER_USE_BOTH );
			}, $to_filter );
		}    	
    }

}

new Propel_Learner_Data();