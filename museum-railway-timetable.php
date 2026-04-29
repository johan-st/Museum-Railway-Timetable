<?php
/**
 * Plugin Name: Museum Railway Timetable
 * Description: A calendar displaying train timetables for a museum railway.
 * Version: 0.3.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Erik
 * Text Domain: museum-railway-timetable
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

define( 'MRT_VERSION', '0.3.0' );
define( 'MRT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MRT_URL', plugin_dir_url( __FILE__ ) );

require_once MRT_PATH . 'inc/constants.php';

// Load translations
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( MRT_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

// Activation & deactivation hooks
register_activation_hook( __FILE__, 'MRT_activate' );
register_deactivation_hook( __FILE__, 'MRT_deactivate' );

/**
 * Plugin activation hook
 * Creates custom database tables and sets default options
 */
function MRT_activate() {
	// Create custom DB tables and default options if needed
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	$stoptimes = $wpdb->prefix . 'mrt_stoptimes';
	$sql       = "CREATE TABLE $stoptimes (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_post_id BIGINT UNSIGNED NOT NULL,
        station_post_id BIGINT UNSIGNED NOT NULL,
        stop_sequence INT NOT NULL,
        arrival_time CHAR(5) NULL,
        departure_time CHAR(5) NULL,
        pickup_allowed TINYINT(1) DEFAULT 1,
        dropoff_allowed TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id),
        KEY service_seq (service_post_id, stop_sequence),
        KEY station (station_post_id)
    ) $charset;";

	dbDelta( $sql );

	if ( get_option( 'mrt_settings' ) === false ) {
		add_option( 'mrt_settings', array( 'enabled' => true ) );
	}
}

/**
 * Plugin deactivation hook
 * Keeps data on deactivation; uninstall.php will remove options
 */
function MRT_deactivate() {
	// No-op: keep data on deactivation; uninstall.php will remove options
}

// Load helper functions first
require_once MRT_PATH . 'inc/functions/helpers.php';
require_once MRT_PATH . 'inc/functions/services.php';
require_once MRT_PATH . 'inc/functions/journey-loader.php';
require_once MRT_PATH . 'inc/functions/timetable-view.php';

// Load assets (CSS/JS enqueuing)
require_once MRT_PATH . 'inc/assets.php';

// Admin and features
require_once MRT_PATH . 'inc/admin-page.php';
require_once MRT_PATH . 'inc/admin-meta-boxes.php';
require_once MRT_PATH . 'inc/admin-ajax.php';
require_once MRT_PATH . 'inc/import-lennakatten/loader.php';
require_once MRT_PATH . 'inc/cpt.php';
require_once MRT_PATH . 'inc/shortcodes.php';
