<?php
/**
 * Development: Clear DB action (WP_DEBUG only)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Handle clear DB action (development only)
 */
add_action(
	'admin_init',
	function () {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( ! isset( $_POST['mrt_action'] ) || $_POST['mrt_action'] !== 'clear_db' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['mrt_clear_db_nonce'] ) || ! wp_verify_nonce( $_POST['mrt_clear_db_nonce'], 'mrt_clear_db' ) ) {
			wp_die( __( 'Security check failed.', 'museum-railway-timetable' ) );
		}

		global $wpdb;

		// Delete all CPTs
		$stations   = get_posts(
			array(
				'post_type'      => 'mrt_station',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$services   = get_posts(
			array(
				'post_type'      => 'mrt_service',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$routes     = get_posts(
			array(
				'post_type'      => 'mrt_route',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$timetables = get_posts(
			array(
				'post_type'      => 'mrt_timetable',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $stations as $id ) {
			wp_delete_post( $id, true );
		}
		foreach ( $services as $id ) {
			wp_delete_post( $id, true );
		}
		foreach ( $routes as $id ) {
			wp_delete_post( $id, true );
		}
		foreach ( $timetables as $id ) {
			wp_delete_post( $id, true );
		}

		// Delete custom tables data
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}mrt_stoptimes" );

		// Delete train types
		$terms = get_terms(
			array(
				'taxonomy'   => 'mrt_train_type',
				'hide_empty' => false,
			)
		);
		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, 'mrt_train_type' );
		}

		wp_redirect( add_query_arg( array( 'mrt_cleared' => '1' ), admin_url( 'admin.php?page=mrt_settings' ) ) );
		exit;
	}
);

// Show success message
add_action(
	'admin_notices',
	function () {
		if ( isset( $_GET['mrt_cleared'] ) && sanitize_text_field( wp_unslash( $_GET['mrt_cleared'] ) ) === '1' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'All timetable data has been cleared.', 'museum-railway-timetable' ) . '</p></div>';
		}
	}
);
