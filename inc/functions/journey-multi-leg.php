<?php
/**
 * Multi-leg journey search (one transfer, two services)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Build one leg object for API (segment on one service)
 *
 * @param int    $service_id Service ID
 * @param int    $from_station_id From station
 * @param int    $to_station_id To station
 * @param string $dateYmd Date for train type
 * @return array<string, mixed>|null
 */
function MRT_journey_build_leg_segment( $service_id, $from_station_id, $to_station_id, $dateYmd ) {
	$detail = MRT_get_connection_journey_detail( $service_id, $from_station_id, $to_station_id );
	if ( empty( $detail['stops'] ) ) {
		return null;
	}
	$stops = $detail['stops'];
	$first = $stops[0];
	$last  = $stops[ count( $stops ) - 1 ];
	$dep   = $first['departure_time'] ?: $first['arrival_time'];
	$arr   = $last['arrival_time'] ?: $last['departure_time'];
	$tt    = MRT_get_service_train_type_for_date( $service_id, $dateYmd );
	$num   = get_post_meta( $service_id, 'mrt_service_number', true );
	return array(
		'service_id'      => (int) $service_id,
		'from_station_id' => (int) $from_station_id,
		'to_station_id'   => (int) $to_station_id,
		'from_departure'  => $dep,
		'to_arrival'      => $arr,
		'train_type'      => $tt ? $tt->name : '',
		'train_type_slug' => $tt ? $tt->slug : '',
		'service_number'  => $num !== '' && $num !== null ? (string) $num : (string) $service_id,
	);
}

/**
 * Build leg from flat connection row (same endpoints as search)
 *
 * @param array<string, mixed> $conn Connection row
 * @param string               $dateYmd Date
 * @param int                  $from_station_id From
 * @param int                  $to_station_id To
 * @return array<string, mixed>
 */
function MRT_journey_leg_from_connection_row( array $conn, $dateYmd, $from_station_id, $to_station_id ) {
	$sid = intval( $conn['service_id'] );
	$tt  = MRT_get_service_train_type_for_date( $sid, $dateYmd );
	$num = get_post_meta( $sid, 'mrt_service_number', true );
	return array(
		'service_id'      => $sid,
		'from_station_id' => (int) $from_station_id,
		'to_station_id'   => (int) $to_station_id,
		'from_departure'  => $conn['from_departure'] ? (string) $conn['from_departure'] : (string) ( $conn['from_arrival'] ?? '' ),
		'to_arrival'      => $conn['to_arrival'] ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' ),
		'train_type'      => $tt ? $tt->name : (string) ( $conn['train_type'] ?? '' ),
		'train_type_slug' => $tt ? $tt->slug : '',
		'service_number'  => $num !== '' && $num !== null ? (string) $num : (string) $sid,
	);
}

/**
 * Wrap direct connection as multi-leg shape (one leg)
 *
 * @param array<string, mixed> $conn Connection row
 * @param string               $dateYmd Date
 * @param int                  $from_station_id From
 * @param int                  $to_station_id To
 * @return array<string, mixed>
 */
function MRT_journey_wrap_direct_multi( array $conn, $dateYmd, $from_station_id, $to_station_id ) {
	$sid = intval( $conn['service_id'] );
	$leg = MRT_journey_build_leg_segment( $sid, $from_station_id, $to_station_id, $dateYmd );
	if ( $leg === null ) {
		$leg = MRT_journey_leg_from_connection_row( $conn, $dateYmd, $from_station_id, $to_station_id );
	}
	return array(
		'connection_type'     => 'direct',
		'transfer_station_id' => null,
		'legs'                => array( $leg ),
	);
}

/**
 * Append transfer options (two legs) to results list
 *
 * @param array<int, mixed>   $results Out results (by ref)
 * @param array<string, bool> $seen Keys (by ref)
 * @param int                 $from_station_id Origin A
 * @param int                 $to_station_id Destination B
 * @param string              $dateYmd Date
 * @param int                 $min_transfer_minutes Min transfer time
 */
function MRT_journey_append_transfer_options( array &$results, array &$seen, $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes ) {
	$service_ids = MRT_services_running_on_date( $dateYmd );
	foreach ( $service_ids as $s1 ) {
		$ordered  = MRT_get_service_stop_times_ordered( (int) $s1 );
		$from_idx = MRT_journey_find_stop_index( $ordered, $from_station_id );
		if ( $from_idx === null ) {
			continue;
		}
		$n = count( $ordered );
		for ( $k = $from_idx + 1; $k < $n; $k++ ) {
			$xfer_id = intval( $ordered[ $k ]['station_post_id'] );
			if ( $xfer_id === (int) $to_station_id ) {
				break;
			}
			$xfer_arr = MRT_stop_effective_arrival( $ordered[ $k ] );
			if ( $xfer_arr === '' || ! MRT_validate_time_hhmm( $xfer_arr ) ) {
				continue;
			}
			$earliest = MRT_add_minutes_to_hhmm( $xfer_arr, (int) $min_transfer_minutes );
			if ( $earliest === null ) {
				continue;
			}
			foreach ( MRT_find_connections_departing_not_before( $xfer_id, $to_station_id, $dateYmd, $earliest ) as $c2 ) {
				if ( intval( $c2['service_id'] ) === (int) $s1 ) {
					continue;
				}
				$key = (int) $s1 . '-' . $xfer_id . '-' . intval( $c2['service_id'] ) . '-' . $c2['from_departure'];
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;
				$leg1         = MRT_journey_build_leg_segment( (int) $s1, $from_station_id, $xfer_id, $dateYmd );
				$leg2         = MRT_journey_leg_from_connection_row( $c2, $dateYmd, $xfer_id, $to_station_id );
				if ( $leg1 === null ) {
					continue;
				}
				$results[] = array(
					'connection_type'     => 'transfer',
					'transfer_station_id' => $xfer_id,
					'legs'                => array( $leg1, $leg2 ),
				);
			}
		}
	}
}

/**
 * Direct and optional two-leg connections (same day, one transfer max)
 *
 * @param int    $from_station_id From
 * @param int    $to_station_id To
 * @param string $dateYmd Date
 * @param int    $min_transfer_minutes Minimum minutes between arrival and next departure
 * @param bool   $include_direct Include single-service connections
 * @return array<int, array<string, mixed>>
 */
function MRT_find_multi_leg_connections( $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes = 5, $include_direct = true ) {
	$results = array();
	if ( $from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id ) {
		return $results;
	}
	if ( ! MRT_validate_date( $dateYmd ) ) {
		return $results;
	}
	if ( $include_direct ) {
		foreach ( MRT_find_connections( $from_station_id, $to_station_id, $dateYmd ) as $conn ) {
			$results[] = MRT_journey_wrap_direct_multi( $conn, $dateYmd, $from_station_id, $to_station_id );
		}
	}
	$seen = array();
	MRT_journey_append_transfer_options( $results, $seen, $from_station_id, $to_station_id, $dateYmd, $min_transfer_minutes );
	return $results;
}
