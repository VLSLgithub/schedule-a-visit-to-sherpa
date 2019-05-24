<?php
/**
 * Sherpa API Class
 *
 * @since {{VERSION}}
 *
 * @package Schedule_a_Visit_to_Sherpa
 * @subpackage Schedule_a_Visit_to_Sherpa/core/api
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Schedule_a_Visit_to_Sherpa_Abstract_API' ) ) {
	require_once Schedule_a_Visit_to_Sherpa_Plugin_DIR . 'core/api/class-schedule-a-visit-to-sherpa-abstract-api.php';
}

if ( ! class_exists( 'Schedule_a_Visit_to_Sherpa_API' ) ) {

	final class Schedule_a_Visit_to_Sherpa_API extends Schedule_a_Visit_to_Sherpa_Abstract_API {

		/**
		 * @var			string $api_endpoint Holds set API Endpoint
		 * @since		{{VERSION}}
		 */
		public $api_endpoint = 'https://sandbox.sherpacrm.com/v1';

		/**
		 * Schedule_a_Visit_to_Sherpa_API constructor.
		 * 
		 * @since		{{VERSION}}
		 */
		function __construct( $api_key ) {

			$this->set_headers( array( 
				'Content-Type: application/json',
				'Authorization: Bearer ' . trim( $api_key ),
			) );

		}
		
		/**
		 * https://documenter.getpostman.com/view/6050361/S11RLvu1?version=latest
		 *
		 * @param   int    $company_id    [$company_id description]
		 * @param   int    $community_id  [$community_id description]
		 * @param   array  $args          [$args description]
		 * @param   int    $timeout       [$timeout description]
		 *
		 * @return  [type]                [return description]
		 */
		public function create_lead( $company_id, $community_id, array $args, int $timeout = 10 ) {

			// Ensures all Required Data is at least initialized
			$args = wp_parse_args( $args, array(
				'vendorName' => '',
				'sourceCategory' => '',
				'sourceName' => '',
				'residentContactFirstName' => '',
				'residentContactLastName' => '',
				'primaryContactFirstName' => '',
				'primaryContactLastName' => '',
				'referralDateTime' => current_time( 'Y-m-d\TH:i:sP', 1 ), // If it is "Zulu Time", or UTC, the Timezone offset is always +0:00 so I don't know why including the offset is especially necessary
			) );

			$response = $this->post(
				'companies/' . $company_id . '/communities/' . $community_id . '/leads',
				$args,
				$timeout
			);

			var_dump( $response );
			die();

		}

	}
	
}