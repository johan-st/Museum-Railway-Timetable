<?php
/**
 * Journey segment detail for a single service (public / API)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Find first index in ordered stoptimes matching station
 *
 * @param array<int, array<string, mixed>> $ordered Ordered stoptimes
 * @param int                              $station_id Station post ID
 * @return int|null Index or null
 */
function MRT_journey_find_stop_index( array $ordered, $station_id ) {
	foreach ( $ordered as $i => $row ) {
		if ( intval( $row['station_post_id'] ) === (int) $station_id ) {
			return (int) $i;
		}
	}
	return null;
}

/**
 * Map stoptime row to public journey stop shape
 *
 * @param array<string, mixed> $row DB row
 * @return array<string, mixed>
 */
function MRT_journey_map_stop_row( array $row ) {
	$sid   = intval( $row['station_post_id'] );
	$title = get_the_title( $sid ) ?: '';
	return array(
		'station_id'      => $sid,
		'station_title'   => $title,
		'stop_sequence'   => intval( $row['stop_sequence'] ),
		'arrival_time'    => $row['arrival_time'] ? (string) $row['arrival_time'] : '',
		'departure_time'  => $row['departure_time'] ? (string) $row['departure_time'] : '',
		'pickup_allowed'  => ! empty( $row['pickup_allowed'] ),
		'dropoff_allowed' => ! empty( $row['dropoff_allowed'] ),
	);
}

/**
 * Journey detail between two stations on one service (ordered stops, duration)
 *
 * @param int $service_id Service post ID
 * @param int $from_station_id Boarding station
 * @param int $to_station_id Alighting station
 * @return array<string, mixed> stops, duration_minutes, service_id (empty if invalid)
 */
function MRT_get_connection_journey_detail( $service_id, $from_station_id, $to_station_id ) {
	$out = array(
		'service_id'       => (int) $service_id,
		'stops'            => array(),
		'duration_minutes' => null,
	);
	if ( $service_id <= 0 || $from_station_id <= 0 || $to_station_id <= 0 ) {
		return $out;
	}
	if ( $from_station_id === $to_station_id ) {
		return $out;
	}
	$ordered = MRT_get_service_stop_times_ordered( $service_id );
	if ( empty( $ordered ) ) {
		return $out;
	}
	$from_i = MRT_journey_find_stop_index( $ordered, $from_station_id );
	$to_i   = MRT_journey_find_stop_index( $ordered, $to_station_id );
	if ( $from_i === null || $to_i === null || $from_i >= $to_i ) {
		return $out;
	}
	$slice = array_slice( $ordered, $from_i, $to_i - $from_i + 1 );
	foreach ( $slice as $row ) {
		$out['stops'][] = MRT_journey_map_stop_row( $row );
	}
	$first                   = $slice[0];
	$last                    = $slice[ count( $slice ) - 1 ];
	$dep                     = MRT_stop_effective_departure( $first );
	$arr                     = MRT_stop_effective_arrival( $last );
	$out['duration_minutes'] = MRT_format_duration_minutes( $dep, $arr );
	return $out;
}
