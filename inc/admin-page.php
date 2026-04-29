<?php
/**
 * Admin Page – Menu, Settings, Dashboard
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/admin-page/dashboard.php';
require_once MRT_PATH . 'inc/admin-page/clear-db.php';
require_once MRT_PATH . 'inc/admin-page/admin-list.php';
require_once MRT_PATH . 'inc/demo-page.php';

/**
 * CPT and taxonomy links under the plugin menu.
 */
function MRT_register_admin_menu_cpt_submenus(): void {
	add_submenu_page(
		'mrt_settings',
		__( 'Timetables', 'museum-railway-timetable' ),
		__( 'Timetables', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_timetable'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Stations', 'museum-railway-timetable' ),
		__( 'Stations', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_station'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Routes', 'museum-railway-timetable' ),
		__( 'Routes', 'museum-railway-timetable' ),
		'edit_posts',
		'edit.php?post_type=mrt_route'
	);

	add_submenu_page(
		'mrt_settings',
		__( 'Train Types', 'museum-railway-timetable' ),
		__( 'Train Types', 'museum-railway-timetable' ),
		'manage_categories',
		'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service'
	);
}

/**
 * Optional component demo submenu + hook fallback.
 */
function MRT_register_admin_menu_demo_submenu(): void {
	$demo_slug = MRT_components_demo_menu_slug();
	add_submenu_page(
		'mrt_settings',
		__( 'Component demo page', 'museum-railway-timetable' ),
		__( 'Component demo page', 'museum-railway-timetable' ),
		'manage_options',
		$demo_slug,
		'MRT_render_components_demo_admin_page'
	);

	$demo_hook = get_plugin_page_hookname( $demo_slug, 'mrt_settings' );
	if ( is_string( $demo_hook ) && $demo_hook !== '' && ! has_action( $demo_hook ) ) {
		add_action( $demo_hook, 'MRT_render_components_demo_admin_page' );
	}
}

/**
 * Top-level menu and submenus.
 */
function MRT_register_admin_menus(): void {
	add_menu_page(
		__( 'Museum Railway Timetable', 'museum-railway-timetable' ),
		__( 'Railway Timetable', 'museum-railway-timetable' ),
		'manage_options',
		'mrt_settings',
		'MRT_render_admin_page',
		'dashicons-calendar-alt'
	);

	MRT_register_admin_menu_cpt_submenus();
	MRT_register_admin_menu_demo_submenu();
}

add_action( 'admin_menu', 'MRT_register_admin_menus' );

/**
 * General plugin settings fields.
 */
function MRT_register_settings_general(): void {
	register_setting(
		'mrt_group',
		'mrt_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'MRT_sanitize_settings',
			'default'           => array(
				'enabled' => true,
				'note'    => '',
			),
		)
	);

	add_settings_section(
		'mrt_main',
		__( 'General Settings', 'museum-railway-timetable' ),
		static function (): void {
			echo '<p>' . esc_html__( 'Configure timetable display.', 'museum-railway-timetable' ) . '</p>';
		},
		'mrt_settings'
	);

	add_settings_field(
		'mrt_enabled',
		__( 'Enable Plugin', 'museum-railway-timetable' ),
		'MRT_render_enabled_field',
		'mrt_settings',
		'mrt_main'
	);

	add_settings_field(
		'mrt_note',
		__( 'Note', 'museum-railway-timetable' ),
		'MRT_render_note_field',
		'mrt_settings',
		'mrt_main'
	);
}

/**
 * Price matrix settings (public journey).
 */
function MRT_register_settings_prices(): void {
	register_setting(
		'mrt_group',
		'mrt_price_matrix',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'MRT_sanitize_price_matrix',
			'default'           => array(),
		)
	);

	add_settings_section(
		'mrt_prices',
		__( 'Public journey — price matrix', 'museum-railway-timetable' ),
		static function (): void {
			echo '<p>' . esc_html__( 'Optional prices for passenger categories (display/API).', 'museum-railway-timetable' ) . '</p>';
		},
		'mrt_settings'
	);

	add_settings_field(
		'mrt_price_matrix',
		__( 'Prices (SEK)', 'museum-railway-timetable' ),
		'MRT_render_price_matrix_field',
		'mrt_settings',
		'mrt_prices'
	);
}

/**
 * Register settings API entries.
 */
function MRT_register_plugin_settings(): void {
	MRT_register_settings_general();
	MRT_register_settings_prices();
}

add_action( 'admin_init', 'MRT_register_plugin_settings' );
