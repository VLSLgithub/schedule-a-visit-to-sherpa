<?php
/**
 * Class Schedule_a_Visit_to_Sherpa_Send
 *
 * Sends our Data to Sherpa
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Schedule_a_Visit_to_Sherpa_Send {

	/**
	 * Schedule_a_Visit_to_Sherpa_Send constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

        add_action( 'gform_after_submission', array( $this, 'send_to_sherpa' ), 10, 2 );

    }
        
    /**
	 * Grab all Field Names and Values to send to Sherpa
	 * 
	 * @param		array $entry Entry Data
	 * @param		array $form  Form
	 *                      
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function send_to_sherpa( $entry, $form ) {
		
		// Grabbing saved Mappings from the DB
        $sherpa_mapping = get_option( 'vibrant_life_sherpa_mapping' );
        
		// Bail on Forms that are not sending to Sherpa
        if ( (int) $form['id'] !== get_schedule_a_visit_to_sherpa_form() ) return false;
        
    }

}

$instance = new Schedule_a_Visit_to_Sherpa_Send();