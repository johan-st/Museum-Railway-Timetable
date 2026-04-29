<?php
/**
 * Shared POST parsing for journey AJAX (nonce, stations, date, trip type)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Validate two station IDs for a journey (no POST)
 *
 * @param int $from_station_id From station post ID
 * @param int $to_station_id To station post ID
 * @return WP_Error|null Null when valid
 */
function MRT_journey_validate_station_pair_ids( $from_station_id, $to_station_id ) {
	$from = (int) $from_station_id;
	$to   = (int) $to_station_id;
	if ( $from <= 0 || $to <= 0 ) {
		return new WP_Error(
			'mrt_journey_stations',
			__( 'Please select both departure and arrival stations.', 'museum-railway-timetable' )
		);
	}
	if ( $from === $to ) {
		return new WP_Error(
			'mrt_journey_same',
			__( 'Please select different stations for departure and arrival.', 'museum-railway-timetable' )
		);
	}

	return null;
}

/**
 * Verify mrt_frontend nonce from POST
 *
 * @return bool
 */
function MRT_journey_ajax_verify_nonce() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	return (bool) wp_verify_nonce( $nonce, 'mrt_frontend' );
}

/**
 * Parse from_station and to_station from POST (no date)
 *
 * @return array<string, int>|WP_Error Keys from, to or error
 */
function MRT_journey_ajax_parse_stations_pair() {
	$from = intval( $_POST['from_station'] ?? 0 );
	$to   = intval( $_POST['to_station'] ?? 0 );
	$err  = MRT_journey_validate_station_pair_ids( $from, $to );
	if ( $err !== null ) {
		return $err;
	}

	return array(
		'from' => $from,
		'to'   => $to,
	);
}

/**
 * Parse from_station, to_station, date from POST
 *
 * @return array<string, mixed>|WP_Error Keys from, to, date or error
 */
function MRT_journey_ajax_parse_from_to_date() {
	$pair = MRT_journey_ajax_parse_stations_pair();
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	if ( $date === '' || ! MRT_validate_date( $date ) ) {
		return new WP_Error(
			'mrt_journey_date',
			__( 'Please select a valid date.', 'museum-railway-timetable' )
		);
	}
	return array_merge( $pair, array( 'date' => $date ) );
}

/**
 * Parse journey search POST: stations, date, trip_type, return extras
 *
 * @return array<string, mixed>|WP_Error
 */
function MRT_journey_ajax_parse_trip_search_params() {
	$base = MRT_journey_ajax_parse_from_to_date();
	if ( is_wp_error( $base ) ) {
		return $base;
	}
	$trip_raw          = isset( $_POST['trip_type'] ) ? sanitize_text_field( wp_unslash( $_POST['trip_type'] ) ) : 'single';
	$trip_type         = ( $trip_raw === 'return' ) ? 'return' : 'single';
	$base['trip_type'] = $trip_type;
	if ( $trip_type !== 'return' ) {
		return $base;
	}
	$arrival = isset( $_POST['outbound_arrival'] ) ? sanitize_text_field( wp_unslash( $_POST['outbound_arrival'] ) ) : '';
	if ( $arrival === '' || ! MRT_validate_time_hhmm( $arrival ) ) {
		return new WP_Error(
			'mrt_journey_return_arrival',
			__( 'Please provide a valid outbound arrival time for return search.', 'museum-railway-timetable' )
		);
	}
	$base['outbound_arrival']       = $arrival;
	$base['outbound_service_id']    = isset( $_POST['outbound_service_id'] ) ? intval( $_POST['outbound_service_id'] ) : 0;
	$base['min_turnaround_minutes'] = isset( $_POST['min_turnaround_minutes'] )
		? max( 0, intval( $_POST['min_turnaround_minutes'] ) )
		: 0;
	return $base;
}

/**
 * Parse calendar month request: stations + year + month
 *
 * @return array<string, int>|WP_Error
 */
function MRT_journey_ajax_parse_calendar_month_params() {
	$pair = MRT_journey_ajax_parse_stations_pair();
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$year  = intval( $_POST['year'] ?? 0 );
	$month = intval( $_POST['month'] ?? 0 );
	if ( $year < 1970 || $year > 2100 || $month < 1 || $month > 12 ) {
		return new WP_Error(
			'mrt_calendar_month_range',
			__( 'Please select a valid month.', 'museum-railway-timetable' )
		);
	}
	return array_merge(
		$pair,
		array(
			'year'  => $year,
			'month' => $month,
		)
	);
}

/**
 * Parse connection detail POST: stations + service_id
 *
 * @return array<string, int>|WP_Error
 */
function MRT_journey_ajax_parse_connection_detail_params() {
	$pair = MRT_journey_ajax_parse_stations_pair();
	if ( is_wp_error( $pair ) ) {
		return $pair;
	}
	$service_id = intval( $_POST['service_id'] ?? 0 );
	if ( $service_id <= 0 ) {
		return new WP_Error(
			'mrt_journey_service',
			__( 'Invalid service.', 'museum-railway-timetable' )
		);
	}
	return array_merge( $pair, array( 'service_id' => $service_id ) );
}
