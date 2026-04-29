<?php
/**
 * Connection helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Find connecting services at a station after a service arrives
 * Used for transfer information display
 *
 * @param int    $station_id Station post ID
 * @param int    $arriving_service_id Service that arrives at the station
 * @param string $arrival_time Arrival time in HH:MM format
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param int    $limit Maximum number of connecting services to return
 * @return array Array of connecting service data
 */
function MRT_find_connecting_services( $station_id, $arriving_service_id, $arrival_time, $dateYmd, $limit = 3 ) {
	if ( ! $station_id || ! $arrival_time || ! $dateYmd ) {
		return array();
	}

	// Get all services running on this date
	$service_ids = MRT_services_running_on_date( $dateYmd );
	if ( empty( $service_ids ) ) {
		return array();
	}

	// Remove the arriving service from the list
	$service_ids = array_filter(
		$service_ids,
		function ( $id ) use ( $arriving_service_id ) {
			return $id != $arriving_service_id;
		}
	);

	if ( empty( $service_ids ) ) {
		return array();
	}

	// Use existing function to find next departures
	$connections = MRT_next_departures_for_station( $station_id, array_values( $service_ids ), $arrival_time, $limit, false );

	// Enrich with service numbers
	foreach ( $connections as &$conn ) {
		$service_number = get_post_meta( $conn['service_id'], 'mrt_service_number', true );
		if ( empty( $service_number ) ) {
			$service_number = $conn['service_id'];
		}
		$conn['service_number'] = $service_number;
	}

	return $connections;
}
