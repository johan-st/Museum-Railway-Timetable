<?php
/**
 * Admin Dashboard – Statistics, Routes Overview, Settings, Guide
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/admin-page/dashboard-stats.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-routes.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-quick-actions.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-guide.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-shortcodes.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-dev-tools.php';
require_once MRT_PATH . 'inc/admin-page/dashboard-prices.php';

/**
 * Sanitize plugin settings input
 *
 * @param array $input Raw input array
 * @return array Sanitized settings array
 */
function MRT_sanitize_settings( $input ) {
	return array(
		'enabled' => ! empty( $input['enabled'] ),
		'note'    => isset( $input['note'] ) ? sanitize_text_field( $input['note'] ) : '',
	);
}

/**
 * Render the enabled checkbox field
 */
function MRT_render_enabled_field() {
	$opts = get_option( 'mrt_settings' );
	echo '<input type="checkbox" name="mrt_settings[enabled]" value="1" ' . checked( ! empty( $opts['enabled'] ), true, false ) . ' />';
}

/**
 * Render the note text field
 */
function MRT_render_note_field() {
	$opts = get_option( 'mrt_settings' );
	echo '<input type="text" name="mrt_settings[note]" value="' . esc_attr( $opts['note'] ?? '' ) . '" class="regular-text" />';
}

/**
 * Get dashboard statistics
 *
 * @return array Stats array
 */
function MRT_get_dashboard_stats() {
	$train_types_count = wp_count_terms(
		array(
			'taxonomy'   => 'mrt_train_type',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $train_types_count ) ) {
		$train_types_count = 0;
	}
	return array(
		'stations_count'    => wp_count_posts( 'mrt_station' )->publish,
		'routes_count'      => wp_count_posts( 'mrt_route' )->publish,
		'timetables_count'  => wp_count_posts( 'mrt_timetable' )->publish,
		'services_count'    => wp_count_posts( 'mrt_service' )->publish,
		'train_types_count' => $train_types_count,
	);
}

/**
 * Render the main admin settings page (Dashboard)
 */
function MRT_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$stats      = MRT_get_dashboard_stats();
	$all_routes = get_posts(
		array(
			'post_type'      => 'mrt_route',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Museum Railway Timetable', 'museum-railway-timetable' ); ?></h1>

		<?php MRT_render_dashboard_stats( $stats ); ?>
		<?php MRT_render_dashboard_routes( $all_routes ); ?>
		<?php MRT_render_dashboard_quick_actions(); ?>

		<div class="mrt-section">
			<h2><?php esc_html_e( 'Settings', 'museum-railway-timetable' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'mrt_group' );
				do_settings_sections( 'mrt_settings' );
				submit_button();
				?>
			</form>
		</div>

		<?php MRT_render_dashboard_guide(); ?>
		<?php MRT_render_dashboard_shortcodes(); ?>
		<?php MRT_render_dashboard_dev_tools(); ?>
	</div>
	<?php
}
