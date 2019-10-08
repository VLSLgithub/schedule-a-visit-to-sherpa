<?php
/**
 * Plugin Name: Schedule a Visit to Sherpa
 * Plugin URI: https://github.com/VLSLgithub/schedule-a-visit-to-sherpa
 * Description: Sends "Schedule a Visit" form data as leads to Sherpa
 * Version: 1.0.1
 * Text Domain: schedule-a-visit-to-sherpa
 * Author: Eric Defore
 * Author URI: https://realbigmarketing.com/
 * Contributors: d4mation
 * GitHub Plugin URI: VLSLgithub/schedule-a-visit-to-sherpa
 * GitHub Branch: master
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Schedule_a_Visit_to_Sherpa_Plugin' ) ) {

	/**
	 * Main Schedule_a_Visit_to_Sherpa_Plugin class
	 *
	 * @since	  1.0.0
	 */
	class Schedule_a_Visit_to_Sherpa_Plugin {
		
		/**
		 * @var			array $plugin_data Holds Plugin Header Info
		 * @since		1.0.0
		 */
		public $plugin_data;
		
		/**
		 * @var			array $admin_errors Stores all our Admin Errors to fire at once
		 * @since		1.0.0
		 */
		private $admin_errors;

		/**
		 * @var         Schedule_a_Visit_to_Sherpa_API API Class
		 *
		 * @since		1.0.0
		 */
		public $api;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  object self::$instance The one true Schedule_a_Visit_to_Sherpa_Plugin
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			
			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				
				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %s or higher to be installed!', 'Outdated Dependency Error', 'schedule-a-visit-to-sherpa' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '4.4', '<a href="' . admin_url( 'update-core.php' ) . '"><strong>WordPress</strong></a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			if ( ! class_exists( 'RBM_FieldHelpers' ) ) {
				
				$this->admin_errors[] = sprintf( _x( 'To use the %s Plugin, %s must be active as either a Plugin or a Must Use Plugin!', 'Missing Dependency Error', 'schedule-a-visit-to-sherpa' ), '<strong>' . SCHEDULEAVISITTOSHERPA()->plugin_data['Name'] . '</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">' . __( 'RBM Field Helpers', 'schedule-a-visit-to-sherpa' ) . '</a>' );

				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}

				return false;

			}
			
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );

			if ( ! defined( 'Schedule_a_Visit_to_Sherpa_Plugin_VER' ) ) {
				// Plugin version
				define( 'Schedule_a_Visit_to_Sherpa_Plugin_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'Schedule_a_Visit_to_Sherpa_Plugin_DIR' ) ) {
				// Plugin path
				define( 'Schedule_a_Visit_to_Sherpa_Plugin_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'Schedule_a_Visit_to_Sherpa_Plugin_URL' ) ) {
				// Plugin URL
				define( 'Schedule_a_Visit_to_Sherpa_Plugin_URL', plugin_dir_url( __FILE__ ) );
			}
			
			if ( ! defined( 'Schedule_a_Visit_to_Sherpa_Plugin_FILE' ) ) {
				// Plugin File
				define( 'Schedule_a_Visit_to_Sherpa_Plugin_FILE', __FILE__ );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = Schedule_a_Visit_to_Sherpa_Plugin_DIR . '/languages/';
			$lang_dir = apply_filters( 'schedule_a_visit_to_sherpa_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'schedule-a-visit-to-sherpa' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'schedule-a-visit-to-sherpa', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/schedule-a-visit-to-sherpa/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/schedule-a-visit-to-sherpa/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'schedule-a-visit-to-sherpa', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/schedule-a-visit-to-sherpa/languages/ folder
				load_textdomain( 'schedule-a-visit-to-sherpa', $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( 'schedule-a-visit-to-sherpa', false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function require_necessities() {

			$this->api = false;

			require_once Schedule_a_Visit_to_Sherpa_Plugin_DIR . 'core/api/class-schedule-a-visit-to-sherpa-api.php';

			if ( $api_key = get_option( 'vibrant_life_sherpa_api_key' ) ) {
				$this->api = new Schedule_a_Visit_to_Sherpa_API( $api_key );
			}
			
			require_once Schedule_a_Visit_to_Sherpa_Plugin_DIR . 'core/admin/class-schedule-a-visit-to-sherpa-admin.php';

			require_once Schedule_a_Visit_to_Sherpa_Plugin_DIR . 'core/front/class-schedule-a-visit-to-sherpa-send.php';
			
		}
		
		/**
		 * Show admin errors.
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  HTML
		 */
		public function admin_errors() {
			?>
			<div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
					<p>
						<?php echo $notice; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				'schedule-a-visit-to-sherpa',
				Schedule_a_Visit_to_Sherpa_Plugin_URL . 'assets/css/style.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Schedule_a_Visit_to_Sherpa_Plugin_VER
			);
			
			wp_register_script(
				'schedule-a-visit-to-sherpa',
				Schedule_a_Visit_to_Sherpa_Plugin_URL . 'assets/js/script.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Schedule_a_Visit_to_Sherpa_Plugin_VER,
				true
			);
			
			wp_localize_script( 
				'schedule-a-visit-to-sherpa',
				'scheduleAVisitToSherpa',
				apply_filters( 'schedule_a_visit_to_sherpa_localize_script', array() )
			);
			
			wp_register_style(
				'schedule-a-visit-to-sherpa-admin',
				Schedule_a_Visit_to_Sherpa_Plugin_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Schedule_a_Visit_to_Sherpa_Plugin_VER
			);
			
			wp_register_script(
				'schedule-a-visit-to-sherpa-admin',
				Schedule_a_Visit_to_Sherpa_Plugin_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : Schedule_a_Visit_to_Sherpa_Plugin_VER,
				true
			);
			
			wp_localize_script( 
				'schedule-a-visit-to-sherpa-admin',
				'scheduleAVisitToSherpa',
				apply_filters( 'schedule_a_visit_to_sherpa_localize_admin_script', array() )
			);
			
		}
		
	}
	
} // End Class Exists Check

/**
 * The main function responsible for returning the one true Schedule_a_Visit_to_Sherpa_Plugin
 * instance to functions everywhere
 *
 * @since	  1.0.0
 * @return	  \Schedule_a_Visit_to_Sherpa_Plugin The one true Schedule_a_Visit_to_Sherpa_Plugin
 */
add_action( 'plugins_loaded', 'Schedule_a_Visit_to_Sherpa_load' );
function Schedule_a_Visit_to_Sherpa_load() {

	require_once __DIR__ . '/core/schedule-a-visit-to-sherpa-functions.php';
	SCHEDULEAVISITTOSHERPA();

}
