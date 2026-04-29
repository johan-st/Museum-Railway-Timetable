<?php
/**
 * Shortcode: Journey Planner [museum_journey_planner]
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render journey planner form
 *
 * @param array  $stations Station IDs
 * @param int    $from_station_id Selected from station
 * @param int    $to_station_id Selected to station
 * @param string $selected_date Selected date
 */
function MRT_render_journey_form( $stations, $from_station_id, $to_station_id, $selected_date ) {
	?>
	<form class="mrt-box mrt-journey-form mrt-border-none" method="get" action="" data-ajax-enabled="true">
		<div class="mrt-form-fields">
			<div class="mrt-form-field">
				<label for="mrt_from"><?php esc_html_e( 'From', 'museum-railway-timetable' ); ?></label>
				<select name="mrt_from" id="mrt_from" required>
					<option value=""><?php esc_html_e( 'Select station', 'museum-railway-timetable' ); ?></option>
					<?php foreach ( $stations as $station_id ) : ?>
						<option value="<?php echo esc_attr( $station_id ); ?>" <?php selected( $from_station_id, $station_id ); ?>>
							<?php echo esc_html( get_the_title( $station_id ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mrt-form-field">
				<label for="mrt_to"><?php esc_html_e( 'To', 'museum-railway-timetable' ); ?></label>
				<select name="mrt_to" id="mrt_to" required>
					<option value=""><?php esc_html_e( 'Select station', 'museum-railway-timetable' ); ?></option>
					<?php foreach ( $stations as $station_id ) : ?>
						<option value="<?php echo esc_attr( $station_id ); ?>" <?php selected( $to_station_id, $station_id ); ?>>
							<?php echo esc_html( get_the_title( $station_id ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mrt-form-field">
				<label for="mrt_date"><?php esc_html_e( 'Date', 'museum-railway-timetable' ); ?></label>
				<input type="date" name="mrt_date" id="mrt_date" value="<?php echo esc_attr( $selected_date ); ?>" required>
			</div>
			<div class="mrt-form-field">
				<button type="submit" class="mrt-btn mrt-btn--primary mrt-journey-search"><?php esc_html_e( 'Search', 'museum-railway-timetable' ); ?></button>
			</div>
		</div>
	</form>
	<?php
}

/**
 * Render journey planner shortcode output
 *
 * @param array $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_journey( $atts ) {
	$atts         = shortcode_atts( array( 'default_date' => '' ), $atts, 'museum_journey_planner' );
	$datetime     = MRT_get_current_datetime();
	$default_date = ! empty( $atts['default_date'] ) && MRT_validate_date( $atts['default_date'] )
		? $atts['default_date']
		: $datetime['date'];

	$from_station_id = isset( $_GET['mrt_from'] ) ? intval( $_GET['mrt_from'] ) : 0;
	$to_station_id   = isset( $_GET['mrt_to'] ) ? intval( $_GET['mrt_to'] ) : 0;
	$selected_date   = isset( $_GET['mrt_date'] ) && MRT_validate_date( $_GET['mrt_date'] )
		? sanitize_text_field( $_GET['mrt_date'] )
		: $default_date;
	$stations        = MRT_get_all_stations();
	$uid             = wp_unique_id( 'mrtjp' );

	ob_start();
	?>
	<div class="mrt-journey-planner mrt-my-lg" id="<?php echo esc_attr( $uid ); ?>-root" role="region" aria-label="<?php esc_attr_e( 'Journey planner', 'museum-railway-timetable' ); ?>">
		<?php MRT_render_journey_form( $stations, $from_station_id, $to_station_id, $selected_date ); ?>
		<?php MRT_render_journey_results( $from_station_id, $to_station_id, $selected_date, $uid ); ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render journey results title
 */
function MRT_render_journey_results_title( $from_name, $to_name, $selected_date ) {
	printf(
		esc_html__( 'Connections from %1$s to %2$s on %3$s', 'museum-railway-timetable' ),
		esc_html( $from_name ),
		esc_html( $to_name ),
		esc_html( date_i18n( get_option( 'date_format' ), strtotime( $selected_date ) ) )
	);
}

/**
 * Render journey search results section
 *
 * @param int    $from_station_id From station ID
 * @param int    $to_station_id To station ID
 * @param string $selected_date Date YYYY-MM-DD
 * @param string $uid Unique id prefix for this planner instance
 */
function MRT_render_journey_results( $from_station_id, $to_station_id, $selected_date, $uid ) {
	$results_id = $uid . '-results';
	if ( $from_station_id > 0 && $to_station_id > 0 && $from_station_id === $to_station_id ) {
		?>
		<div class="mrt-journey-results mrt-mt-lg" id="<?php echo esc_attr( $results_id ); ?>" role="region" aria-live="polite" aria-relevant="additions text" aria-busy="false" aria-label="<?php esc_attr_e( 'Connection search results', 'museum-railway-timetable' ); ?>">
			<?php echo MRT_render_alert( __( 'Please select different stations for departure and arrival.', 'museum-railway-timetable' ), 'error' ); ?>
		</div>
		<?php
		return;
	}
	if ( $from_station_id <= 0 || $to_station_id <= 0 ) {
		?>
		<div class="mrt-journey-results mrt-mt-lg mrt-journey-results--empty" id="<?php echo esc_attr( $results_id ); ?>" role="region" aria-live="polite" aria-relevant="additions text" aria-busy="false" aria-label="<?php esc_attr_e( 'Connection search results', 'museum-railway-timetable' ); ?>"></div>
		<?php
		return;
	}

	$bundle           = MRT_journey_single_trip_normalized_and_planner_rows( $from_station_id, $to_station_id, $selected_date );
	$planner_rows     = $bundle['planner_rows'];
	$from_name        = get_the_title( $from_station_id );
	$to_name          = get_the_title( $to_station_id );
	$services_on_date = MRT_services_running_on_date( $selected_date );
	$caption          = MRT_journey_connections_table_caption( $from_name, $to_name, $selected_date );
	?>
	<div class="mrt-journey-results mrt-mt-lg" id="<?php echo esc_attr( $results_id ); ?>" role="region" aria-live="polite" aria-relevant="additions text" aria-busy="false" aria-label="<?php esc_attr_e( 'Connection search results', 'museum-railway-timetable' ); ?>">
		<h3 id="mrt-journey-results-heading" class="mrt-heading mrt-heading--xl mrt-mb-1"><?php MRT_render_journey_results_title( $from_name, $to_name, $selected_date ); ?></h3>
		<?php if ( empty( $services_on_date ) ) : ?>
			<div class="mrt-alert mrt-alert-error" role="alert">
				<p><strong><?php esc_html_e( 'No services running.', 'museum-railway-timetable' ); ?></strong></p>
				<p><?php esc_html_e( 'There are no services running on the selected date. Please try a different date.', 'museum-railway-timetable' ); ?></p>
			</div>
		<?php elseif ( empty( $planner_rows ) ) : ?>
			<div class="mrt-alert mrt-alert-info mrt-empty" role="status">
				<p><strong><?php esc_html_e( 'No connections found.', 'museum-railway-timetable' ); ?></strong></p>
				<p><?php esc_html_e( 'There are no connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable' ); ?></p>
			</div>
		<?php else : ?>
			<?php MRT_render_journey_connections_table( $planner_rows, $caption ); ?>
		<?php endif; ?>
	</div>
	<?php
}
