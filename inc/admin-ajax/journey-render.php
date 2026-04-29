<?php
/**
 * HTML rendering for journey AJAX (legacy table output)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render journey search results as HTML (direct connections table)
 *
 * @param array<int, array<string, mixed>> $connections Planner table rows (flat or from MRT_journey_normalized_to_planner_row)
 * @param string                           $from_station_name From title
 * @param string                           $to_station_name To title
 * @param string                           $dateYmd Date Y-m-d
 * @param bool                             $is_return Return leg (to → from) heading
 * @return string HTML fragment
 */
function MRT_journey_render_search_results_html(
	$connections,
	$from_station_name,
	$to_station_name,
	$dateYmd,
	$is_return = false
) {
	ob_start();
	$date_display = date_i18n( get_option( 'date_format' ), strtotime( $dateYmd ) );
	$caption      = MRT_journey_connections_table_caption( $from_station_name, $to_station_name, $dateYmd, $is_return );
	?>
	<h3 id="mrt-journey-results-heading" class="mrt-heading mrt-heading--xl mrt-mb-1">
		<?php
		$fmt = $is_return
			? __( 'Return connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' )
			: __( 'Connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' );
		printf(
			esc_html( $fmt ),
			esc_html( $from_station_name ),
			esc_html( $to_station_name ),
			esc_html( $date_display )
		);
		?>
	</h3>
	<?php if ( empty( $connections ) ) : ?>
		<div class="mrt-alert mrt-alert-info mrt-empty" role="status">
			<p><strong><?php esc_html_e( 'No connections found.', 'museum-railway-timetable' ); ?></strong></p>
			<p><?php esc_html_e( 'There are no connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable' ); ?></p>
		</div>
	<?php else : ?>
		<?php MRT_render_journey_connections_table( $connections, $caption ); ?>
	<?php endif; ?>
	<?php
	return (string) ob_get_clean();
}
