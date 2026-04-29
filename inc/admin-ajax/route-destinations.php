<?php
/**
 * AJAX handler for route destinations
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Accept either timetable services or service meta nonce (add-trip UI).
 */
function MRT_route_destinations_nonce_valid( string $nonce ): bool {
	return wp_verify_nonce( $nonce, 'mrt_timetable_services_nonce' )
		|| wp_verify_nonce( $nonce, 'mrt_save_service_meta' );
}

/**
 * @return array<int, array{id: int, name: string}>
 */
function MRT_build_route_destinations_list( int $route_id ): array {
	$end_stations   = MRT_get_route_end_stations( $route_id );
	$route_stations = get_post_meta( $route_id, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		$route_stations = array();
	}

	$destinations = array();

	if ( $end_stations['start'] > 0 ) {
		$start_station = get_post( $end_stations['start'] );
		if ( $start_station ) {
			$destinations[] = array(
				'id'   => $end_stations['start'],
				'name' => $start_station->post_title . ' (' . __( 'Start', 'museum-railway-timetable' ) . ')',
			);
		}
	}
	if ( $end_stations['end'] > 0 ) {
		$end_station = get_post( $end_stations['end'] );
		if ( $end_station ) {
			$destinations[] = array(
				'id'   => $end_stations['end'],
				'name' => $end_station->post_title . ' (' . __( 'End', 'museum-railway-timetable' ) . ')',
			);
		}
	}

	foreach ( $route_stations as $station_id ) {
		if ( $station_id == $end_stations['start'] || $station_id == $end_stations['end'] ) {
			continue;
		}
		$station = get_post( $station_id );
		if ( $station ) {
			$destinations[] = array(
				'id'   => $station_id,
				'name' => $station->post_title,
			);
		}
	}

	return $destinations;
}

function MRT_ajax_get_route_destinations() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( ! MRT_route_destinations_nonce_valid( $nonce ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'museum-railway-timetable' ) ) );
	}
	$route_id = (int) ( $_POST['route_id'] ?? 0 );

	if ( $route_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid route.', 'museum-railway-timetable' ) ) );
	}
	MRT_verify_ajax_edit_post_permission( $route_id );

	wp_send_json_success( array( 'destinations' => MRT_build_route_destinations_list( $route_id ) ) );
}
