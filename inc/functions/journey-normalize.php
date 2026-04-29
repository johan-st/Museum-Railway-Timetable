<?php
/**
 * Normalize journey results for JSON API / frontends
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Sum leg durations; null if any segment missing times
 *
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return int|null
 */
function MRT_normalize_total_duration_from_legs( array $legs ) {
	$total = 0;
	foreach ( $legs as $leg ) {
		$dep = $leg['from_departure'] ?? '';
		$arr = $leg['to_arrival'] ?? '';
		$m   = MRT_format_duration_minutes( $dep, $arr );
		if ( $m === null ) {
			return null;
		}
		$total += $m;
	}
	return $total;
}

/**
 * Short train-type label for multi-leg (one value if all same, else "a / b")
 *
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return string
 */
function MRT_journey_multi_leg_train_type_label( array $legs ) {
	$tts = array();
	foreach ( $legs as $leg ) {
		$t = (string) ( $leg['train_type'] ?? '' );
		if ( $t !== '' ) {
			$tts[] = $t;
		}
	}
	$tts = array_values( array_unique( $tts ) );
	if ( count( $tts ) <= 1 ) {
		return (string) ( $tts[0] ?? '' );
	}
	return implode( ' / ', $tts );
}

/**
 * Human-readable label for a transfer journey (two services)
 *
 * @param array<string, mixed>             $item Multi-leg bundle
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return string
 */
function MRT_journey_multi_leg_service_label( array $item, array $legs ) {
	$transfer_id = (int) ( $item['transfer_station_id'] ?? 0 );
	$hub         = $transfer_id > 0 ? get_the_title( $transfer_id ) : '';
	$titles      = array();
	foreach ( $legs as $leg ) {
		$sid = (int) ( $leg['service_id'] ?? 0 );
		if ( $sid > 0 ) {
			$titles[] = get_the_title( $sid ) ?: ( '#' . $sid );
		}
	}
	if ( $hub !== '' && isset( $titles[0], $titles[1] ) ) {
		return sprintf(
			/* translators: 1: first service name, 2: transfer station name, 3: second service name */
			__( '%1$s · Change at %2$s · %3$s', 'museum-railway-timetable' ),
			$titles[0],
			$hub,
			$titles[1]
		);
	}
	return implode( ' · ', $titles );
}

/**
 * Map normalized API connection to legacy table row (HTML planner / shortcode table)
 *
 * @param array<string, mixed> $n Output from MRT_normalize_connection_for_api
 * @return array<string, mixed>
 */
function MRT_journey_normalized_to_planner_row( array $n ) {
	if ( ( $n['connection_type'] ?? '' ) === 'transfer' && ! empty( $n['legs'] ) && is_array( $n['legs'] ) ) {
		$legs     = $n['legs'];
		$last_sid = (int) ( $legs[ count( $legs ) - 1 ]['service_id'] ?? 0 );
		$dest     = $last_sid > 0 ? MRT_get_service_destination( $last_sid ) : array(
			'destination' => '',
			'direction'   => '',
		);
		return array(
			'service_id'     => (int) ( $n['service_id'] ?? 0 ),
			'service_name'   => (string) ( $n['service_name'] ?? '' ),
			'route_name'     => '',
			'train_type'     => (string) ( $n['train_type'] ?? '' ),
			'from_departure' => (string) ( $n['from_departure'] ?? $n['departure'] ?? '' ),
			'from_arrival'   => '',
			'to_arrival'     => (string) ( $n['to_arrival'] ?? $n['arrival'] ?? '' ),
			'to_departure'   => '',
			'destination'    => (string) ( $dest['destination'] ?? '' ),
			'direction'      => (string) ( $dest['direction'] ?? '' ),
		);
	}
	return array(
		'service_id'     => (int) ( $n['service_id'] ?? 0 ),
		'service_name'   => (string) ( $n['service_name'] ?? '' ),
		'route_name'     => (string) ( $n['route_name'] ?? '' ),
		'train_type'     => (string) ( $n['train_type'] ?? '' ),
		'from_departure' => (string) ( $n['from_departure'] ?? $n['departure'] ?? '' ),
		'from_arrival'   => '',
		'to_arrival'     => (string) ( $n['to_arrival'] ?? $n['arrival'] ?? '' ),
		'to_departure'   => '',
		'destination'    => (string) ( $n['destination'] ?? '' ),
		'direction'      => (string) ( $n['direction'] ?? '' ),
	);
}

/**
 * Build segments / notice for one service connection
 *
 * @param int    $service_id Service
 * @param int    $from_id From station
 * @param int    $to_id To station
 * @param string $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_segments_single_service( $service_id, $from_id, $to_id, $dateYmd ) {
	$detail = MRT_get_connection_journey_detail( $service_id, $from_id, $to_id );
	return array(
		'segments'         => $detail['stops'],
		'duration_minutes' => $detail['duration_minutes'],
		'notice'           => MRT_get_service_notice( $service_id, $dateYmd ),
	);
}

/**
 * One-leg wrapped direct → flat connection row for normalizer
 *
 * @param array<string, mixed> $item Wrapped direct multi
 * @return array<string, mixed>|null
 */
function MRT_flatten_wrapped_direct_connection( array $item ) {
	if ( ( $item['connection_type'] ?? '' ) !== 'direct' || empty( $item['legs'][0] ) ) {
		return null;
	}
	$leg = $item['legs'][0];
	$sid = (int) ( $leg['service_id'] ?? 0 );
	if ( $sid <= 0 ) {
		return null;
	}
	$route_id = get_post_meta( $sid, 'mrt_service_route_id', true );
	$dest     = MRT_get_service_destination( $sid );
	return array(
		'service_id'     => $sid,
		'service_name'   => get_the_title( $sid ) ?: ( '#' . $sid ),
		'route_name'     => $route_id ? get_the_title( (int) $route_id ) : '',
		'destination'    => $dest['destination'],
		'direction'      => $dest['direction'],
		'train_type'     => (string) ( $leg['train_type'] ?? '' ),
		'from_departure' => (string) ( $leg['from_departure'] ?? '' ),
		'from_arrival'   => '',
		'to_arrival'     => (string) ( $leg['to_arrival'] ?? '' ),
		'to_departure'   => '',
		'from_sequence'  => 0,
		'to_sequence'    => 0,
	);
}

/**
 * Normalize multi-leg bundle for API
 *
 * @param array<string, mixed> $item Must contain legs[]
 * @param string               $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_multi_leg_for_api( array $item, $dateYmd ) {
	$legs     = $item['legs'];
	$duration = MRT_normalize_total_duration_from_legs( $legs );
	$notices  = array();
	foreach ( $legs as $leg ) {
		$nid = isset( $leg['service_id'] ) ? (int) $leg['service_id'] : 0;
		if ( $nid <= 0 ) {
			continue;
		}
		$n = MRT_get_service_notice( $nid, $dateYmd );
		if ( $n !== '' ) {
			$notices[] = $n;
		}
	}
	$last      = count( $legs ) - 1;
	$dep_first = (string) ( $legs[0]['from_departure'] ?? '' );
	$arr_last  = (string) ( $legs[ $last ]['to_arrival'] ?? '' );
	return array(
		'connection_type'     => $item['connection_type'] ?? 'transfer',
		'transfer_station_id' => $item['transfer_station_id'] ?? null,
		'legs'                => $legs,
		'duration_minutes'    => $duration,
		'segments'            => array(),
		'notice'              => implode( "\n", array_unique( $notices ) ),
		'service_id'          => isset( $legs[0]['service_id'] ) ? (int) $legs[0]['service_id'] : 0,
		'departure'           => $dep_first,
		'arrival'             => $arr_last,
		'from_departure'      => $dep_first,
		'to_arrival'          => $arr_last,
		'service_name'        => MRT_journey_multi_leg_service_label( $item, $legs ),
		'train_type'          => MRT_journey_multi_leg_train_type_label( $legs ),
	);
}

/**
 * Unified connection payload (direct row or multi-leg bundle)
 *
 * @param array<string, mixed> $item Either flat connection or multi-leg array
 * @param string               $dateYmd Date
 * @param int                  $from_station_id Search from
 * @param int                  $to_station_id Search to
 * @return array<string, mixed>
 */
function MRT_normalize_connection_for_api( $item, $dateYmd, $from_station_id, $to_station_id ) {
	$flat = MRT_flatten_wrapped_direct_connection( $item );
	if ( $flat !== null ) {
		$item = $flat;
	}
	if ( isset( $item['legs'] ) && is_array( $item['legs'] ) && count( $item['legs'] ) > 1 ) {
		return MRT_normalize_multi_leg_for_api( $item, $dateYmd );
	}
	$conn  = $item;
	$sid   = intval( $conn['service_id'] ?? 0 );
	$dep   = MRT_connection_row_departure_at_from( $conn );
	$arr   = ! empty( $conn['to_arrival'] ) ? (string) $conn['to_arrival'] : (string) ( $conn['to_departure'] ?? '' );
	$extra = MRT_normalize_segments_single_service( $sid, $from_station_id, $to_station_id, $dateYmd );
	$dur   = $extra['duration_minutes'];
	if ( $dur === null ) {
		$dur = MRT_format_duration_minutes( $dep, $arr );
	}
	return array(
		'connection_type'     => 'direct',
		'transfer_station_id' => null,
		'legs'                => array(),
		'service_id'          => $sid,
		'departure'           => $dep,
		'arrival'             => $arr,
		'from_departure'      => $dep,
		'to_arrival'          => $arr,
		'duration_minutes'    => $dur,
		'train_type'          => (string) ( $conn['train_type'] ?? '' ),
		'service_name'        => (string) ( $conn['service_name'] ?? '' ),
		'route_name'          => (string) ( $conn['route_name'] ?? '' ),
		'destination'         => (string) ( $conn['destination'] ?? '' ),
		'direction'           => (string) ( $conn['direction'] ?? '' ),
		'segments'            => $extra['segments'],
		'notice'              => $extra['notice'],
	);
}

/**
 * One-way journey search: normalized API connections + planner table rows (shared by AJAX + shortcode SSR).
 *
 * @return array{normalized: array<int, array<string, mixed>>, planner_rows: array<int, mixed>}
 */
function MRT_journey_single_trip_normalized_and_planner_rows( int $from_station_id, int $to_station_id, string $dateYmd ): array {
	$min_xfer   = (int) apply_filters( 'mrt_min_transfer_minutes', 5 );
	$raw_multi  = MRT_find_multi_leg_connections(
		$from_station_id,
		$to_station_id,
		$dateYmd,
		$min_xfer,
		true
	);
	$normalized = array();
	foreach ( $raw_multi as $item ) {
		$normalized[] = MRT_normalize_connection_for_api(
			$item,
			$dateYmd,
			$from_station_id,
			$to_station_id
		);
	}
	$planner_rows = array_map( 'MRT_journey_normalized_to_planner_row', $normalized );

	return array(
		'normalized'   => $normalized,
		'planner_rows' => $planner_rows,
	);
}
