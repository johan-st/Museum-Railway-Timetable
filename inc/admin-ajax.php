<?php
/**
 * AJAX handlers for Stop Times and Timetable management
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/admin-ajax/stoptimes.php';
require_once MRT_PATH . 'inc/admin-ajax/timetable-services.php';
require_once MRT_PATH . 'inc/admin-ajax/route-destinations.php';
require_once MRT_PATH . 'inc/admin-ajax/route-stations.php';
require_once MRT_PATH . 'inc/admin-ajax/journey-parse.php';
require_once MRT_PATH . 'inc/admin-ajax/journey-render.php';
require_once MRT_PATH . 'inc/admin-ajax/journey.php';
require_once MRT_PATH . 'inc/admin-ajax/timetable-frontend.php';

/**
 * Register AJAX actions
 */
add_action( 'wp_ajax_mrt_add_stoptime', 'MRT_ajax_add_stoptime' );
add_action( 'wp_ajax_mrt_update_stoptime', 'MRT_ajax_update_stoptime' );
add_action( 'wp_ajax_mrt_delete_stoptime', 'MRT_ajax_delete_stoptime' );
add_action( 'wp_ajax_mrt_save_all_stoptimes', 'MRT_ajax_save_all_stoptimes' );
add_action( 'wp_ajax_mrt_get_stoptime', 'MRT_ajax_get_stoptime' );
add_action( 'wp_ajax_mrt_add_service_to_timetable', 'MRT_ajax_add_service_to_timetable' );
add_action( 'wp_ajax_mrt_remove_service_from_timetable', 'MRT_ajax_remove_service_from_timetable' );
add_action( 'wp_ajax_mrt_get_route_destinations', 'MRT_ajax_get_route_destinations' );
add_action( 'wp_ajax_mrt_get_route_stations_for_stoptimes', 'MRT_ajax_get_route_stations_for_stoptimes' );
add_action( 'wp_ajax_mrt_save_route_end_stations', 'MRT_ajax_save_route_end_stations' );

add_action( 'wp_ajax_mrt_search_journey', 'MRT_ajax_search_journey' );
add_action( 'wp_ajax_nopriv_mrt_search_journey', 'MRT_ajax_search_journey' );
add_action( 'wp_ajax_mrt_journey_calendar_month', 'MRT_ajax_journey_calendar_month' );
add_action( 'wp_ajax_nopriv_mrt_journey_calendar_month', 'MRT_ajax_journey_calendar_month' );
add_action( 'wp_ajax_mrt_journey_connection_detail', 'MRT_ajax_journey_connection_detail' );
add_action( 'wp_ajax_nopriv_mrt_journey_connection_detail', 'MRT_ajax_journey_connection_detail' );
add_action( 'wp_ajax_mrt_get_timetable_for_date', 'MRT_ajax_get_timetable_for_date' );
add_action( 'wp_ajax_nopriv_mrt_get_timetable_for_date', 'MRT_ajax_get_timetable_for_date' );
