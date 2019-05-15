<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	Schedule_a_Visit_to_Sherpa_Plugin
 * @subpackage Schedule_a_Visit_to_Sherpa_Plugin/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		Schedule_a_Visit_to_Sherpa_Plugin
 */
function SCHEDULEAVISITTOSHERPA() {
	return Schedule_a_Visit_to_Sherpa_Plugin::instance();
}

/**
 * The the Gravity Form ID to use for Sherpa interactions (The Schedule a Visit form)
 * If it has been set in the Customizer by the Theme for other purposes, it will pull from there
 *
 * @since	1.0.0
 * @return  integer  Gravity Forms ID
 */
function get_schedule_a_visit_to_sherpa_form() {

	if ( $form = get_theme_mod( 'vibrant_life_schedule_a_visit_form' ) ) {
		return (int) $form;
	}

	return (int) get_option( 'schedule_a_visit_to_sherpa_form' );

}