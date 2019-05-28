<?php
/**
 * Class Schedule_a_Visit_to_Sherpa_Admin
 *
 * Admin Page for configuring the Plugin
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Schedule_a_Visit_to_Sherpa_Admin {

	/**
	 * GF_ActOn constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'submenu_page' ), 99 );

		// Fix <title> tag for the Settings Page
		add_filter( 'admin_title', array( $this, 'admin_title' ), 10, 2 );
		
		add_filter( 'parent_file', array( $this, 'parent_file' ) );
		
		add_filter( 'submenu_file', array( $this, 'submenu_file' ), 10, 2 );
		
		// This is not a typo, they are actually two different hooks. Don't look at me, I didn't write WP.
		add_action( 'adminmenu', array( $this, 'adminmenu' ) );

	}

	/**
	 * Register Settings using the WP Settings API
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function register_settings() {

		add_settings_section(
			'schedule_a_visit_to_sherpa_settings_section',
			__( 'Schedule a Visit Leads to Sherpa', 'schedule-a-visit-to-sherpa' ),
			__return_false(),
			'schedule-a-visit-to-sherpa'
		);

		$fields = $this->get_settings_fields();

		foreach ( $fields as $field ) {

			$field = wp_parse_args( $field, array(
				'settings_label' => '',
			) );

			$callback = 'rbm_fh_do_field_' . $field['type'];

			add_settings_field(
				$field['name'],
				$field['settings_label'],
				( is_callable( $callback ) ) ? 'rbm_fh_do_field_' . $field['type']  : 'rbm_fh_missing_callback',
				'schedule-a-visit-to-sherpa',
				'schedule_a_visit_to_sherpa_settings_section',
				$field
			);

			register_setting( 'schedule_a_visit_to_sherpa_settings_section', $field['name'] );

		}

	}

	/**
	 * Add Settings Page for our Plugin
	 * 
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function submenu_page() {

		add_submenu_page(
			'options-general.php',
			_x( 'Schedule a Visit Leads to Sherpa', 'Admin Page Title', 'schedule-a-visit-to-sherpa' ),
			_x( 'Schedule a Visit to Sherpa', 'Admin Sub-Menu Title', 'schedule-a-visit-to-sherpa' ),
			'manage_options',
			'schedule-a-visit-to-sherpa',
			array( $this, 'settings_page' )
		);
		
		// Move our menu from Settings to under Forms
		
		global $submenu;

		$settings_index = null;
		foreach ( $submenu['options-general.php'] as $key => $menu_item ) {
			
			// Index 2 is always the child page slug
			if ( $menu_item[2] == 'schedule-a-visit-to-sherpa' ) {
				$settings_index = $key;
				break;
			}
			
		}
		
		// We need to make the path more absolute
		$submenu['options-general.php'][ $settings_index ][2] = 'options-general.php?page=schedule-a-visit-to-sherpa';
		
		// Move the Menu Item
		$submenu['gf_edit_forms'][] = $submenu['options-general.php'][ $settings_index ];
		unset( $submenu['options-general.php'][ $settings_index ] );

	}

	/**
	 * Fix the Admin Title since our pages "don't exist"
	 * 
	 * @param		string $admin_title The page title, with extra context added
	 * @param		string $title       The original page title
	 *                                               
	 * @access		public
	 * @since		1.0.0
	 * @return		string Admin Title
	 */
	public function admin_title( $admin_title, $title ) {
		
		global $current_screen;
		
		if ( $current_screen->base == 'settings_page_schedule-a-visit-to-sherpa' ) {
			return __( 'Schedule a Visit Leads to Sherpa', 'learndash-slack' ) . $admin_title;
		}
		
		return $admin_title;
		
	}
	
	/**
	 * Filters the Parent File when rendering the Admin Menu
	 * 
	 * @param		string $parent_file Parent File, the top-level Menu Item
	 *                                                             
	 * @access		public
	 * @since		1.0.0
	 * @return		string Parent File
	 */
	public function parent_file( $parent_file ) {
	
		global $current_screen;
		global $self;

		if ( $current_screen->base == 'settings_page_schedule-a-visit-to-sherpa' ) {

			// Render this as the Active Page Menu
			$parent_file = 'admin.php?page=gf_edit_forms';

			// Ensure the top-level "Settings" doesn't show as active
			$self = 'gf_edit_forms';

		}

		return $parent_file;

	}
	
	/**
	 * Filters the Submenu File when rendering the Admin Menu
	 * 
	 * @param		string $submenu_file Submenu File, the chosen Menu Item
	 * @param		string $parent_file  Parent File, the top-level Menu Item
	 *                                                              
	 * @access		public
	 * @since		1.0.0
	 * @return		string Submenu File
	 */
	public function submenu_file( $submenu_file, $parent_file ) {

		global $current_screen;

		if ( $current_screen->base == 'settings_page_schedule-a-visit-to-sherpa' ) {
			$submenu_file = 'options-general.php?page=schedule-a-visit-to-sherpa';
		}

		return $submenu_file;

	}
	
	/**
	 * Resets the Parent File after the Admin Menu is rendered
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function adminmenu() {
	
		global $current_screen;
		global $parent_file;

		if ( $current_screen->base == 'settings_page_schedule-a-visit-to-sherpa' ) {
			// We have to reset this after the Menu is generated so Settings Errors still appear
			$parent_file = 'options-general.php';
		}

	}

	/**
	 * Output Fields on the Settings Page
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function settings_page() {

		?>

		<div class="wrap schedule-a-visit-to-sherpa-settings">
			
			<form id="schedule-a-visit-to-sherpa-form" method="post" action="options.php">
				
				<?php echo wp_nonce_field( 'schedule_a_visit_to_sherpa_data', 'schedule_a_visit_to_sherpa_nonce' ); ?>

				<?php settings_fields( 'schedule_a_visit_to_sherpa_settings_section' ); ?>

				<?php do_settings_sections( 'schedule-a-visit-to-sherpa' ); ?>
				
				<?php submit_button(); ?>

			</form>

        </div>

		<?php

	}

	/**
	 * Returns Filterable Array of our Settings Fields
	 * 
	 * @access      private
	 * @since       1.0.0
	 * @return      array Settings Field Parameters. See RBM Field Helpers
	 */
	private function get_settings_fields() {

		$fields = array();

		$fields[] = array(
			'name' => 'vibrant_life_sherpa_api_key',
			'type' => 'password',
			'settings_label' => __( 'Sherpa API Key', 'schedule-a-visit-to-sherpa' ),
			'no_init' => true,
			'option_field' => true,
			'description' => '<p class="description">' . __( 'Enter an API Key and save your changes for the other fields to appear', 'schedule-a-visit-to-sherpa' ) . '</p>',
			'description_tip' => false,
		);

		if ( ! $api_key = get_option( 'vibrant_life_sherpa_api_key' ) ) {

			return $fields;

		}

		if ( ! $form = get_theme_mod( 'vibrant_life_schedule_a_visit_form' ) ) {

			$forms = wp_list_pluck( RGFormsModel::get_forms( null, 'title' ), 'title', 'id' );

			$fields[] = array(
				'name' => 'schedule_a_visit_to_sherpa_form',
				'type' => 'select',
				'settings_label' => __( 'Which Form is the Schedule a Visit Form?', 'schedule-a-visit-form' ),
				'no_init' => true,
				'option_field' => true,
				'option_none' => __( '-- Choose a Gravity Form --', 'schedule-a-visit-to-sherpa' ),
				'options' => $forms,
			);

		}

		$locations_query = new WP_Query( array(
			'post_type' => 'facility',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		) );

		$locations = wp_list_pluck( $locations_query->posts, 'post_title', 'ID' );

		$sherpa_communities = apply_filters( 'schedule_a_visit_to_sherpa_communities', array(
			2 => __( 'Lodges of Durand', 'schedule-a-visit-to-sherpa' ),
			4 => __( 'New Friends Memory Care Kalamazoo', 'schedule-a-visit-to-sherpa' ),
			1 => __( 'Vibrant Life Superior Township', 'schedule-a-visit-to-sherpa' ),
			3 => __( 'Vibrant Life Temperance', 'schedule-a-visit-to-sherpa' ),
		) );

		$fields[] = array(
			'name' => 'vibrant_life_sherpa_mapping',
			'type' => 'repeater',
			'settings_label' => __( 'Location/Sherpa Mapping', 'schedule-a-visit-to-sherpa' ),
			'no_init' => true,
			'option_field' => true,
			'description' => '<p class="description">' . __( 'Associate each Location with each Community within Sherpa', 'schedule-a-visit-to-sherpa' ) . '</p>',
			'description_tip' => false,
			'fields' => array(
				'location_id' => array(
					'type' => 'select',	
					'args' => array(
						'label' => '<strong>' . __( 'Location', 'schedule-a-visit-to-sherpa' ) . '</strong>',
						'options' => $locations,
						'option_none' => __( '-- Choose a Location --', 'schedule-a-visit-to-sherpa' ),
						'input_class' => 'regular-text',
					),
				),
				'community_id' => array(
					'type' => 'select',	
					'args' => array(
						'label' => '<strong>' . __( 'Sherpa Community', 'schedule-a-visit-to-sherpa' ) . '</strong>',
						'options' => $sherpa_communities,
						'option_none' => __( '-- Choose a Community from Sherpa --', 'schedule-a-visit-to-sherpa' ),
						'input_class' => 'regular-text',
						'description' => '<p class="description">' . __( 'These values are hardcoded. We cannot add or remove Sherpa Communities without updating the plugin.', 'schedule-a-visit-to-sherpa' ) . '</p>',
						'description_tip' => false,
					),
				),
			),
		);

		return apply_filters( 'schedule_a_visit_to_sherpa_settings_fields', $fields );

	}

}

$instance = new Schedule_a_Visit_to_Sherpa_Admin();