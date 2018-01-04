<?php
/**
 * Plugin Name:  VC Templates Import & Export
 * Description: Export & Import Visual Composer Templates (Saved Templates/My Templates) in few clicks
 * Plugin URI: https://wordpress.org/plugins/vc-templates-import-export/
 * Version: 0.1.4
 * Author: Tomiup
 * Author URI: http://tomiup.com/
 * Requires at least: 4.4
 * Tested up to: 4.5
 * License: GPLv3
 *
 * Text Domain: vc-templates-import-export
 * Domain Path: /languages/
 */

if ( ! class_exists( 'VC_Templates_Import_Export' ) ) {
	class VC_Templates_Import_Export {

		protected $option_name = 'wpb_js_templates';

		function __construct() {

			// We safely integrate with VC with this hook
			add_action( 'init', array( $this, 'integrate_with_vc' ) );
			if ( ! defined( 'VC_TIE_PATH' ) ) {
				define( 'VC_TIE_PATH', plugin_dir_path( __FILE__ ) );
			}
			if ( ! defined( 'VC_TIE_URL' ) ) {
				define( 'VC_TIE_URL', plugin_dir_url( __FILE__ ) );
			}
			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			add_action( 'tmu_sidebar_after', array( $this, 'sidebar_rss_news' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
			$this->vc_export_template();
		}


		public function admin_enqueue_assets() {
			wp_enqueue_style( 'tmu-admin-style', VC_TIE_URL . 'assets/css/admin.css', array() );
		}


		public function integrate_with_vc() {
			// Check if Visual Composer is installed
			if ( ! defined( 'WPB_VC_VERSION' ) ) {
				// Display notice that Visual Compser is required
				add_action( 'admin_notices', array( $this, 'show_vc_version_notice' ) );

				return;
			}
		}


		/**
		 * Show notice if your plugin is activated but Visual Composer is not
		 */
		public function show_vc_version_notice() {
			$plugin_data = get_plugin_data( __FILE__ );
			echo '
        <div class="updated">
          <p>' . sprintf( __( '<strong>%s</strong> requires <strong><a href="http://bit.ly/vc-plugin" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend' ), $plugin_data['Name'] ) . '</p>
        </div>';
		}


		public function menu_page() {
			add_submenu_page( 'vc-general', 'Templates Import & Export', 'Templates Import & Export', 'manage_options', 'vc-templates-import-export', array(
				$this,
				'page_callback'
			) );
		}


		/**
		 * Render submenu
		 * @return void
		 */
		public function page_callback() { ?>

			<div class="tmu-wrap">
				<h2><?php esc_html_e( 'Templates Manager', 'vc-templates-import-export' ); ?></h2>
				<?php
				if ( isset( $_GET['tab'] ) ) {
					$active_tab = $_GET['tab'];
				} else {
					$active_tab = 'export';
				}
				?>
				<div class="tmu-row">
					<div class="tmu-left tmu-col tmu-col-8">
						<h2 class="nav-tab-wrapper">
							<a href="?page=vc-templates-import-export&tab=export" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Export', 'vc-templates-import-export' ); ?></a>
							<a href="?page=vc-templates-import-export&tab=import" class="nav-tab <?php echo $active_tab == 'import' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Import', 'vc-templates-import-export' ); ?></a>
						</h2>

						<?php if ( $active_tab == 'export' ) : ?>
							<p>
								<?php
								$this->export_tab();
								?>
							</p>
						<?php else: ?>
							<?php if ( isset( $_FILES['file-upload'] ) ):
								if ( $_FILES['file-upload']['size'] > 0 ) {
									$uploaded = $this->upload_file();
									if ( $uploaded ) {
										$this->import_handle( $uploaded['file'] );
									} else {
										echo '<p>Error: Can\'t upload file to server, please CHMOD  0777 for folder wo-content/uploads</p>';
									}
								} else {
									echo '<p>Error: Please select file</p>';
								}
								?>
							<?php else: ?>
								<form action="" id="uploadtemplates" method="post" enctype="multipart/form-data">
									<p>
										<input type="file" name="file-upload" id="file-upload" />
									</p>
									<button class="button-bottom button-primary"><?php esc_html_e( 'Import Templates', 'vc-templates-import-export' ); ?></button>
								</form>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div class="tmu-right tmu-col tmu-col-4">
						<div class="tmu-inner">
							<?php do_action( 'tmu_sidebar_before' ); ?>

							<div class="tmu-box">

								<h4>Features</h4>
								<ul>
									<li>
										<strong>Import/Export templates</strong> in JSON format – take your custom templates.
									</li>
									<li><strong>Single Export</strong> – export only those templates you wish.</li>
									<li><strong>Multi Choose</strong> – export multi choose templates you wish
										<a href="http://bit.ly/temport" target="_blank"><strong>(Premium)</strong></a>.
									</li>
									<li><strong>Export All</strong> – Allow export all templates with a click
										<a href="http://bit.ly/temport" target="_blank"><strong>(Premium)</strong></a>.
									</li>
								</ul>

								Download Pro version here >> <a href="http://bit.ly/temport" target="_blank"><strong>TEMPORT</strong></a>

								<h4>Looking for support?</h4>
								<ul class="ul-square">
									<li>
										Use the
										<a target="_blank" href="https://wordpress.org/support/plugin/vc-templates-import-export">support forums</a>
									</li>
								</ul>

								<h4>Your Appreciation</h4>
								<ul class="ul-square">
									<li>
										<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/vc-templates-import-export?rate=5#postform">Leave a ★★★★★ plugin review on WordPress.org</a>
									</li>
								</ul>
							</div>

							<?php do_action( 'tmu_sidebar_after' ); ?>
						</div>
					</div>
				</div>

			</div><!-- /.wrap -->
			<?php
		}

		/**
		 * Upload JSON file
		 * @return boolean
		 */
		public function upload_file() {
			if ( isset( $_FILES['file-upload'] ) ) {
				add_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );
				$upload = wp_handle_upload( $_FILES['file-upload'], array( 'test_form' => false ) );
				remove_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );

				return $upload;
			}

			return false;
		}

		/**
		 * Add mime type for JSON
		 *
		 * @param array $existing_mimes
		 *
		 * @return string
		 */
		public static function json_upload_mimes( $existing_mimes = array() ) {
			$existing_mimes['json'] = 'application/json';

			return $existing_mimes;
		}


		/**
		 * @param $file
		 */
		public function import_handle( $file ) {
			if ( file_exists( $file ) ) {
				$contents   = file_get_contents( $file );
				$contents   = json_decode( $contents, true );
				$theme_mods = get_option( $this->option_name );

				// Mergers new options and clean
				$import_templates = array();
				if ( is_array( $contents ) ) {
					foreach ( $contents as $key => $template ) {
						$new_id                      = uniqid( 'Template_' );
						$template['name']            = $template['name'] . ' (' . current_time( 'mysql' ) . ')';
						$import_templates[ $new_id ] = $template;
					}
				}

				if ( $theme_mods ) {
					$theme_mods = array_merge( $theme_mods, $import_templates );
				} else {
					$theme_mods = $import_templates;
				}

				// Update theme mods
				$update = update_option( $this->option_name, $theme_mods );
				if ( $update ) {
					echo '<p><span class="dashicons dashicons-smiley"></span> ' . esc_html__( 'Import successfully!', 'vc-templates-import-export' ) . '</p>';
					echo '<p><a href="?page=vc-templates-import-export&tab=export">' . esc_html__( 'View All Templates', 'vc-templates-import-export' ) . '</a>';
				} else {
					echo '<p>' . esc_html__( 'Error: Templates exists!', 'vc-templates-import-export' ) . '</p>';
				}
			}

		}

		public function download_file( $content, $file_name = 'vc_templates.json', $type = 'text/plain' ) {
			if ( headers_sent() ) {
				wp_die( __( 'Something went wrong', 'vc-templates-import-export' ) );
			}

			header( "Content-type: $type" );
			header( "Content-Disposition: attachment; filename=$file_name" );

			echo $content;
			die();
		}


		public function export_tab() {
			$saved_templates = get_option( $this->option_name );
			$output          = '';

			if ( $saved_templates ) {
				$output .= '<form action="" id="vc-export-template" method="post">';
				$output .= '<input type="hidden" id="action" name="action" value="vc_export_template" />';
				wp_nonce_field( 'vc_export_template', '_wpnonce' );
				$output .= '<table class="wp-list-table widefat fixed striped">';
				$output .= '<thead>';
				$output .= '<tr>';
				$output .= '<td class="manage-column check-column"><input id="cb-select-all-1" disabled=disabled type="checkbox"></td><th scope="col" id="title" class="manage-column column-title"><strong><label for="cb-select-all-1">Template Name</label></strong></th>';
				$output .= '</tr>';
				$output .= '</thead>';
				$output .= '<tbody>';
				foreach ( $saved_templates as $key => $template ) {
					$output .= '<tr>';
					$output .= '<th class="check-column"><input id="' . $key . '" type="radio" name="templates[]" value="' . $key . '"></th>';
					$output .= '<td class="title-column"><label for="' . $key . '">' . $template['name'] . '</label></td>';
					$output .= '</tr>';
				}
				$output .= '</tbody>';
				$output .= '<tfoot>';
				$output .= '<tr>';
				$output .= '<td class="manage-column check-column"><input id="cb-select-all-1" disabled=disabled type="checkbox"></td><th scope="col" id="title" class="manage-column column-title"><strong><label for="cb-select-all-1">Select All (Premium)</label></strong></th>';
				$output .= '</tr>';
				$output .= '</tfoot>';
				$output .= '</table>';
				$output .= '<p><button class="button-bottom button-primary">' . esc_html__( 'Download Export File', 'vc-templates-import-export' ) . '</button></p>';
				$output .= '</form>';
			} else {
				$output .= esc_html__( 'No Templates found!!', 'vc-templates-import-export' );
			}
			echo $output;
		}


		/**
		 * Output the JSON for download
		 */
		public function vc_export_template() {
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'vc_export_template' ) {
				unset( $_POST['action'] );
				unset( $_POST['_wpnonce'] );
				unset( $_POST['_wp_http_referer'] );
				if ( isset( $_POST['templates'] ) ) {
					$export_data     = array();
					$saved_templates = get_option( $this->option_name );

					foreach ( $_POST['templates'] as $template ) {
						$export_data[ $template ] = $saved_templates[ $template ];
					}

					$text = json_encode( $export_data );
					self::download_file( $text, 'vc_templates.json' );
				}
			}
		}


		public function rss_news_widget() {
			echo '<div class="tmu-rss-widget rss-widget">';
			wp_widget_rss_output( array(
				'url'          => 'https://tomiup.com/feed/',
				'items'        => 5,
				'show_summary' => 0,
				'show_author'  => 0,
				'show_date'    => 0
			) );
			echo '</div>';
			echo '<div class="tips-news-footer">
					<a href="https://tomiup.com/?utm_source=vc-templates-import-export" target="_blank">Tomiup <span class="screen-reader-text">(opens in a new window)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
				</div>';
		}

		public function sidebar_rss_news() {
			echo '<div class="tmu-box">';
			echo '<h3 class="tmu-title-box">Best News & Tips</h3>';
			$this->rss_news_widget();
			echo '</div>';
		}

		// Function used in the action hook
		public function add_dashboard_widgets() {
			add_meta_box( 'tmu_dashboard_widget', 'Best News & Tips', array(
				$this,
				'rss_news_widget'
			), 'dashboard', 'side', 'high' );
		}

	}

	$VC_Templates_Import_Export = new VC_Templates_Import_Export();
}