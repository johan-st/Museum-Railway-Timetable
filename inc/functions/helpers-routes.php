<?php
/**
 * Route helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get end stations (start and end) for a route
 *
 * @param int $route_id Route post ID
 * @return array Array with 'start' and 'end' station IDs, or empty array if not set
 */
function MRT_get_route_end_stations( $route_id ) {
	$start = get_post_meta( $route_id, 'mrt_route_start_station', true );
	$end   = get_post_meta( $route_id, 'mrt_route_end_station', true );
	return array(
		'start' => $start ? intval( $start ) : 0,
		'end'   => $end ? intval( $end ) : 0,
	);
}

/**
 * Get route stations array, normalized to array format
 *
 * @param int $route_id Route post ID
 * @return array Array of station post IDs
 */
function MRT_get_route_stations( $route_id ) {
	if ( ! $route_id || $route_id <= 0 ) {
		return array();
	}

	$route_stations = get_post_meta( $route_id, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		return array();
	}

	return $route_stations;
}

/**
 * Calculate direction based on route and end station
 *
 * @param int $route_id Route post ID
 * @param int $end_station_id End station (destination) post ID
 * @return string 'dit' if going towards end station, 'från' if going from end station, or '' if cannot determine
 */
function MRT_calculate_direction_from_end_station( $route_id, $end_station_id ) {
	if ( ! $route_id || ! $end_station_id ) {
		return '';
	}

	$end_stations   = MRT_get_route_end_stations( $route_id );
	$route_stations = MRT_get_route_stations( $route_id );

	if ( empty( $route_stations ) ) {
		return '';
	}

	// If end station is the route's end station, direction is 'dit' (towards)
	if ( $end_stations['end'] == $end_station_id ) {
		return 'dit';
	}

	// If end station is the route's start station, direction is 'från' (from)
	if ( $end_stations['start'] == $end_station_id ) {
		return 'från';
	}

	// Check position in route stations array
	$end_station_index       = array_search( $end_station_id, $route_stations );
	$start_station_index     = array_search( $end_stations['start'], $route_stations );
	$route_end_station_index = array_search( $end_stations['end'], $route_stations );

	if ( $end_station_index === false ) {
		return '';
	}

	// If end station is closer to route's end station, direction is 'dit'
	if ( $route_end_station_index !== false && $end_station_index > $route_end_station_index ) {
		return 'dit';
	}

	// If end station is closer to route's start station, direction is 'från'
	if ( $start_station_index !== false && $end_station_index < $start_station_index ) {
		return 'från';
	}

	// Default: if end station is after middle point, assume 'dit', otherwise 'från'
	$middle = count( $route_stations ) / 2;
	return $end_station_index >= $middle ? 'dit' : 'från';
}

/**
 * Get route label from end stations
 * Helper function for MRT_get_route_label()
 *
 * @param int $route_id Route post ID
 * @param int $end_station_id End station post ID
 * @return string Route label or empty string if cannot determine
 */
function MRT_get_route_label_from_end_station( $route_id, $end_station_id ) {
	$end_station_post = get_post( $end_station_id );
	if ( ! $end_station_post ) {
		return '';
	}

	$end_stations     = MRT_get_route_end_stations( $route_id );
	$start_station_id = $end_stations['start'];
	$start_station    = $start_station_id ? get_post( $start_station_id ) : null;

	if ( $start_station ) {
		return sprintf(
			__( 'Från %1$s Till %2$s', 'museum-railway-timetable' ),
			$start_station->post_title,
			$end_station_post->post_title
		);
	}

	return sprintf( __( 'Route to %s', 'museum-railway-timetable' ), $end_station_post->post_title );
}

/**
 * Get route label from direction
 * Helper function for MRT_get_route_label()
 *
 * @param int    $route_id Route post ID
 * @param string $direction Direction ('dit' or 'från')
 * @param array  $station_posts Optional array of station posts (will be fetched if not provided)
 * @return string Route label or empty string if cannot determine
 */
function MRT_get_route_label_from_direction( $route_id, $direction, $station_posts = array() ) {
	if ( $direction !== 'dit' && $direction !== 'från' ) {
		return '';
	}

	// Fetch station posts if not provided
	if ( empty( $station_posts ) ) {
		$route_stations = MRT_get_route_stations( $route_id );
		if ( ! empty( $route_stations ) ) {
			$station_posts = get_posts(
				array(
					'post_type'      => 'mrt_station',
					'post__in'       => $route_stations,
					'posts_per_page' => -1,
					'orderby'        => 'post__in',
					'fields'         => 'all',
				)
			);
		}
	}

	if ( empty( $station_posts ) ) {
		return '';
	}

	$first_station = $station_posts[0];
	$last_station  = end( $station_posts );

	if ( $direction === 'dit' ) {
		return sprintf(
			__( 'Från %1$s Till %2$s', 'museum-railway-timetable' ),
			$first_station->post_title,
			$last_station->post_title
		);
	}

	return sprintf(
		__( 'Från %1$s Till %2$s', 'museum-railway-timetable' ),
		$last_station->post_title,
		$first_station->post_title
	);
}

/**
 * Get route label based on end stations or direction
 * Creates a human-readable label like "Från X Till Y" or "Route to Y"
 *
 * @param WP_Post $route Route post object
 * @param string  $direction Direction ('dit' or 'från')
 * @param array   $services_list Optional array of service data to check for end stations
 * @param array   $station_posts Optional array of station posts (for direction fallback)
 * @return string Route label
 */
function MRT_get_route_label( $route, $direction, $services_list = array(), $station_posts = array() ) {
	if ( ! $route ) {
		return '';
	}

	$route_id    = $route->ID;
	$route_label = $route->post_title;

	// Check if services have end stations set
	$end_station_ids = array();
	if ( ! empty( $services_list ) ) {
		foreach ( $services_list as $service_data ) {
			$service = is_array( $service_data ) && isset( $service_data['service'] )
				? $service_data['service']
				: ( is_object( $service_data ) ? $service_data : null );

			if ( ! $service ) {
				continue;
			}

			$end_station_id = get_post_meta( $service->ID, 'mrt_service_end_station_id', true );
			if ( $end_station_id ) {
				$end_station_ids[] = $end_station_id;
			}
		}
	}

	// Try to get label from end stations first
	if ( ! empty( $end_station_ids ) ) {
		$unique_end_stations = array_unique( $end_station_ids );
		if ( count( $unique_end_stations ) === 1 ) {
			$label = MRT_get_route_label_from_end_station( $route_id, reset( $unique_end_stations ) );
			if ( ! empty( $label ) ) {
				return $label;
			}
		}
	}

	// Fallback to direction-based label
	$label = MRT_get_route_label_from_direction( $route_id, $direction, $station_posts );
	if ( ! empty( $label ) ) {
		return $label;
	}

	return $route_label;
}
