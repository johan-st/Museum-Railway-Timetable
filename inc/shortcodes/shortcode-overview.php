<?php
/**
 * Shortcode: Timetable Overview [museum_timetable_overview]
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render timetable overview shortcode output
 *
 * @param array $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_overview( $atts ) {
	$atts = shortcode_atts(
		array(
			'timetable_id' => '',
			'timetable'    => '',
		),
		$atts,
		'museum_timetable_overview'
	);

	$timetable_id = intval( $atts['timetable_id'] );
	if ( ! $timetable_id && ! empty( $atts['timetable'] ) ) {
		$timetable_post = MRT_get_post_by_title( $atts['timetable'], 'mrt_timetable' );
		if ( $timetable_post ) {
			$timetable_id = intval( $timetable_post->ID );
		}
	}

	if ( ! $timetable_id || $timetable_id <= 0 ) {
		return MRT_render_alert( __( 'Timetable not found.', 'museum-railway-timetable' ), 'error' );
	}

	return MRT_render_timetable_overview( $timetable_id );
}
