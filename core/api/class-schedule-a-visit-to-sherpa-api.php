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

			$this->set_default_args( array( 
				'key' => trim( $api_key ), // API Key gets passed in as a POST Param
			) );

        }
        
        public function test() {

            $test = $this->get(
                'companies/250',
                array(),
                10
            );

            var_dump( $test );die();

        }

	}
	
}