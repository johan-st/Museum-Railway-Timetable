<?php

declare(strict_types=1);

/**
 * Prepare service information for timetable rendering
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param WP_Post      $service
 * @param WP_Term|null $train_type
 * @return array{classes: array<int, string>, is_special: bool, special_name: string, service_number: string|int}
 */
function MRT_prepare_service_train_display( WP_Post $service, $train_type ): array {
	$service_number = get_post_meta( $service->ID, 'mrt_service_number', true );
	if ( empty( $service_number ) ) {
		$service_number = $service->ID;
	}

	$classes      = array( 'mrt-service-col' );
	$is_special   = false;
	$special_name = '';
	if ( $train_type ) {
		$train_type_slug       = $train_type->slug;
		$train_type_name_lower = strtolower( $train_type->name );
		if ( strpos( $train_type_name_lower, 'buss' ) !== false || strpos( $train_type_slug, 'bus' ) !== false ) {
			$classes[] = 'mrt-service-bus';
		} elseif ( strpos( $train_type_name_lower, 'express' ) !== false || strpos( $service->post_title, 'express' ) !== false ) {
			$classes[]  = 'mrt-service-special';
			$is_special = true;
			if ( strpos( strtolower( $service->post_title ), 'express' ) !== false ) {
				$special_name = 'Express';
			} elseif ( strpos( strtolower( $service->post_title ), 'thun' ) !== false ) {
				$special_name = "Thun's-expressen";
			}
		}
	}

	return array(
		'classes'        => $classes,
		'is_special'     => $is_special,
		'special_name'   => $special_name,
		'service_number' => $service_number,
	);
}

/**
 * Connections after this service at end station (for overview footnotes).
 *
 * @param array<string, mixed> $service_stop_times
 * @param array<string, mixed> $destination_data
 * @return array<int, mixed>
 */
function MRT_prepare_service_end_connections( WP_Post $service, $service_stop_times, $destination_data, string $dateYmd ): array {
	$connections = array();
	if ( empty( $destination_data['end_station_id'] ) ) {
		return $connections;
	}
	$end_station_id = $destination_data['end_station_id'];
	if ( ! isset( $service_stop_times[ $end_station_id ] ) ) {
		return $connections;
	}
	$end_stop    = $service_stop_times[ $end_station_id ];
	$end_arrival = $end_stop['arrival_time'] ?? '';
	if ( $end_arrival && $dateYmd !== '' ) {
		$connections = MRT_find_connecting_services( $end_station_id, $service->ID, $end_arrival, $dateYmd, 2 );
	}
	return $connections;
}

/**
 * Prepare service information and CSS classes for timetable rendering
 *
 * @param array<int, array<string, mixed>> $services_list From MRT_group_services_by_route
 * @return array{service_classes: array<int, array<int, string>>, service_info: array<int, array<string, mixed>>, all_connections: array<int, mixed>}
 */
function MRT_prepare_service_info( array $services_list, string $dateYmd ): array {
	$service_classes = array();
	$service_info    = array();
	$all_connections = array();

	foreach ( $services_list as $idx => $service_data ) {
		$service                 = $service_data['service'];
		$train_type              = $service_data['train_type'];
		$disp                    = MRT_prepare_service_train_display( $service, $train_type );
		$service_classes[ $idx ] = $disp['classes'];
		if ( in_array( (string) $disp['service_number'], array( '93', '96' ), true ) ) {
			$service_classes[ $idx ][] = 'mrt-service-thuns';
		}

		$service_stop_times = $service_data['stop_times'] ?? array();
		$destination_data   = MRT_get_service_destination( $service->ID );
		$connections        = MRT_prepare_service_end_connections( $service, $service_stop_times, $destination_data, $dateYmd );

		if ( $connections !== array() ) {
			$all_connections[ $idx ] = $connections;
		}

		$service_info[ $idx ] = array(
			'service'        => $service,
			'train_type'     => $train_type,
			'service_number' => $disp['service_number'],
			'is_special'     => $disp['is_special'],
			'special_name'   => $disp['special_name'],
			'destination'    => $destination_data['destination'] ?? '',
		);
	}

	return array(
		'service_classes' => $service_classes,
		'service_info'    => $service_info,
		'all_connections' => $all_connections,
	);
}
