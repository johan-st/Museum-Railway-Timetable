<?php
/**
 * Station helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get station display name for timetable (with optional bus asterisk)
 *
 * @param WP_Post $station Station post object
 * @return string Display name, e.g. "Selknä*" if bus suffix is set
 */
function MRT_get_station_display_name( $station ) {
	if ( ! $station || $station->post_title === '' ) {
		return '';
	}
	$name       = $station->post_title;
	$bus_suffix = get_post_meta( $station->ID, 'mrt_station_bus_suffix', true );
	if ( $bus_suffix === '1' ) {
		$name .= '*';
	}
	return $name;
}

/**
 * Get all stations ordered by display order
 *
 * @return array Array of station post IDs
 */
function MRT_get_all_stations() {
	$q = new WP_Query(
		array(
			'post_type'      => 'mrt_station',
			'posts_per_page' => -1,
			'orderby'        => array(
				'meta_value_num' => 'ASC',
				'title'          => 'ASC',
			),
			'meta_key'       => 'mrt_display_order',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'nopaging'       => true,
		)
	);
	return $q->posts;
}
