<?php
/**
 * Lennakatten import – run logic
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Run the Lennakatten import
 *
 * @return string Success/error message
 */
function MRT_run_lennakatten_import() {
	global $wpdb;

	$station_ids    = MRT_import_create_stations();
	$train_type_ids = MRT_import_create_train_types();
	list($route_id, $route_rev_id, $route_station_ids, $route_rev_station_ids) = MRT_import_create_routes( $station_ids );
	$timetable_id      = MRT_import_create_timetable();
	$created_services  = MRT_import_create_services_out( $route_id, $route_station_ids, $station_ids, $timetable_id, $train_type_ids );
	$created_services += MRT_import_create_services_in( $route_rev_id, $route_rev_station_ids, $station_ids, $timetable_id, $train_type_ids );

	$dates_count = count( MRT_get_timetable_dates( $timetable_id ) );
	return sprintf(
		__( 'Import complete. Stations: %1$d, Routes: 2, Train types: %2$d, Timetable: GRÖN (ID %3$d, %4$d dates), Services created: %5$d.', 'museum-railway-timetable' ),
		count( $station_ids ),
		count( $train_type_ids ),
		$timetable_id,
		$dates_count,
		$created_services
	);
}

function MRT_import_create_stations() {
	$station_ids = array();
	foreach ( MRT_import_get_stations_data() as $s ) {
		$name       = $s[0];
		$order      = $s[1];
		$bus_suffix = isset( $s[2] ) && $s[2];
		$existing   = get_page_by_title( $name, OBJECT, 'mrt_station' );
		if ( $existing ) {
			$station_ids[ $name ] = $existing->ID;
			update_post_meta( $existing->ID, 'mrt_display_order', $order );
			update_post_meta( $existing->ID, 'mrt_station_bus_suffix', $bus_suffix ? '1' : '0' );
		} else {
			$id = wp_insert_post(
				array(
					'post_type'   => 'mrt_station',
					'post_title'  => $name,
					'post_status' => 'publish',
				)
			);
			if ( $id && ! ( $id instanceof \WP_Error ) ) {
				$station_ids[ $name ] = $id;
				update_post_meta( $id, 'mrt_display_order', $order );
				update_post_meta( $id, 'mrt_station_bus_suffix', $bus_suffix ? '1' : '0' );
			}
		}
	}
	return $station_ids;
}

function MRT_import_create_train_types() {
	$ids = array();
	foreach ( MRT_import_get_train_types() as $name => $slug ) {
		$term = term_exists( $name, 'mrt_train_type' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'mrt_train_type', array( 'slug' => $slug ) );
		}
		if ( ! ( $term instanceof \WP_Error ) ) {
			$ids[ $name ] = is_array( $term ) ? $term['term_id'] : $term;
		}
	}
	return $ids;
}

function MRT_import_create_routes( $station_ids ) {
	$route_stations    = array(
		'Uppsala Östra',
		'Fyrislund',
		'Årsta',
		'Skölsta',
		'Bärby',
		'Gunsta',
		'Marielund',
		'Lövstahagen',
		'Selknä',
		'Löt',
		'Länna',
		'Almunge',
		'Moga',
		'Faringe',
	);
	$route_station_ids = array_values(
		array_filter(
			array_map(
				function ( $n ) use ( $station_ids ) {
					return $station_ids[ $n ] ?? null;
				},
				$route_stations
			)
		)
	);

	$route_id              = MRT_import_ensure_route( 'Uppsala Östra – Faringe', $route_station_ids );
	$route_rev_station_ids = array_reverse( $route_station_ids );
	$route_rev_id          = MRT_import_ensure_route( 'Faringe – Uppsala Östra', $route_rev_station_ids );

	return array( $route_id, $route_rev_id, $route_station_ids, $route_rev_station_ids );
}

function MRT_import_ensure_route( $title, $station_ids ) {
	$route = get_page_by_title( $title, OBJECT, 'mrt_route' );
	if ( ! $route ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'mrt_route',
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);
		if ( $id && ! ( $id instanceof \WP_Error ) ) {
			update_post_meta( $id, 'mrt_route_stations', array_values( $station_ids ) );
			update_post_meta( $id, 'mrt_route_start_station', $station_ids[0] );
			update_post_meta( $id, 'mrt_route_end_station', end( $station_ids ) );
			return $id;
		}
		return 0;
	}
	update_post_meta( $route->ID, 'mrt_route_stations', array_values( $station_ids ) );
	update_post_meta( $route->ID, 'mrt_route_start_station', $station_ids[0] );
	update_post_meta( $route->ID, 'mrt_route_end_station', end( $station_ids ) );
	return $route->ID;
}

function MRT_import_create_timetable() {
	$timetable_dates = array_slice( MRT_import_get_timetable_dates(), 0, 20 );
	$existing        = get_posts(
		array(
			'post_type'      => 'mrt_timetable',
			'posts_per_page' => 1,
			'meta_key'       => 'mrt_timetable_type',
			'meta_value'     => 'green',
		)
	);
	if ( empty( $existing ) ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'mrt_timetable',
				'post_title'  => 'GRÖN TIDTABELL 2026',
				'post_status' => 'publish',
			)
		);
		if ( $id && ! ( $id instanceof \WP_Error ) ) {
			update_post_meta( $id, 'mrt_timetable_dates', $timetable_dates );
			update_post_meta( $id, 'mrt_timetable_type', 'green' );
			return $id;
		}
		return 0;
	}
	$id = $existing[0]->ID;
	update_post_meta( $id, 'mrt_timetable_dates', $timetable_dates );
	update_post_meta( $id, 'mrt_timetable_type', 'green' );
	return $id;
}

function MRT_import_create_services_out( $route_id, $route_station_ids, $station_ids, $timetable_id, $train_type_ids ) {
	global $wpdb;
	$table   = $wpdb->prefix . 'mrt_stoptimes';
	$created = 0;
	foreach ( MRT_import_get_services_out() as $svc ) {
		$created += MRT_import_insert_service( $svc, "Uppsala Östra – Faringe {$svc[0]}", $route_id, $route_station_ids, $station_ids['Faringe'] ?? 0, $timetable_id, $table, $train_type_ids );
	}
	return $created;
}

function MRT_import_create_services_in( $route_rev_id, $route_rev_station_ids, $station_ids, $timetable_id, $train_type_ids ) {
	global $wpdb;
	$table   = $wpdb->prefix . 'mrt_stoptimes';
	$created = 0;
	foreach ( MRT_import_get_services_in() as $svc ) {
		$created += MRT_import_insert_service( $svc, "Faringe – Uppsala Östra {$svc[0]}", $route_rev_id, $route_rev_station_ids, $station_ids['Uppsala Östra'] ?? 0, $timetable_id, $table, $train_type_ids );
	}
	return $created;
}

/**
 * @param array<int, mixed>  $times
 * @param array<int, string> $pickup
 * @param array<int, int>    $station_ids
 */
function MRT_import_insert_stoptimes_for_service( int $service_id, array $station_ids, array $times, array $pickup, string $table ): void {
	global $wpdb;
	$seq = 0;
	$n   = count( $station_ids );
	for ( $i = 0; $i < $n; $i++ ) {
		$st_id = $station_ids[ $i ];
		$t     = $times[ $i ] ?? null;
		$arr   = $dep = null;
		if ( is_array( $t ) ) {
			if ( count( $t ) >= 4 ) {
				$arr = sprintf( '%02d:%02d', $t[0], $t[1] );
				$dep = sprintf( '%02d:%02d', $t[2], $t[3] );
			} else {
				$arr = sprintf( '%02d:%02d', $t[0], $t[1] );
				$dep = $arr;
			}
		}
		$sym = $pickup[ $i ] ?? '';
		$pu  = ( $sym === 'P' || $sym === 'X' || $sym === '' ) ? 1 : 0;
		$do  = ( $sym === 'X' || $sym === '' ) ? 1 : 0;
		if ( $sym === 'P' ) {
			$do = 0;
		}

		$wpdb->insert(
			$table,
			array(
				'service_post_id' => $service_id,
				'station_post_id' => $st_id,
				'stop_sequence'   => $seq,
				'arrival_time'    => $arr,
				'departure_time'  => $dep,
				'pickup_allowed'  => $pu,
				'dropoff_allowed' => $do,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%d', '%d' )
		);
		++$seq;
	}
}

function MRT_import_insert_service( $svc, $title, $route_id, $station_ids, $end_station_id, $timetable_id, $table, $train_type_ids ) {
	$num             = $svc[0];
	$train_type_name = $svc[1];
	$times           = $svc[2];
	$pickup          = $svc[3];
	$train_type_id   = $train_type_ids[ $train_type_name ] ?? null;

	if ( MRT_get_post_by_title( $title, 'mrt_service' ) ) {
		return 0;
	}

	$service_id = wp_insert_post(
		array(
			'post_type'   => 'mrt_service',
			'post_title'  => $title,
			'post_status' => 'publish',
		)
	);
	if ( ! $service_id || $service_id instanceof \WP_Error ) {
		return 0;
	}

	if ( $train_type_id ) {
		wp_set_object_terms( $service_id, (int) $train_type_id, 'mrt_train_type' );
	}
	update_post_meta( $service_id, 'mrt_service_timetable_id', $timetable_id );
	update_post_meta( $service_id, 'mrt_service_route_id', $route_id );
	update_post_meta( $service_id, 'mrt_service_number', $num );
	update_post_meta( $service_id, 'mrt_service_end_station_id', $end_station_id );

	MRT_import_insert_stoptimes_for_service( $service_id, $station_ids, $times, $pickup, $table );
	return 1;
}
