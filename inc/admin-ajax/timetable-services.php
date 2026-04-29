<?php
/**
 * AJAX handlers for Timetable Services (add/remove trips)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Validate and parse add-service-to-timetable AJAX input
 *
 * @return array|WP_Error [timetable_id, route_id, train_type_id, end_station_id, direction] or error
 */
function MRT_ajax_validate_add_service_input() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mrt_timetable_services_nonce' ) ) {
		return new WP_Error( 'nonce', __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ) );
	}
	$timetable_id = intval( $_POST['timetable_id'] ?? 0 );
	$route_id     = intval( $_POST['route_id'] ?? 0 );
	if ( $timetable_id <= 0 ) {
		return new WP_Error( 'timetable', __( 'Invalid timetable.', 'museum-railway-timetable' ) );
	}
	if ( $route_id <= 0 ) {
		return new WP_Error( 'route', __( 'Route is required.', 'museum-railway-timetable' ) );
	}
	if ( ! current_user_can( 'edit_post', $timetable_id ) ) {
		return new WP_Error( 'permission', __( 'Permission denied.', 'museum-railway-timetable' ) );
	}
	$train_type_id  = intval( $_POST['train_type_id'] ?? 0 );
	$end_station_id = intval( $_POST['end_station_id'] ?? 0 );
	$direction      = sanitize_text_field( $_POST['direction'] ?? '' );
	if ( $end_station_id > 0 ) {
		$direction = MRT_calculate_direction_from_end_station( $route_id, $end_station_id );
	} elseif ( $direction !== '' && ! in_array( $direction, array( 'dit', 'från' ), true ) ) {
		$direction = '';
	}
	return compact( 'timetable_id', 'route_id', 'train_type_id', 'end_station_id', 'direction' );
}

/**
 * Build auto title for new service
 *
 * @param int    $route_id       Route post ID
 * @param int    $end_station_id End station post ID
 * @param string $direction      Direction ('dit' or 'från')
 * @return string Service title
 */
function MRT_build_service_auto_title( $route_id, $end_station_id, $direction ) {
	$route      = get_post( $route_id );
	$route_name = $route ? $route->post_title : __( 'Route', 'museum-railway-timetable' ) . ' #' . $route_id;
	$dest       = '';
	if ( $end_station_id > 0 ) {
		$s    = get_post( $end_station_id );
		$dest = $s ? ' → ' . $s->post_title : '';
	} elseif ( $direction === 'dit' ) {
		$dest = ' - ' . __( 'Dit', 'museum-railway-timetable' );
	} elseif ( $direction === 'från' ) {
		$dest = ' - ' . __( 'Från', 'museum-railway-timetable' );
	}
	return $route_name . $dest;
}

/**
 * Build response data for add-service success
 *
 * @param int    $service_id     Service post ID
 * @param int    $route_id       Route post ID
 * @param int    $train_type_id  Train type term ID
 * @param int    $end_station_id End station post ID
 * @param string $direction      Direction ('dit' or 'från')
 * @return array Response data for frontend
 */
function MRT_build_add_service_response( $service_id, $route_id, $train_type_id, $end_station_id, $direction ) {
	$service    = get_post( $service_id );
	$route      = get_post( $route_id );
	$train_type = $train_type_id > 0 ? get_term( $train_type_id, 'mrt_train_type' ) : null;
	$dest_name  = '—';
	if ( $end_station_id > 0 ) {
		$s         = get_post( $end_station_id );
		$dest_name = $s ? $s->post_title : '—';
	} elseif ( $direction === 'dit' ) {
		$dest_name = __( 'Dit', 'museum-railway-timetable' );
	} elseif ( $direction === 'från' ) {
		$dest_name = __( 'Från', 'museum-railway-timetable' );
	}
	return array(
		'service_id'      => $service_id,
		'service_title'   => $service ? $service->post_title : '',
		'route_name'      => $route ? $route->post_title : '—',
		'train_type_name' => $train_type ? $train_type->name : '—',
		'destination'     => $dest_name,
		'direction'       => $direction === 'dit' ? __( 'Dit', 'museum-railway-timetable' ) : ( $direction === 'från' ? __( 'Från', 'museum-railway-timetable' ) : '—' ),
		'edit_url'        => get_edit_post_link( $service_id, 'raw' ),
	);
}

/**
 * Add service to timetable via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_add_service_to_timetable() {
	$input = MRT_ajax_validate_add_service_input();
	if ( is_wp_error( $input ) ) {
		wp_send_json_error( array( 'message' => $input->get_error_message() ) );
	}

	$auto_title = MRT_build_service_auto_title( $input['route_id'], $input['end_station_id'], $input['direction'] );
	$service_id = wp_insert_post(
		array(
			'post_type'   => 'mrt_service',
			'post_title'  => $auto_title,
			'post_status' => 'publish',
		)
	);

	if ( $service_id instanceof \WP_Error ) {
		wp_send_json_error( array( 'message' => __( 'Failed to create trip: ', 'museum-railway-timetable' ) . $service_id->get_error_message() ) );
	}

	update_post_meta( $service_id, 'mrt_service_timetable_id', $input['timetable_id'] );
	update_post_meta( $service_id, 'mrt_service_route_id', $input['route_id'] );
	if ( $input['end_station_id'] > 0 ) {
		update_post_meta( $service_id, 'mrt_service_end_station_id', $input['end_station_id'] );
		if ( $input['direction'] ) {
			update_post_meta( $service_id, 'mrt_direction', $input['direction'] );
		}
	} elseif ( $input['direction'] !== '' ) {
		update_post_meta( $service_id, 'mrt_direction', $input['direction'] );
	}
	if ( $input['train_type_id'] > 0 ) {
		wp_set_object_terms( $service_id, array( $input['train_type_id'] ), 'mrt_train_type' );
	}

	$response = MRT_build_add_service_response(
		$service_id,
		$input['route_id'],
		$input['train_type_id'],
		$input['end_station_id'],
		$input['direction']
	);
	wp_send_json_success( $response );
}

/**
 * Remove service from timetable via AJAX
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_remove_service_from_timetable() {
	check_ajax_referer( 'mrt_timetable_services_nonce', 'nonce' );

	$service_id = intval( $_POST['service_id'] ?? 0 );

	if ( $service_id <= 0 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid service.', 'museum-railway-timetable' ) ) );
	}
	MRT_verify_ajax_edit_post_permission( $service_id );

	$timetable_id = (int) get_post_meta( $service_id, 'mrt_service_timetable_id', true );
	if ( $timetable_id > 0 ) {
		MRT_verify_ajax_edit_post_permission( $timetable_id );
	}

	delete_post_meta( $service_id, 'mrt_service_timetable_id' );

	wp_send_json_success( array( 'message' => __( 'Trip removed from timetable.', 'museum-railway-timetable' ) ) );
}
