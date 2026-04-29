<?php
/**
 * AJAX handlers for route stations (Stop Times table, end stations)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get route stations for Stop Times table via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
/**
 * @return array<int, array<string, mixed>>
 */
function MRT_map_existing_stoptimes_by_station( int $service_id ): array {
	if ( $service_id <= 0 ) {
		return array();
	}
	global $wpdb;
	$stoptimes_table    = $wpdb->prefix . 'mrt_stoptimes';
	$stoptimes          = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $stoptimes_table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
			$service_id
		),
		ARRAY_A
	);
	$existing_stoptimes = array();
	foreach ( $stoptimes as $st ) {
		$existing_stoptimes[ $st['station_post_id'] ] = $st;
	}
	return $existing_stoptimes;
}

/**
 * @param array<int>                       $route_stations
 * @param array<int, array<string, mixed>> $existing_stoptimes
 * @return array<int, array<string, mixed>>
 */
function MRT_build_stoptimes_station_rows( array $route_stations, array $existing_stoptimes ): array {
	if ( $route_stations === array() ) {
		return array();
	}
	$station_posts = get_posts(
		array(
			'post_type'      => 'mrt_station',
			'post__in'       => $route_stations,
			'posts_per_page' => -1,
			'orderby'        => 'post__in',
			'fields'         => 'all',
		)
	);

	$stations = array();
	foreach ( $station_posts as $index => $station ) {
		$st         = $existing_stoptimes[ $station->ID ] ?? null;
		$stops_here = $st !== null;
		$sequence   = $st ? $st['stop_sequence'] : ( $index + 1 );

		$stations[] = array(
			'id'              => $station->ID,
			'name'            => $station->post_title,
			'sequence'        => $sequence,
			'stops_here'      => $stops_here,
			'arrival_time'    => $st ? $st['arrival_time'] : '',
			'departure_time'  => $st ? $st['departure_time'] : '',
			'pickup_allowed'  => $st ? ! empty( $st['pickup_allowed'] ) : true,
			'dropoff_allowed' => $st ? ! empty( $st['dropoff_allowed'] ) : true,
		);
	}
	return $stations;
}

function MRT_ajax_get_route_stations_for_stoptimes() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );

	$route_id   = (int) ( $_POST['route_id'] ?? 0 );
	$service_id = (int) ( $_POST['service_id'] ?? 0 );

	if ( $route_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid route.', 'museum-railway-timetable' ) ) );
	}
	MRT_verify_ajax_edit_post_permission( $route_id );
	if ( $service_id > 0 ) {
		MRT_verify_ajax_edit_post_permission( $service_id );
	}

	$route_stations = get_post_meta( $route_id, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		$route_stations = array();
	}

	$existing = MRT_map_existing_stoptimes_by_station( $service_id );
	$stations = MRT_build_stoptimes_station_rows( $route_stations, $existing );

	wp_send_json_success(
		array(
			'stations'     => $stations,
			'has_stations' => $stations !== array(),
		)
	);
}

/**
 * Save route end stations via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_save_route_end_stations() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mrt_save_route_meta' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'museum-railway-timetable' ) ) );
	}
	$route_id      = intval( $_POST['route_id'] ?? 0 );
	$start_station = intval( $_POST['start_station'] ?? 0 );
	$end_station   = intval( $_POST['end_station'] ?? 0 );

	if ( $route_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid route.', 'museum-railway-timetable' ) ) );
	}
	MRT_verify_ajax_edit_post_permission( $route_id );

	if ( $start_station > 0 ) {
		update_post_meta( $route_id, 'mrt_route_start_station', $start_station );
	} else {
		delete_post_meta( $route_id, 'mrt_route_start_station' );
	}

	if ( $end_station > 0 ) {
		update_post_meta( $route_id, 'mrt_route_end_station', $end_station );
	} else {
		delete_post_meta( $route_id, 'mrt_route_end_station' );
	}

	$start_station_name = '';
	$end_station_name   = '';
	if ( $start_station > 0 ) {
		$start_post = get_post( $start_station );
		if ( $start_post ) {
			$start_station_name = $start_post->post_title;
		}
	}
	if ( $end_station > 0 ) {
		$end_post = get_post( $end_station );
		if ( $end_post ) {
			$end_station_name = $end_post->post_title;
		}
	}

	wp_send_json_success(
		array(
			'message'            => __( 'End stations saved successfully.', 'museum-railway-timetable' ),
			'start_station_name' => $start_station_name,
			'end_station_name'   => $end_station_name,
		)
	);
}
