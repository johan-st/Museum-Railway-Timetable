<?php
/**
 * Dashboard: Development tools (WP_DEBUG only)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render development tools section (Clear DB)
 */
function MRT_render_dashboard_dev_tools() {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	?>
	<div class="mrt-card mrt-card--warning mrt-mt-xl">
		<h2><?php esc_html_e( 'Development Tools', 'museum-railway-timetable' ); ?></h2>
		<p><?php esc_html_e( 'These tools are only available when WP_DEBUG is enabled.', 'museum-railway-timetable' ); ?></p>
		<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete ALL timetable data? This cannot be undone!', 'museum-railway-timetable' ) ); ?>');">
			<?php wp_nonce_field( 'mrt_clear_db', 'mrt_clear_db_nonce' ); ?>
			<input type="hidden" name="mrt_action" value="clear_db" />
			<p>
				<button type="submit" class="button button-secondary mrt-btn--danger">
					<?php esc_html_e( 'Clear All Timetable Data', 'museum-railway-timetable' ); ?>
				</button>
			</p>
			<p class="description">
				<?php esc_html_e( 'This will delete all Stations, Services, Routes, Timetables, and Stop Times. Use with caution!', 'museum-railway-timetable' ); ?>
			</p>
		</form>
	</div>
	<?php
}
