<?php
/**
 * Timetable overview meta box
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render timetable overview preview box
 *
 * @param WP_Post $post Current post object (Timetable)
 */
function MRT_render_timetable_overview_box( $post ) {
	?>
	<div class="mrt-box mrt-timetable-overview-preview">
		<p class="description">
			<?php esc_html_e( 'Preview of how the timetable will look when displayed. Services are grouped by route and destination.', 'museum-railway-timetable' ); ?>
		</p>
		<?php echo MRT_render_timetable_overview( $post->ID ); ?>
	</div>
	<?php
}
