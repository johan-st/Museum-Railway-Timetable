<?php
/**
 * Dashboard: Quick actions
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render quick actions section
 */
function MRT_render_dashboard_quick_actions() {
	?>
	<div class="mrt-section mrt-bg-info">
		<h2><?php esc_html_e( 'Quick Actions', 'museum-railway-timetable' ); ?></h2>
		<div class="mrt-grid mrt-grid-auto-250 mrt-mt-1">
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_station' ) ); ?>" class="button button-primary mrt-btn mrt-btn--action">
				<strong><?php esc_html_e( '➕ Add Station', 'museum-railway-timetable' ); ?></strong>
				<span><?php esc_html_e( 'Create a new station', 'museum-railway-timetable' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_route' ) ); ?>" class="button button-primary mrt-btn mrt-btn--action">
				<strong><?php esc_html_e( '➕ Add Route', 'museum-railway-timetable' ); ?></strong>
				<span><?php esc_html_e( 'Create a new route with stations', 'museum-railway-timetable' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mrt_timetable' ) ); ?>" class="button button-primary mrt-btn mrt-btn--action">
				<strong><?php esc_html_e( '➕ Add Timetable', 'museum-railway-timetable' ); ?></strong>
				<span><?php esc_html_e( 'Create a new timetable with dates', 'museum-railway-timetable' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_station&mrt_view=overview' ) ); ?>" class="button mrt-btn mrt-btn--action">
				<strong><?php esc_html_e( '📊 Stations Overview', 'museum-railway-timetable' ); ?></strong>
				<span><?php esc_html_e( 'View all stations with statistics', 'museum-railway-timetable' ); ?></span>
			</a>
		</div>
	</div>
	<?php
}
