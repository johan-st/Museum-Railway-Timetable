<?php
/**
 * CPT admin customizations (columns, title placeholders, labels)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

add_filter(
	'manage_edit-mrt_station_columns',
	function ( $columns ) {
		if ( isset( $columns['title'] ) ) {
			$columns['title'] = __( 'Station Name', 'museum-railway-timetable' );
		}
		return $columns;
	}
);

add_filter(
	'manage_edit-mrt_route_columns',
	function ( $columns ) {
		if ( isset( $columns['title'] ) ) {
			$columns['title'] = __( 'Route Name', 'museum-railway-timetable' );
		}
		return $columns;
	}
);

add_filter(
	'manage_edit-mrt_service_columns',
	function ( $columns ) {
		if ( isset( $columns['title'] ) ) {
			$columns['title'] = __( 'Trip Name', 'museum-railway-timetable' );
		}
		return $columns;
	}
);

add_filter(
	'manage_edit-mrt_timetable_columns',
	function ( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'title' ) {
				$new_columns['mrt_timetable_id'] = __( 'ID', 'museum-railway-timetable' );
			}
		}
		return $new_columns;
	}
);

add_action(
	'manage_mrt_timetable_posts_custom_column',
	function ( $column, $post_id ) {
		if ( $column === 'mrt_timetable_id' ) {
			echo '<code class="mrt-code-inline">' . esc_html( $post_id ) . '</code>';
		}
	},
	10,
	2
);

add_filter(
	'enter_title_here',
	function ( $title, $post ) {
		if ( ! $post ) {
			global $post_type;
			if ( $post_type === 'mrt_station' ) {
				return __( 'Enter station name', 'museum-railway-timetable' );
			} elseif ( $post_type === 'mrt_route' ) {
				return __( 'Enter route name', 'museum-railway-timetable' );
			} elseif ( $post_type === 'mrt_service' ) {
				return __( 'Trip name (auto-generated from Route + Direction)', 'museum-railway-timetable' );
			}
			return $title;
		}
		if ( $post->post_type === 'mrt_station' ) {
			return __( 'Enter station name', 'museum-railway-timetable' );
		} elseif ( $post->post_type === 'mrt_route' ) {
			return __( 'Enter route name', 'museum-railway-timetable' );
		} elseif ( $post->post_type === 'mrt_service' ) {
			return __( 'Trip name (auto-generated from Route + Direction)', 'museum-railway-timetable' );
		}
		return $title;
	},
	10,
	2
);

add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( $screen->post_type === 'mrt_station' ) {
			echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js( __( 'Station Name', 'museum-railway-timetable' ) ) . '");
        });
        </script>';
		} elseif ( $screen->post_type === 'mrt_route' ) {
			echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js( __( 'Route Name', 'museum-railway-timetable' ) ) . '");
        });
        </script>';
		} elseif ( $screen->post_type === 'mrt_service' ) {
			echo '<script>
        jQuery(document).ready(function($) {
            $("#titlewrap label").text("' . esc_js( __( 'Trip Name', 'museum-railway-timetable' ) ) . '");
        });
        </script>';
		}
	}
);
