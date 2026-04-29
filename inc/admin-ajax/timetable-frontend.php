<?php
/**
 * AJAX handler for timetable by date (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get timetable for a specific date via AJAX (frontend)
 *
 * @return void Sends JSON response via wp_send_json_success/wp_send_json_error
 */
function MRT_ajax_get_timetable_for_date() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mrt_frontend' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page.', 'museum-railway-timetable' ) ) );
	}
	$date       = sanitize_text_field( $_POST['date'] ?? '' );
	$train_type = sanitize_text_field( $_POST['train_type'] ?? '' );

	if ( empty( $date ) || ! MRT_validate_date( $date ) ) {
		wp_send_json_error( array( 'message' => __( 'Please select a valid date.', 'museum-railway-timetable' ) ) );
	}

	$html = MRT_render_timetable_for_date( $date, $train_type );

	wp_send_json_success( array( 'html' => $html ) );
}
