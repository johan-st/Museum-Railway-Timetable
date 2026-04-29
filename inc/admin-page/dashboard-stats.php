<?php
/**
 * Dashboard: Statistics cards
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render dashboard statistics cards
 *
 * @param array $stats Stats array with keys: stations_count, routes_count, timetables_count, services_count, train_types_count
 */
function MRT_render_dashboard_stats( $stats ) {
	?>
	<div class="mrt-grid mrt-grid-auto mrt-my-lg">
		<div class="mrt-card mrt-card--center">
			<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( $stats['stations_count'] ); ?></div>
			<div class="mrt-text-muted mrt-mt-sm">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_station' ) ); ?>">
					<?php esc_html_e( 'Stations', 'museum-railway-timetable' ); ?>
				</a>
			</div>
		</div>
		<div class="mrt-card mrt-card--center">
			<div class="mrt-stat-number"><?php echo esc_html( $stats['routes_count'] ); ?></div>
			<div class="mrt-stat-label mrt-mt-sm">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_route' ) ); ?>">
					<?php esc_html_e( 'Routes', 'museum-railway-timetable' ); ?>
				</a>
			</div>
		</div>
		<div class="mrt-card mrt-card--center">
			<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( $stats['timetables_count'] ); ?></div>
			<div class="mrt-text-muted mrt-mt-sm">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_timetable' ) ); ?>">
					<?php esc_html_e( 'Timetables', 'museum-railway-timetable' ); ?>
				</a>
			</div>
		</div>
		<div class="mrt-card mrt-card--center">
			<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( $stats['services_count'] ); ?></div>
			<div class="mrt-text-muted mrt-mt-sm">
				<?php esc_html_e( 'Trips (Services)', 'museum-railway-timetable' ); ?>
				<span class="mrt-block mrt-text-small mrt-mt-xs mrt-opacity-85">
					<?php esc_html_e( 'Managed via Timetables', 'museum-railway-timetable' ); ?>
				</span>
			</div>
		</div>
		<div class="mrt-card mrt-card--center">
			<div class="mrt-text-2xl mrt-font-bold mrt-text-link"><?php echo esc_html( $stats['train_types_count'] ); ?></div>
			<div class="mrt-text-muted mrt-mt-sm">
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service' ) ); ?>">
					<?php esc_html_e( 'Train Types', 'museum-railway-timetable' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}
