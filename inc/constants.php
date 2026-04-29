<?php

declare(strict_types=1);

/**
 * Plugin constants for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Text domain for translations */
define( 'MRT_TEXT_DOMAIN', 'museum-railway-timetable' );

/** Custom post types */
define( 'MRT_POST_TYPE_STATION', 'mrt_station' );
define( 'MRT_POST_TYPE_ROUTE', 'mrt_route' );
define( 'MRT_POST_TYPE_TIMETABLE', 'mrt_timetable' );
define( 'MRT_POST_TYPE_SERVICE', 'mrt_service' );

/** Taxonomies */
define( 'MRT_TAXONOMY_TRAIN_TYPE', 'mrt_train_type' );

/** All MRT post types (for iteration/validation) */
define(
	'MRT_POST_TYPES',
	array(
		MRT_POST_TYPE_STATION,
		MRT_POST_TYPE_ROUTE,
		MRT_POST_TYPE_TIMETABLE,
		MRT_POST_TYPE_SERVICE,
	)
);
