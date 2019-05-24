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

		$location_id = false;
		$email = false;
		$first_name = false;
		$last_name = false;

		foreach ( $form['fields'] as $field ) {

			if ( $field->label == 'Which Vibrant Life Community Are You Wanting to Visit?' ) {

				$location_id = ( isset( $entry[ $field->id ] ) && $entry[ $field->id ] ) ? $entry[ $field->id ] : false;

			}

			if ( $field->label == 'Email' ) {

				$email = ( isset( $entry[ $field->id ] ) && $entry[ $field->id ] ) ? $entry[ $field->id ] : false;

			}

			if ( $field->label == 'Name' ) {

				foreach ( $field->inputs as $input ) {

					if ( $input['label'] == 'First' ) {

						$first_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

					if ( $input['label'] == 'Last' ) {

						$last_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

				}

			}

		}

		$result = SCHEDULEAVISITTOSHERPA()->api->create_lead( 250, 1, array(
			'vendorName' => get_bloginfo( 'name' ),
			'sourceCategory' => get_site_url(),
			'sourceName' => ( is_front_page() ) ? __( 'Home Page', 'schedule-a-visit-to-sherpa' ) : get_the_title(),
			'residentContactFirstName' => $first_name,
			'residentContactLastName' => $last_name,
			'primaryContactFirstName' => $first_name,
			'primaryContactLastName' => $last_name,
			'primaryContactEmail' => $email,
		) );
        
    }

}

$instance = new Schedule_a_Visit_to_Sherpa_Send();