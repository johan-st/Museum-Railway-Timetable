<?php
/**
 * Shared HTML for journey planner connections table (shortcode + AJAX)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Plain-text caption for the journey connections table
 *
 * @param string $from_name From station title
 * @param string $to_name To station title
 * @param string $selected_date Date Y-m-d
 * @param bool   $is_return     Return leg wording (AJAX return search)
 * @return string
 */
function MRT_journey_connections_table_caption( $from_name, $to_name, $selected_date, $is_return = false ) {
	$date_display = date_i18n( get_option( 'date_format' ), strtotime( $selected_date ) );

	if ( $is_return ) {
		return sprintf(
			/* translators: 1: departure station, 2: arrival station, 3: formatted date */
			__( 'Return train connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' ),
			$from_name,
			$to_name,
			$date_display
		);
	}

	return sprintf(
		/* translators: 1: departure station, 2: arrival station, 3: formatted date */
		__( 'Train connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' ),
		$from_name,
		$to_name,
		$date_display
	);
}

/**
 * Render connections table rows (caption + scoped headers)
 *
 * @param array<int, array<string, mixed>> $connections Planner table rows (flat connection or normalized row)
 * @param string                           $caption_text Accessible table caption (plain text)
 * @return void Outputs HTML
 */
function MRT_render_journey_connections_table( array $connections, string $caption_text ): void {
	?>
	<div class="mrt-journey-table-container mrt-overflow-x-auto">
		<table class="mrt-table mrt-journey-table mrt-mt-sm">
			<caption class="mrt-journey-table__caption"><?php echo esc_html( $caption_text ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Service', 'museum-railway-timetable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Train Type', 'museum-railway-timetable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Departure', 'museum-railway-timetable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Arrival', 'museum-railway-timetable' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Destination', 'museum-railway-timetable' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $connections as $conn ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $conn['service_name'] ); ?></strong>
							<?php if ( ! empty( $conn['route_name'] ) ) : ?>
								<br><small class="mrt-text-tertiary mrt-font-italic"><?php echo esc_html( $conn['route_name'] ); ?></small>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $conn['train_type'] ); ?></td>
						<td>
							<strong>
							<?php
								$dep = $conn['from_departure'] ?: $conn['from_arrival'];
								echo $dep ? esc_html( MRT_format_time_display( $dep ) ) : '—';
							?>
							</strong>
						</td>
						<td>
							<strong>
							<?php
								$arr = $conn['to_arrival'] ?: $conn['to_departure'];
								echo $arr ? esc_html( MRT_format_time_display( $arr ) ) : '—';
							?>
							</strong>
						</td>
						<td><?php echo esc_html( ! empty( $conn['destination'] ) ? $conn['destination'] : ( ! empty( $conn['direction'] ) ? $conn['direction'] : '—' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
