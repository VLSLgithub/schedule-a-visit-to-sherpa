<?php
/**
 * Abstract API Class that handles most of the logic
 *
 * @since {{VERSION}}
 *
 * @package Schedule_a_Visit_to_Sherpa
 * @subpackage Schedule_a_Visit_to_Sherpa/core/api
 */

defined( 'ABSPATH' ) || die();

abstract class Schedule_a_Visit_to_Sherpa_Abstract_API {
	
	/**
	 * @var			string $api_endpoint Holds set API Endpoint
	 * @since		{{VERSION}}
	 */
	public $api_endpoint = '';
	
	/**
	 * @var			array $headers The Headers sent to the API
	 * @since		{{VERSION}}
	 */
	private $headers = array();
	
	/**
	 * @var			array $default_args The default Args sent to the API
	 * @since		{{VERSION}}
	 */
	private $default_args = array();
	
	/**
	 * Schedule_a_Visit_to_Sherpa_Abstract_API constructor.
	 * 
	 * @since		{{VERSION}}
	 */
	function __construct() {
		// Extended Classes have their own Constructors
	}

	/**
	 * Make an HTTP DELETE request - for deleting data
	 * 
	 * @param		string $method  	URL of the API request method
	 * @param		array	$args		Assoc array of arguments (if any)
	 * @param		int 	$timeout	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false 		Assoc array of API response, decoded from JSON
	 */
	public function delete( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'delete', $method, $args, $timeout );
	}

	/**
	 * Make an HTTP GET request - for retrieving data
	 * 
	 * @param		string 	$method  	URL of the API request method
	 * @param		array	$args		Assoc array of arguments (if any)
	 * @param		int 	$timeout	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false 		Assoc array of API response, decoded from JSON
	 */
	public function get( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'get', $method, $args, $timeout );
	}

	/**
	 * Make an HTTP PATCH request - for performing partial updates
	 * 
	 * @param		string 	$method  	URL of the API request method
	 * @param		array	$args		Assoc array of arguments (if any)
	 * @param		int 	$timeout	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false			Assoc array of API response, decoded from JSON
	 */
	public function patch( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'patch', $method, $args, $timeout );
	}

	/**
	 * Make an HTTP POST request - for creating and updating items
	 * 
	 * @param		string 	$method  	URL of the API request method
	 * @param		array	$args		Assoc array of arguments (if any)
	 * @param		int 	$timeout	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false			Assoc array of API response, decoded from JSON
	 */
	public function post( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'post', $method, $args, $timeout );
	}

	/**
	 * Make an HTTP PUT request - for creating new items
	 * 
	 * @param		string 	$method  	URL of the API request method
	 * @param		array	$args		Assoc array of arguments (if any)
	 * @param		int 	$timeout	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false			Assoc array of API response, decoded from JSON
	 */
	public function put( $method, $args = array(), $timeout = 10 ) {
		return $this->make_request( 'put', $method, $args, $timeout );
	}

	/**
	 * Performs the underlying HTTP request
	 * 
	 * @param		string 	$http_verb  The HTTP verb to use: get, post, put, patch, delete
	 * @param		string	$method		The API method to be called
	 * @param		array 	$args		Assoc array of parameters to be passed
	 * @param		integer $timeout 	Timeout limit for request in seconds
	 *																
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array|false 		Assoc array of API response, decoded from JSON
	 */
	private function make_request( $http_verb, $method, $args = array(), $timeout = 10 ) {
		
		$url = $this->api_endpoint . '/' . $method;
		
		$ch = curl_init();
		
		curl_setopt( $ch, CURLOPT_FRESH_CONNECT, true );
        curl_setopt( $ch, CURLOPT_FORBID_REUSE, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		
		if ( $http_verb !== 'get' ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, strtoupper( $http_verb ) );
        }
        
        // Pull in default Args
        $args = wp_parse_args( $args, $this->default_args );
		
		if ( ! empty( $args ) ) {

			if ( ( isset( $args['__req'] ) && strtolower( $args['__req'] ) == 'get' ) || 
				$http_verb == 'get' ) {
				
                unset( $args['__req'] );
                $url .= '?' . http_build_query( $args );
				
            }
			elseif ( $http_verb == 'post' || 
					$http_verb == 'delete' ) {
				
                $params_str = is_array( $args ) ? json_encode( $args ) : $args;
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $params_str );
				
            }
			
        }
		
		curl_setopt( $ch, CURLOPT_URL, $url);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->headers );
		
		$buffer = curl_exec( $ch );

		return json_decode( $buffer );
		
	}
	
	/**
	 * Return the API Endpoint
	 * 
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		string API Endpoint
	 */
	public function get_api_endpoint() {
		return $this->api_endpoint;
	}
	
	/**
	 * Sets the Private $header Member
	 * 
	 * @param		array $headers New Header Values
	 *								   
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		void
	 */
	public function set_headers( $headers ) {
		
		$this->headers = $headers;
		
	}
	
	/**
	 * Sets the Private $default_args Member
	 * 
	 * @param		array $default_args Default Args
	 *								   
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		void
	 */
	public function set_default_args( $default_args ) {
		
		$this->default_args = $default_args;
		
	}

}