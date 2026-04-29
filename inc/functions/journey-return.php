<?php
/**
 * Return-trip connection helpers
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * First departure (HH:MM) at journey origin from a raw multi-leg bundle
 *
 * @param array<string, mixed> $item Row from MRT_find_multi_leg_connections
 * @return string HH:MM or empty
 */
function MRT_journey_raw_item_first_departure( array $item ) {
	if ( empty( $item['legs'][0] ) || ! is_array( $item['legs'][0] ) ) {
		return '';
	}
	$leg = $item['legs'][0];
	$dep = $leg['from_departure'] ?? '';
	if ( $dep !== '' ) {
		return (string) $dep;
	}
	return (string) ( $leg['from_arrival'] ?? '' );
}

/**
 * Whether return search has valid stations, date, and outbound arrival time
 *
 * @param int    $from_station_id Outbound origin
 * @param int    $to_station_id   Outbound destination
 * @param string $dateYmd         Date YYYY-MM-DD
 * @param string $outbound_arrival_hhmm Arrival at outbound destination
 */
function MRT_return_journey_inputs_valid( $from_station_id, $to_station_id, $dateYmd, $outbound_arrival_hhmm ): bool {
	if ( $from_station_id <= 0 || $to_station_id <= 0 ) {
		return false;
	}
	if ( ! MRT_validate_date( $dateYmd ) || MRT_time_hhmm_to_minutes( $outbound_arrival_hhmm ) === null ) {
		return false;
	}
	return true;
}

/**
 * Normalize raw multi-leg rows that depart on or after earliest allowed (turnaround)
 *
 * @param array<int, array<string, mixed>> $raw From MRT_find_multi_leg_connections
 * @param string                           $dateYmd Date
 * @param int                              $return_origin_id Station ID “from” on return (outbound destination)
 * @param int                              $return_dest_id Station ID “to” on return (outbound origin)
 * @param string                           $earliest_hhmm First allowed departure (HH:MM)
 * @return array<int, array<string, mixed>>
 */
function MRT_return_journey_normalized_after_turnaround(
	array $raw,
	string $dateYmd,
	int $return_origin_id,
	int $return_dest_id,
	string $earliest_hhmm
): array {
	$out = array();
	foreach ( $raw as $item ) {
		$dep = MRT_journey_raw_item_first_departure( $item );
		if ( $dep === '' || ! MRT_validate_time_hhmm( $dep ) ) {
			continue;
		}
		if ( MRT_compare_hhmm( $dep, $earliest_hhmm ) < 0 ) {
			continue;
		}
		$out[] = MRT_normalize_connection_for_api(
			$item,
			$dateYmd,
			$return_origin_id,
			$return_dest_id
		);
	}
	return $out;
}

/**
 * Return journeys (to → from) after outbound arrival + turnaround — direct and one transfer
 *
 * @param int    $from_station_id Outbound origin (becomes return destination)
 * @param int    $to_station_id Outbound destination (return origin)
 * @param string $dateYmd Date YYYY-MM-DD
 * @param string $outbound_arrival_hhmm Arrival at to_station on outbound
 * @param int    $min_turnaround_minutes Minimum minutes before return may depart
 * @return array<int, array<string, mixed>> Normalized API payloads (MRT_normalize_connection_for_api)
 */
function MRT_find_return_connections(
	$from_station_id,
	$to_station_id,
	$dateYmd,
	$outbound_arrival_hhmm,
	$min_turnaround_minutes = 0
) {
	if ( ! MRT_return_journey_inputs_valid( $from_station_id, $to_station_id, $dateYmd, $outbound_arrival_hhmm ) ) {
		return array();
	}
	$min_xfer = (int) apply_filters( 'mrt_min_transfer_minutes', 5 );
	$raw      = MRT_find_multi_leg_connections(
		(int) $to_station_id,
		(int) $from_station_id,
		$dateYmd,
		$min_xfer,
		true
	);
	if ( $raw === array() ) {
		return array();
	}
	$earliest = MRT_add_minutes_to_hhmm( $outbound_arrival_hhmm, (int) $min_turnaround_minutes );
	if ( $earliest === null ) {
		return array();
	}
	return MRT_return_journey_normalized_after_turnaround(
		$raw,
		$dateYmd,
		(int) $to_station_id,
		(int) $from_station_id,
		$earliest
	);
}
