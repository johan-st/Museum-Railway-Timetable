<?php

declare(strict_types=1);

/**
 * Utility helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify meta box save: nonce, autosave, permissions
 * Call at start of save_post_* handlers.
 *
 * @param int    $post_id Post ID
 * @param string $nonce_name $_POST key for nonce
 * @param string $nonce_action wp_verify_nonce action
 * @return bool True if save should proceed, false to abort
 */
function MRT_verify_meta_box_save( int $post_id, string $nonce_name, string $nonce_action ): bool {
	if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) ), $nonce_action ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
}

/**
 * Verify AJAX permission to edit a specific post.
 *
 * @param int $post_id Post ID
 * @return void
 */
function MRT_verify_ajax_edit_post_permission( int $post_id ): void {
	if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', MRT_TEXT_DOMAIN ) ) );
	}
}

/**
 * Verify AJAX permission to delete a specific post.
 *
 * @param int $post_id Post ID
 * @return void
 */
function MRT_verify_ajax_delete_post_permission( int $post_id ): void {
	if ( $post_id <= 0 || ! current_user_can( 'delete_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', MRT_TEXT_DOMAIN ) ) );
	}
}

/**
 * Render alert HTML (error, info, warning)
 *
 * @param string $message Message text (will be escaped)
 * @param string $type 'error'|'info'|'warning'
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-empty')
 * @return string HTML
 */
function MRT_render_alert( string $message, string $type = 'error', string $extra_classes = '' ): string {
	$allowed_types = array( 'error', 'info', 'warning' );
	$type          = in_array( $type, $allowed_types ) ? $type : 'error';
	$classes       = 'mrt-alert mrt-alert-' . $type;
	if ( ! empty( $extra_classes ) ) {
		$classes .= ' ' . esc_attr( $extra_classes );
	}
	$role = ( $type === 'info' ) ? 'status' : 'alert';

	return '<div class="' . $classes . '" role="' . esc_attr( $role ) . '">' . esc_html( $message ) . '</div>';
}

/**
 * Render info box (title + content)
 *
 * @param string $title Box title (will be escaped)
 * @param string $content HTML content (sanitized with wp_kses_post for safe HTML)
 * @param string $extra_classes Optional extra CSS classes (e.g. 'mrt-mb-1')
 * @return void Outputs HTML
 */
function MRT_render_info_box( string $title, string $content, string $extra_classes = '' ): void {
	$classes = 'mrt-alert mrt-alert-info mrt-info-box';
	if ( ! empty( $extra_classes ) ) {
		$classes .= ' ' . esc_attr( $extra_classes );
	}
	echo '<div class="' . esc_attr( $classes ) . '">';
	echo '<p><strong>' . esc_html( $title ) . '</strong></p>';
	echo wp_kses_post( $content );
	echo '</div>';
}

/**
 * Get post by title and post type
 *
 * @param string $title Post title
 * @param string $post_type Post type (e.g., 'mrt_station', 'mrt_service')
 * @return WP_Post|null Post object or null if not found
 */
function MRT_get_post_by_title( string $title, string $post_type ): ?WP_Post {
	if ( empty( $title ) || empty( $post_type ) ) {
		return null;
	}
	$post = get_page_by_title( sanitize_text_field( $title ), OBJECT, $post_type );
	return $post ?: null;
}

/**
 * Check for database errors and log if WP_DEBUG is enabled
 *
 * @param string $context Context string for error logging (e.g., function name)
 * @return bool True if error occurred, false otherwise
 */
function MRT_check_db_error( string $context = '' ): bool {
	global $wpdb;
	if ( $wpdb->last_error ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$message = 'MRT: Database error';
			if ( $context ) {
				$message .= ' in ' . $context;
			}
			$message .= ': ' . $wpdb->last_error;
			error_log( $message );
		}
		return true;
	}
	return false;
}

/**
 * Log error message if WP_DEBUG is enabled
 *
 * @param string $message Error message to log
 * @return void
 */
function MRT_log_error( string $message ): void {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'MRT: ' . $message );
	}
}

/**
 * Get icon for train type
 * Returns emoji or HTML based on train type name/slug
 *
 * @param WP_Term|null $train_type Train type term object
 * @return string Icon HTML or empty string
 */
function MRT_get_train_type_icon( ?WP_Term $train_type ): string {
	if ( ! $train_type ) {
		return '';
	}

	$name_lower = strtolower( $train_type->name );
	$slug_lower = strtolower( $train_type->slug );

	// Match common train types
	if ( strpos( $name_lower, 'ång' ) !== false || strpos( $slug_lower, 'steam' ) !== false || strpos( $slug_lower, 'ang' ) !== false ) {
		return MRT_train_type_symbol_svg( 'steam' );
	} elseif ( strpos( $name_lower, 'diesel' ) !== false || strpos( $slug_lower, 'diesel' ) !== false ) {
		return MRT_train_type_symbol_svg( 'diesel' );
	} elseif ( strpos( $name_lower, 'elektrisk' ) !== false || strpos( $name_lower, 'electric' ) !== false || strpos( $slug_lower, 'electric' ) !== false ) {
		return MRT_train_type_symbol_svg( 'diesel' );
	} elseif ( strpos( $name_lower, 'rälsbuss' ) !== false || strpos( $name_lower, 'railbus' ) !== false || strpos( $slug_lower, 'bus' ) !== false || strpos( $slug_lower, 'buss' ) !== false ) {
		return MRT_train_type_symbol_svg( 'railbus' );
	}

	return MRT_train_type_symbol_svg( 'diesel' );
}

/**
 * Printed timetable train symbol SVG.
 *
 * @param string $type steam|diesel|railbus
 */
function MRT_train_type_symbol_svg( string $type ): string {
	if ( $type === 'railbus' ) {
		return '<svg class="mrt-train-symbol mrt-train-symbol--railbus" aria-hidden="true" viewBox="0 0 64 24" focusable="false"><path d="M11 8h40c4 0 7 3 7 7v2H6v-4c0-3 2-5 5-5Z" fill="currentColor"/><path d="M18 4h24c5 0 9 3 11 7H8c2-4 5-7 10-7Z" fill="currentColor"/><circle cx="18" cy="19" r="4" fill="currentColor"/><circle cx="47" cy="19" r="4" fill="currentColor"/><rect x="18" y="7" width="8" height="4" fill="#fff"/><rect x="30" y="7" width="8" height="4" fill="#fff"/></svg>';
	}

	if ( $type === 'steam' ) {
		return '<svg class="mrt-train-symbol mrt-train-symbol--steam" aria-hidden="true" viewBox="0 0 64 24" focusable="false"><path d="M8 12h27v7H8z" fill="currentColor"/><path d="M36 8h9l5 4h6v7H36z" fill="currentColor"/><rect x="14" y="6" width="5" height="6" fill="currentColor"/><rect x="23" y="4" width="4" height="8" fill="currentColor"/><path d="M27 2h8v4h-8z" fill="currentColor"/><circle cx="14" cy="20" r="3" fill="currentColor"/><circle cx="26" cy="20" r="3" fill="currentColor"/><circle cx="44" cy="20" r="3" fill="currentColor"/><circle cx="55" cy="20" r="3" fill="currentColor"/></svg>';
	}

	return '<svg class="mrt-train-symbol mrt-train-symbol--diesel" aria-hidden="true" viewBox="0 0 64 24" focusable="false"><path d="M9 7h46c3 0 5 2 5 5v6H4v-6c0-3 2-5 5-5Z" fill="currentColor"/><rect x="14" y="3" width="10" height="4" fill="currentColor"/><rect x="30" y="3" width="12" height="4" fill="currentColor"/><rect x="13" y="10" width="8" height="4" fill="#fff"/><rect x="25" y="10" width="8" height="4" fill="#fff"/><rect x="37" y="10" width="8" height="4" fill="#fff"/><circle cx="18" cy="20" r="3" fill="currentColor"/><circle cx="46" cy="20" r="3" fill="currentColor"/></svg>';
}

/**
 * Convert time format from HH:MM to HH.MM
 *
 * @param string|null $time Time in HH:MM format or null
 * @return string Time in HH.MM format or empty string
 */
function MRT_format_time_display( ?string $time ): string {
	if ( empty( $time ) ) {
		return '';
	}
	return str_replace( ':', '.', $time );
}

/**
 * Format time display for a stop time
 * Determines the appropriate symbol (P, A, X, |) and formats the time
 *
 * @param array|null $stop_time Stop time data array with keys: arrival_time, departure_time, pickup_allowed, dropoff_allowed
 * @return string Formatted time display (e.g., "10.13", "P 10.13", "X", "|", "—")
 */
/**
 * Prefix (P/A) and time fragment for a stopping row.
 *
 * @return array{0: string, 1: string} [symbol_prefix, time_str]
 */
function MRT_stop_time_prefix_and_time_parts( array $stop_time ): array {
	$arrival         = $stop_time['arrival_time'] ?? '';
	$departure       = $stop_time['departure_time'] ?? '';
	$pickup_allowed  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff_allowed = ! empty( $stop_time['dropoff_allowed'] );

	$symbol_prefix = '';
	if ( $pickup_allowed && ! $dropoff_allowed ) {
		$symbol_prefix = 'P ';
	} elseif ( ! $pickup_allowed && $dropoff_allowed ) {
		$symbol_prefix = 'A ';
	}

	if ( $departure ) {
		$time_str = $departure;
	} elseif ( $arrival ) {
		$time_str = $arrival;
	} elseif ( $pickup_allowed && $dropoff_allowed ) {
		return array( '', 'X' );
	} else {
		$time_str = '';
	}

	if ( $time_str !== '' && $time_str !== 'X' ) {
		$time_str = MRT_format_time_display( $time_str );
	}

	return array( $symbol_prefix, $time_str );
}

function MRT_format_stop_time_display( ?array $stop_time ): string {
	if ( ! $stop_time ) {
		return '—';
	}

	$pickup_allowed  = ! empty( $stop_time['pickup_allowed'] );
	$dropoff_allowed = ! empty( $stop_time['dropoff_allowed'] );
	$stops_here      = $pickup_allowed || $dropoff_allowed;

	if ( ! $stops_here ) {
		return '|';
	}

	[$symbol_prefix, $time_str] = MRT_stop_time_prefix_and_time_parts( $stop_time );

	return $symbol_prefix . $time_str;
}
