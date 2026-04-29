<?php

declare(strict_types=1);

/**
 * AJAX handlers for Stop Times
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate stoptime input for add/update
 * Sends JSON error and exits on failure
 *
 * @param bool $require_id If true, require id field (for update)
 * @return array Validated data
 */
function MRT_validate_stoptime_input( $require_id = false ) {
	$id         = intval( $_POST['id'] ?? 0 );
	$service_id = intval( $_POST['service_id'] ?? 0 );
	$station_id = intval( $_POST['station_id'] ?? 0 );
	$sequence   = intval( $_POST['sequence'] ?? 0 );
	$arrival    = sanitize_text_field( $_POST['arrival'] ?? '' );
	$departure  = sanitize_text_field( $_POST['departure'] ?? '' );
	$pickup     = isset( $_POST['pickup'] ) ? 1 : 0;
	$dropoff    = isset( $_POST['dropoff'] ) ? 1 : 0;

	if ( $require_id && $id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid input.', MRT_TEXT_DOMAIN ) ) );
	}
	if ( ! $require_id && ( $service_id <= 0 || $station_id <= 0 || $sequence <= 0 ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid input.', MRT_TEXT_DOMAIN ) ) );
	}
	if ( $require_id && ( $station_id <= 0 || $sequence <= 0 ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid input.', MRT_TEXT_DOMAIN ) ) );
	}
	$arrival_msg   = __( 'Invalid arrival time format. Use HH:MM.', MRT_TEXT_DOMAIN );
	$departure_msg = __( 'Invalid departure time format. Use HH:MM.', MRT_TEXT_DOMAIN );
	if ( $arrival && ! MRT_validate_time_hhmm( $arrival ) ) {
		wp_send_json_error( array( 'message' => $arrival_msg ) );
	}
	if ( $departure && ! MRT_validate_time_hhmm( $departure ) ) {
		wp_send_json_error( array( 'message' => $departure_msg ) );
	}
	$data = compact( 'station_id', 'sequence', 'arrival', 'departure', 'pickup', 'dropoff' );
	if ( $require_id ) {
		$data['id'] = $id;
	} else {
		$data['service_id'] = $service_id;
	}
	return $data;
}

/**
 * Fetch a stop time row by id.
 *
 * @param int $id Stop time row ID
 * @return array<string, mixed>|null
 */
function MRT_get_stoptime_row_by_id( int $id ): ?array {
	if ( $id <= 0 ) {
		return null;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';
	$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
	return is_array( $row ) ? $row : null;
}

/**
 * Fetch a stop time row and verify the current user may edit its service.
 *
 * @param int $id Stop time row ID
 * @return array<string, mixed>
 */
function MRT_get_authorized_stoptime_row_for_ajax( int $id ): array {
	$row = MRT_get_stoptime_row_by_id( $id );
	if ( ! $row ) {
		wp_send_json_error( array( 'message' => __( 'Stop time not found.', MRT_TEXT_DOMAIN ) ) );
	}
	MRT_verify_ajax_edit_post_permission( (int) ( $row['service_post_id'] ?? 0 ) );
	return $row;
}

/**
 * Add stop time via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_add_stoptime() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );
	$data = MRT_validate_stoptime_input( false );
	MRT_verify_ajax_edit_post_permission( (int) $data['service_id'] );

	global $wpdb;
	$table  = $wpdb->prefix . 'mrt_stoptimes';
	$result = $wpdb->insert(
		$table,
		array(
			'service_post_id' => $data['service_id'],
			'station_post_id' => $data['station_id'],
			'stop_sequence'   => $data['sequence'],
			'arrival_time'    => $data['arrival'] ?: null,
			'departure_time'  => $data['departure'] ?: null,
			'pickup_allowed'  => $data['pickup'],
			'dropoff_allowed' => $data['dropoff'],
		),
		array( '%d', '%d', '%d', '%s', '%s', '%d', '%d' )
	);

	if ( $result === false ) {
		MRT_check_db_error( 'MRT_ajax_add_stoptime' );
		wp_send_json_error( array( 'message' => __( 'Failed to add stop time.', MRT_TEXT_DOMAIN ) ) );
	}
	$station      = get_post( $data['station_id'] );
	$station_name = $station ? $station->post_title : '#' . $data['station_id'];
	wp_send_json_success(
		array(
			'id'           => $wpdb->insert_id,
			'station_name' => $station_name,
			'arrival'      => $data['arrival'] ?: '—',
			'departure'    => $data['departure'] ?: '—',
			'pickup'       => $data['pickup'],
			'dropoff'      => $data['dropoff'],
		)
	);
}

/**
 * Update stop time via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_update_stoptime() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );
	$data              = MRT_validate_stoptime_input( true );
	$row               = MRT_get_authorized_stoptime_row_for_ajax( (int) $data['id'] );
	$posted_service_id = intval( $_POST['service_id'] ?? 0 );
	if ( $posted_service_id > 0 && $posted_service_id !== (int) $row['service_post_id'] ) {
		wp_send_json_error( array( 'message' => __( 'Stop time does not belong to this service.', MRT_TEXT_DOMAIN ) ) );
	}

	global $wpdb;
	$table  = $wpdb->prefix . 'mrt_stoptimes';
	$result = $wpdb->update(
		$table,
		array(
			'station_post_id' => $data['station_id'],
			'stop_sequence'   => $data['sequence'],
			'arrival_time'    => $data['arrival'] ?: null,
			'departure_time'  => $data['departure'] ?: null,
			'pickup_allowed'  => $data['pickup'],
			'dropoff_allowed' => $data['dropoff'],
		),
		array( 'id' => $data['id'] ),
		array( '%d', '%d', '%s', '%s', '%d', '%d' ),
		array( '%d' )
	);

	if ( $result === false ) {
		MRT_check_db_error( 'MRT_ajax_update_stoptime' );
		wp_send_json_error( array( 'message' => __( 'Failed to update stop time.', MRT_TEXT_DOMAIN ) ) );
	}
	$station      = get_post( $data['station_id'] );
	$station_name = $station ? $station->post_title : '#' . $data['station_id'];
	wp_send_json_success(
		array(
			'station_name' => $station_name,
			'arrival'      => $data['arrival'] ?: '—',
			'departure'    => $data['departure'] ?: '—',
			'pickup'       => $data['pickup'],
			'dropoff'      => $data['dropoff'],
		)
	);
}

/**
 * Delete stop time via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_delete_stoptime() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );

	$id = intval( $_POST['id'] ?? 0 );
	if ( $id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid ID.', MRT_TEXT_DOMAIN ) ) );
	}
	MRT_get_authorized_stoptime_row_for_ajax( $id );

	global $wpdb;
	$table = $wpdb->prefix . 'mrt_stoptimes';

	$result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

	if ( $result === false ) {
		MRT_check_db_error( 'MRT_ajax_delete_stoptime' );
		wp_send_json_error( array( 'message' => __( 'Failed to delete stop time.', MRT_TEXT_DOMAIN ) ) );
	}

	wp_send_json_success();
}

/**
 * Get stop time data via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_get_stoptime() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );

	$id = intval( $_POST['id'] ?? 0 );
	if ( $id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid ID.', MRT_TEXT_DOMAIN ) ) );
	}
	$stoptime = MRT_get_authorized_stoptime_row_for_ajax( $id );

	wp_send_json_success( $stoptime );
}

/**
 * Normalize one submitted stop time row for save_all.
 *
 * @param array $stop Stop data
 * @param int   $sequence Stop sequence
 * @return array<string, mixed>|WP_Error|null Null when the row is intentionally omitted
 */
function MRT_normalize_stoptime_for_save_all( array $stop, int $sequence ) {
	$station_id = intval( $stop['station_id'] ?? 0 );
	$stops_here = isset( $stop['stops_here'] ) && $stop['stops_here'] == '1';
	if ( ! $stops_here ) {
		return null;
	}
	if ( $station_id <= 0 ) {
		return new WP_Error( 'invalid_station', __( 'Invalid station in stop times.', MRT_TEXT_DOMAIN ) );
	}
	$arrival   = sanitize_text_field( $stop['arrival'] ?? '' );
	$departure = sanitize_text_field( $stop['departure'] ?? '' );
	if ( ( $arrival && ! MRT_validate_time_hhmm( $arrival ) ) || ( $departure && ! MRT_validate_time_hhmm( $departure ) ) ) {
		return new WP_Error( 'invalid_time', __( 'Invalid time format in stop times. Use HH:MM.', MRT_TEXT_DOMAIN ) );
	}
	$pickup  = isset( $stop['pickup'] ) && $stop['pickup'] == '1' ? 1 : 0;
	$dropoff = isset( $stop['dropoff'] ) && $stop['dropoff'] == '1' ? 1 : 0;

	return array(
		'station_post_id' => $station_id,
		'stop_sequence'   => $sequence,
		'arrival_time'    => $arrival ?: null,
		'departure_time'  => $departure ?: null,
		'pickup_allowed'  => $pickup,
		'dropoff_allowed' => $dropoff,
	);
}

/**
 * Normalize all submitted stop time rows for save_all.
 *
 * @param array<int, array<string, mixed>> $stops Stop data
 * @return array<int, array<string, mixed>>|WP_Error
 */
function MRT_prepare_stoptimes_for_save_all( array $stops ) {
	$prepared = array();
	$sequence = 1;
	foreach ( $stops as $stop ) {
		if ( ! is_array( $stop ) ) {
			return new WP_Error( 'invalid_stop', __( 'Invalid stops data.', MRT_TEXT_DOMAIN ) );
		}
		$row = MRT_normalize_stoptime_for_save_all( $stop, $sequence );
		if ( is_wp_error( $row ) ) {
			return $row;
		}
		if ( $row !== null ) {
			$prepared[] = $row;
			++$sequence;
		}
	}
	return $prepared;
}

/**
 * Insert a prepared stop time for save_all.
 *
 * @param wpdb  $wpdb WordPress DB object
 * @param array $row Prepared stop row
 * @param int   $service_id Service ID
 * @return int|false Inserted row ID, or false on failure
 */
function MRT_insert_prepared_stoptime_for_save_all( $wpdb, array $row, int $service_id ) {
	$table  = $wpdb->prefix . 'mrt_stoptimes';
	$result = $wpdb->insert(
		$table,
		array(
			'service_post_id' => $service_id,
			'station_post_id' => $row['station_post_id'],
			'stop_sequence'   => $row['stop_sequence'],
			'arrival_time'    => $row['arrival_time'],
			'departure_time'  => $row['departure_time'],
			'pickup_allowed'  => $row['pickup_allowed'],
			'dropoff_allowed' => $row['dropoff_allowed'],
		),
		array( '%d', '%d', '%d', '%s', '%s', '%d', '%d' )
	);
	if ( $result === false ) {
		MRT_check_db_error( 'MRT_ajax_save_all_stoptimes' );
		return false;
	}
	return (int) $wpdb->insert_id;
}

/**
 * Delete inserted replacement rows after a failed save_all attempt.
 *
 * @param wpdb       $wpdb WordPress DB object
 * @param array<int> $inserted_ids Row IDs to delete
 * @return void
 */
function MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, array $inserted_ids ): void {
	$table = $wpdb->prefix . 'mrt_stoptimes';
	foreach ( $inserted_ids as $id ) {
		$wpdb->delete( $table, array( 'id' => (int) $id ), array( '%d' ) );
	}
}

/**
 * Delete old service stop times after replacements were inserted.
 *
 * @param wpdb       $wpdb WordPress DB object
 * @param int        $service_id Service ID
 * @param array<int> $replacement_ids Replacement row IDs
 * @return bool True if old rows were deleted
 */
function MRT_delete_old_stoptimes_after_save_all( $wpdb, int $service_id, array $replacement_ids ): bool {
	$table = $wpdb->prefix . 'mrt_stoptimes';
	if ( $replacement_ids === array() ) {
		return $wpdb->delete( $table, array( 'service_post_id' => $service_id ), array( '%d' ) ) !== false;
	}

	$in  = implode( ',', array_map( 'intval', $replacement_ids ) );
	$sql = $wpdb->prepare(
		"DELETE FROM $table WHERE service_post_id = %d AND id NOT IN ($in)",
		$service_id
	);
	return $wpdb->query( $sql ) !== false;
}

/**
 * Save all stop times for a service (from route-based form)
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_save_all_stoptimes() {
	check_ajax_referer( 'mrt_stoptimes_nonce', 'nonce' );
	$service_id = intval( $_POST['service_id'] ?? 0 );
	if ( $service_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid service ID.', MRT_TEXT_DOMAIN ) ) );
	}
	MRT_verify_ajax_edit_post_permission( $service_id );
	$stops = isset( $_POST['stops'] ) ? $_POST['stops'] : array();
	if ( ! is_array( $stops ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid stops data.', MRT_TEXT_DOMAIN ) ) );
	}
	$prepared = MRT_prepare_stoptimes_for_save_all( $stops );
	if ( is_wp_error( $prepared ) ) {
		wp_send_json_error( array( 'message' => $prepared->get_error_message() ) );
	}

	global $wpdb;
	$inserted     = 0;
	$inserted_ids = array();
	foreach ( $prepared as $row ) {
		$inserted_id = MRT_insert_prepared_stoptime_for_save_all( $wpdb, $row, $service_id );
		if ( $inserted_id === false ) {
			MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, $inserted_ids );
			wp_send_json_error( array( 'message' => __( 'Failed to save stop times.', MRT_TEXT_DOMAIN ) ) );
		}
		$inserted_ids[] = $inserted_id;
		++$inserted;
	}
	if ( ! MRT_delete_old_stoptimes_after_save_all( $wpdb, $service_id, $inserted_ids ) ) {
		MRT_cleanup_inserted_stoptimes_for_save_all( $wpdb, $inserted_ids );
		MRT_check_db_error( 'MRT_ajax_save_all_stoptimes' );
		wp_send_json_error( array( 'message' => __( 'Failed to replace stop times.', MRT_TEXT_DOMAIN ) ) );
	}

	wp_send_json_success(
		array(
			'message' => sprintf( __( '%d stop times saved.', MRT_TEXT_DOMAIN ), $inserted ),
			'count'   => $inserted,
		)
	);
}
