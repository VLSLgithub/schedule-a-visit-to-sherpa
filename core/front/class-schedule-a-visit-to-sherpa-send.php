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
		
		// Bail if mapping not configured
		if ( ! $sherpa_mapping ) return false;
        
		// Bail on Forms that are not sending to Sherpa
		if ( (int) $form['id'] !== get_schedule_a_visit_to_sherpa_form() ) return false;

		$location_id = false;
		$email = false;
		$your_first_name = false;
		$your_last_name = false;
		$resident_first_name = false;
		$resident_last_name = false;

		foreach ( $form['fields'] as $field ) {

			if ( $field->label == 'Which Vibrant Life Community Are You Wanting to Visit?' ) {

				$location_short_name = ( isset( $entry[ $field->id ] ) && $entry[ $field->id ] ) ? $entry[ $field->id ] : false;

				$location = new WP_Query( array(
					'post_type' => 'facility',
					'posts_per_page' => 1,
					'fields' => 'ids',
					'meta_query' => array(
						'relationship' => 'AND',
						array(
							'key' => 'rbm_cpts_short_name',
							'value' => $location_short_name,
							'compare' => '=',
						),
					),
				) );

				if ( ! $location->have_posts() ) continue;
	
				$location_id = $location->posts[0];

			}

			if ( $field->label == 'Email' ) {

				$email = ( isset( $entry[ $field->id ] ) && $entry[ $field->id ] ) ? $entry[ $field->id ] : false;

			}

			if ( $field->label == 'Your Name' ) {

				foreach ( $field->inputs as $input ) {

					if ( $input['label'] == 'First' ) {

						$your_first_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

					if ( $input['label'] == 'Last' ) {

						$your_last_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

				}

			}

			if ( $field->label == 'Resident Name' ) {

				foreach ( $field->inputs as $input ) {

					if ( $input['label'] == 'First' ) {

						$resident_first_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

					if ( $input['label'] == 'Last' ) {

						$resident_last_name = ( isset( $entry[ $input['id'] ] ) && $entry[ $input['id'] ] ) ? $entry[ $input['id'] ] : false;

					}

				}

			}

		}

		$community_id = false;
		$company_id = apply_filters( 'schedule_a_visit_to_sherpa_company_id', 224, $location_id );

		foreach ( $sherpa_mapping as $row ) {

			if ( $row['location_id'] == $location_id ) {
				$community_id = $row['community_id'];
				break;
			}

		}

		if ( ! $community_id ) return false;

		$result = SCHEDULEAVISITTOSHERPA()->api->create_lead( $company_id, $community_id, array(
			'residentContactFirstName' => $resident_first_name,
			'residentContactLastName' => $resident_last_name,
			'primaryContactFirstName' => $your_first_name,
			'primaryContactLastName' => $your_last_name,
			'primaryContactEmail' => $email,
		) );
        
    }

}

$instance = new Schedule_a_Visit_to_Sherpa_Send();