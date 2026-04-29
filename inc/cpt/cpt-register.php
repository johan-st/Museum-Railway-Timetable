<?php

declare(strict_types=1);

/**
 * Register custom post types and taxonomies
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function (): void {
		MRT_register_station_post_type();
		MRT_register_route_post_type();
		MRT_register_timetable_post_type();
		MRT_register_service_post_type();
		MRT_register_train_type_taxonomy();
	}
);

function MRT_register_station_post_type(): void {
	register_post_type(
		MRT_POST_TYPE_STATION,
		array(
			'labels'       => array(
				'name'                  => __( 'Stations', MRT_TEXT_DOMAIN ),
				'singular_name'         => __( 'Station', MRT_TEXT_DOMAIN ),
				'add_new'               => __( 'Add New', MRT_TEXT_DOMAIN ),
				'add_new_item'          => __( 'Add New Station', MRT_TEXT_DOMAIN ),
				'edit_item'             => __( 'Edit Station', MRT_TEXT_DOMAIN ),
				'new_item'              => __( 'New Station', MRT_TEXT_DOMAIN ),
				'view_item'             => __( 'View Station', MRT_TEXT_DOMAIN ),
				'view_items'            => __( 'View Stations', MRT_TEXT_DOMAIN ),
				'all_items'             => __( 'All Stations', MRT_TEXT_DOMAIN ),
				'search_items'          => __( 'Search Stations', MRT_TEXT_DOMAIN ),
				'not_found'             => __( 'No stations found', MRT_TEXT_DOMAIN ),
				'not_found_in_trash'    => __( 'No stations found in Trash', MRT_TEXT_DOMAIN ),
				'parent_item_colon'     => __( 'Parent Station:', MRT_TEXT_DOMAIN ),
				'archives'              => __( 'Station Archives', MRT_TEXT_DOMAIN ),
				'attributes'            => __( 'Station Attributes', MRT_TEXT_DOMAIN ),
				'insert_into_item'      => __( 'Insert into station', MRT_TEXT_DOMAIN ),
				'uploaded_to_this_item' => __( 'Uploaded to this station', MRT_TEXT_DOMAIN ),
				'filter_items_list'     => __( 'Filter stations list', MRT_TEXT_DOMAIN ),
				'items_list_navigation' => __( 'Stations list navigation', MRT_TEXT_DOMAIN ),
				'items_list'            => __( 'Stations list', MRT_TEXT_DOMAIN ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_menu' => false,
			'menu_icon'    => 'dashicons-location',
			'supports'     => array( 'title' ),
			'show_in_rest' => false,
		)
	);
}

function MRT_register_route_post_type(): void {
	register_post_type(
		MRT_POST_TYPE_ROUTE,
		array(
			'labels'       => array(
				'name'                  => __( 'Routes', MRT_TEXT_DOMAIN ),
				'singular_name'         => __( 'Route', MRT_TEXT_DOMAIN ),
				'add_new'               => __( 'Add New', MRT_TEXT_DOMAIN ),
				'add_new_item'          => __( 'Add New Route', MRT_TEXT_DOMAIN ),
				'edit_item'             => __( 'Edit Route', MRT_TEXT_DOMAIN ),
				'new_item'              => __( 'New Route', MRT_TEXT_DOMAIN ),
				'view_item'             => __( 'View Route', MRT_TEXT_DOMAIN ),
				'view_items'            => __( 'View Routes', MRT_TEXT_DOMAIN ),
				'all_items'             => __( 'All Routes', MRT_TEXT_DOMAIN ),
				'search_items'          => __( 'Search Routes', MRT_TEXT_DOMAIN ),
				'not_found'             => __( 'No routes found', MRT_TEXT_DOMAIN ),
				'not_found_in_trash'    => __( 'No routes found in Trash', MRT_TEXT_DOMAIN ),
				'parent_item_colon'     => __( 'Parent Route:', MRT_TEXT_DOMAIN ),
				'archives'              => __( 'Route Archives', MRT_TEXT_DOMAIN ),
				'attributes'            => __( 'Route Attributes', MRT_TEXT_DOMAIN ),
				'insert_into_item'      => __( 'Insert into route', MRT_TEXT_DOMAIN ),
				'uploaded_to_this_item' => __( 'Uploaded to this route', MRT_TEXT_DOMAIN ),
				'filter_items_list'     => __( 'Filter routes list', MRT_TEXT_DOMAIN ),
				'items_list_navigation' => __( 'Routes list navigation', MRT_TEXT_DOMAIN ),
				'items_list'            => __( 'Routes list', MRT_TEXT_DOMAIN ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_menu' => false,
			'menu_icon'    => 'dashicons-randomize',
			'supports'     => array( 'title' ),
			'show_in_rest' => true,
		)
	);
}

function MRT_register_timetable_post_type() {
	register_post_type(
		'mrt_timetable',
		array(
			'labels'       => array(
				'name'                  => __( 'Timetables', MRT_TEXT_DOMAIN ),
				'singular_name'         => __( 'Timetable', MRT_TEXT_DOMAIN ),
				'add_new'               => __( 'Add New', MRT_TEXT_DOMAIN ),
				'add_new_item'          => __( 'Add New Timetable', MRT_TEXT_DOMAIN ),
				'edit_item'             => __( 'Edit Timetable', MRT_TEXT_DOMAIN ),
				'new_item'              => __( 'New Timetable', MRT_TEXT_DOMAIN ),
				'view_item'             => __( 'View Timetable', MRT_TEXT_DOMAIN ),
				'view_items'            => __( 'View Timetables', MRT_TEXT_DOMAIN ),
				'all_items'             => __( 'All Timetables', MRT_TEXT_DOMAIN ),
				'search_items'          => __( 'Search Timetables', MRT_TEXT_DOMAIN ),
				'not_found'             => __( 'No timetables found', MRT_TEXT_DOMAIN ),
				'not_found_in_trash'    => __( 'No timetables found in Trash', MRT_TEXT_DOMAIN ),
				'parent_item_colon'     => __( 'Parent Timetable:', MRT_TEXT_DOMAIN ),
				'archives'              => __( 'Timetable Archives', MRT_TEXT_DOMAIN ),
				'attributes'            => __( 'Timetable Attributes', MRT_TEXT_DOMAIN ),
				'insert_into_item'      => __( 'Insert into timetable', MRT_TEXT_DOMAIN ),
				'uploaded_to_this_item' => __( 'Uploaded to this timetable', MRT_TEXT_DOMAIN ),
				'filter_items_list'     => __( 'Filter timetables list', MRT_TEXT_DOMAIN ),
				'items_list_navigation' => __( 'Timetables list navigation', MRT_TEXT_DOMAIN ),
				'items_list'            => __( 'Timetables list', MRT_TEXT_DOMAIN ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_menu' => false,
			'menu_icon'    => 'dashicons-calendar-alt',
			'supports'     => array(),
			'show_in_rest' => false,
		)
	);
}

function MRT_register_service_post_type(): void {
	register_post_type(
		MRT_POST_TYPE_SERVICE,
		array(
			'labels'       => array(
				'name'                  => __( 'Services', MRT_TEXT_DOMAIN ),
				'singular_name'         => __( 'Service', MRT_TEXT_DOMAIN ),
				'add_new'               => __( 'Add New', MRT_TEXT_DOMAIN ),
				'add_new_item'          => __( 'Add New Trip', MRT_TEXT_DOMAIN ),
				'edit_item'             => __( 'Edit Trip', MRT_TEXT_DOMAIN ),
				'new_item'              => __( 'New Trip', MRT_TEXT_DOMAIN ),
				'view_item'             => __( 'View Trip', MRT_TEXT_DOMAIN ),
				'view_items'            => __( 'View Trips', MRT_TEXT_DOMAIN ),
				'all_items'             => __( 'All Trips', MRT_TEXT_DOMAIN ),
				'search_items'          => __( 'Search Trips', MRT_TEXT_DOMAIN ),
				'not_found'             => __( 'No trips found', MRT_TEXT_DOMAIN ),
				'not_found_in_trash'    => __( 'No trips found in Trash', MRT_TEXT_DOMAIN ),
				'parent_item_colon'     => __( 'Parent Trip:', MRT_TEXT_DOMAIN ),
				'archives'              => __( 'Trip Archives', MRT_TEXT_DOMAIN ),
				'attributes'            => __( 'Trip Attributes', MRT_TEXT_DOMAIN ),
				'insert_into_item'      => __( 'Insert into trip', MRT_TEXT_DOMAIN ),
				'uploaded_to_this_item' => __( 'Uploaded to this trip', MRT_TEXT_DOMAIN ),
				'filter_items_list'     => __( 'Filter trips list', MRT_TEXT_DOMAIN ),
				'items_list_navigation' => __( 'Trips list navigation', MRT_TEXT_DOMAIN ),
				'items_list'            => __( 'Trips list', MRT_TEXT_DOMAIN ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_menu' => false,
			'menu_icon'    => 'dashicons-clock',
			'supports'     => array( 'title' ),
			'show_in_rest' => false,
		)
	);
}

function MRT_register_train_type_taxonomy(): void {
	register_taxonomy(
		MRT_TAXONOMY_TRAIN_TYPE,
		MRT_POST_TYPE_SERVICE,
		array(
			'labels'       => array(
				'name'                       => __( 'Train Types', MRT_TEXT_DOMAIN ),
				'singular_name'              => __( 'Train Type', MRT_TEXT_DOMAIN ),
				'add_new_item'               => __( 'Add New Train Type', MRT_TEXT_DOMAIN ),
				'edit_item'                  => __( 'Edit Train Type', MRT_TEXT_DOMAIN ),
				'update_item'                => __( 'Update Train Type', MRT_TEXT_DOMAIN ),
				'new_item_name'              => __( 'New Train Type Name', MRT_TEXT_DOMAIN ),
				'search_items'               => __( 'Search Train Types', MRT_TEXT_DOMAIN ),
				'popular_items'              => __( 'Popular Train Types', MRT_TEXT_DOMAIN ),
				'all_items'                  => __( 'All Train Types', MRT_TEXT_DOMAIN ),
				'separate_items_with_commas' => __( 'Separate train types with commas', MRT_TEXT_DOMAIN ),
				'add_or_remove_items'        => __( 'Add or remove train types', MRT_TEXT_DOMAIN ),
				'choose_from_most_used'      => __( 'Choose from the most used train types', MRT_TEXT_DOMAIN ),
				'not_found'                  => __( 'No train types found', MRT_TEXT_DOMAIN ),
				'no_terms'                   => __( 'No train types', MRT_TEXT_DOMAIN ),
				'items_list_navigation'      => __( 'Train types list navigation', MRT_TEXT_DOMAIN ),
				'items_list'                 => __( 'Train types list', MRT_TEXT_DOMAIN ),
				'back_to_items'              => __( '← Back to Train Types', MRT_TEXT_DOMAIN ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => false,
		)
	);
}
