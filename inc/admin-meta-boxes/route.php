<?php
/**
 * Route meta box
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render related trips section for route meta box
 *
 * @param int $route_id Route post ID
 */
function MRT_render_route_related_services( $route_id ) {
	$services = get_posts(
		array(
			'post_type'      => 'mrt_service',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_route_id',
					'value'   => $route_id,
					'compare' => '=',
				),
			),
			'fields'         => 'all',
		)
	);
	if ( empty( $services ) ) {
		return;
	}
	$content  = '<p class="description">' . esc_html__( 'This route is used by the following trips:', 'museum-railway-timetable' ) . '</p>';
	$content .= '<ul class="mrt-list-indent">';
	foreach ( $services as $service ) {
		$timetable_id    = get_post_meta( $service->ID, 'mrt_service_timetable_id', true );
		$timetable_link  = $timetable_id ? get_edit_post_link( $timetable_id ) : '';
		$service_link    = $timetable_link ? add_query_arg( 'timetable_id', $timetable_id, get_edit_post_link( $service->ID ) ) : get_edit_post_link( $service->ID );
		$timetable       = $timetable_id ? get_post( $timetable_id ) : null;
		$timetable_label = $timetable ? ( $timetable->post_title ?: __( 'Timetable', 'museum-railway-timetable' ) . ' #' . $timetable_id ) : '';
		$content        .= '<li><a href="' . esc_url( $service_link ) . '">' . esc_html( $service->post_title ) . '</a>';
		if ( $timetable_link && $timetable_label ) {
			$content .= ' <span class="description">(' . esc_html__( 'in', 'museum-railway-timetable' ) . ' <a href="' . esc_url( $timetable_link ) . '">' . esc_html( $timetable_label ) . '</a>)</span>';
		}
		$content .= '</li>';
	}
	$content .= '</ul>';
	MRT_render_info_box( __( 'Related Trips:', 'museum-railway-timetable' ), $content, 'mrt-mt-1' );
}

/**
 * Render route info/help box
 */
function MRT_render_route_info_box() {
	$content  = '<p>' . esc_html__( 'A route defines which stations trains travel between and in what order. When you create a trip (service), you select a route and a destination station, and all stations on that route become available for configuring stop times.', 'museum-railway-timetable' ) . '</p>';
	$content .= '<div class="mrt-mt-sm"><p><strong>' . esc_html__( 'How to use:', 'museum-railway-timetable' ) . '</strong></p>';
	$content .= '<ol class="mrt-list-indent">';
	$content .= '<li>' . esc_html__( 'Give your route a descriptive name, e.g., "Hultsfred → Västervik" or "Main Line"', 'museum-railway-timetable' ) . '</li>';
	$content .= '<li>' . esc_html__( 'Set the start and end stations (terminus) for this route below', 'museum-railway-timetable' ) . '</li>';
	$content .= '<li>' . esc_html__( 'Add stations in the order they appear on the route using the dropdown below', 'museum-railway-timetable' ) . '</li>';
	$content .= '<li>' . esc_html__( 'Use ↑ ↓ buttons to reorder stations if needed', 'museum-railway-timetable' ) . '</li>';
	$content .= '<li>' . esc_html__( 'When creating a trip in a Timetable, select this route and choose a destination station', 'museum-railway-timetable' ) . '</li>';
	$content .= '</ol></div>';
	MRT_render_info_box( __( '💡 What is a Route?', 'museum-railway-timetable' ), $content );
}

/**
 * Render route end stations (terminus) section
 *
 * @param array $all_stations All station posts
 * @param mixed $route_start_station Start station ID
 * @param mixed $route_end_station End station ID
 */
function MRT_render_route_end_stations_section( $all_stations, $route_start_station, $route_end_station ) {
	?>
	<div class="mrt-box">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'End Stations (Terminus)', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Define the start and end stations for this route. These are the terminus stations where trains can start or end their journey.', 'museum-railway-timetable' ); ?></p>
		<table class="form-table mrt-mt-sm">
			<tr>
				<th class="mrt-w-150"><label for="mrt-route-start-station"><?php esc_html_e( 'Start Station', 'museum-railway-timetable' ); ?></label></th>
				<td>
					<select name="mrt_route_start_station" id="mrt-route-start-station" class="mrt-input mrt-input--meta">
						<option value=""><?php esc_html_e( '— Select Start Station —', 'museum-railway-timetable' ); ?></option>
						<?php foreach ( $all_stations as $station ) : ?>
							<option value="<?php echo esc_attr( $station->ID ); ?>" <?php selected( $route_start_station, $station->ID ); ?>><?php echo esc_html( $station->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'The starting point (origin) of this route.', 'museum-railway-timetable' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="mrt-route-end-station"><?php esc_html_e( 'End Station', 'museum-railway-timetable' ); ?></label></th>
				<td>
					<select name="mrt_route_end_station" id="mrt-route-end-station" class="mrt-input mrt-input--meta">
						<option value=""><?php esc_html_e( '— Select End Station —', 'museum-railway-timetable' ); ?></option>
						<?php foreach ( $all_stations as $station ) : ?>
							<option value="<?php echo esc_attr( $station->ID ); ?>" <?php selected( $route_end_station, $station->ID ); ?>><?php echo esc_html( $station->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'The ending point (destination/terminus) of this route.', 'museum-railway-timetable' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Render route stations table (stations on route)
 *
 * @param array $route_stations Array of station IDs
 * @param array $all_stations All station posts
 */
function MRT_render_route_stations_table( $route_stations, $all_stations ) {
	?>
	<div id="mrt-route-stations-container" class="mrt-box mrt-mt-1">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Stations on Route', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Add stations in the order they appear on the route. Use ↑ ↓ to reorder.', 'museum-railway-timetable' ); ?></p>
		<table class="widefat striped" id="mrt-route-stations-table">
			<thead>
				<tr>
					<th class="mrt-w-60"><?php esc_html_e( 'Order', 'museum-railway-timetable' ); ?></th>
					<th><?php esc_html_e( 'Station', 'museum-railway-timetable' ); ?></th>
					<th class="mrt-w-200"><?php esc_html_e( 'Actions', 'museum-railway-timetable' ); ?></th>
				</tr>
			</thead>
			<tbody id="mrt-route-stations-tbody">
				<?php if ( ! empty( $route_stations ) ) : ?>
					<?php
					foreach ( $route_stations as $index => $station_id ) :
						$station = get_post( $station_id );
						if ( ! $station ) {
							continue;
						}
						?>
						<tr class="mrt-row-hover" data-station-id="<?php echo esc_attr( $station_id ); ?>">
							<td><?php echo esc_html( (string) ( $index + 1 ) ); ?></td>
							<td><?php echo esc_html( $station->post_title ); ?></td>
							<td>
								<button type="button" class="button button-small mrt-move-route-station-up" data-station-id="<?php echo esc_attr( $station_id ); ?>" title="<?php esc_attr_e( 'Move up', 'museum-railway-timetable' ); ?>" <?php echo $index === 0 ? 'disabled' : ''; ?>>↑</button>
								<button type="button" class="button button-small mrt-move-route-station-down" data-station-id="<?php echo esc_attr( $station_id ); ?>" title="<?php esc_attr_e( 'Move down', 'museum-railway-timetable' ); ?>" <?php echo $index === count( $route_stations ) - 1 ? 'disabled' : ''; ?>>↓</button>
								<button type="button" class="button button-small mrt-remove-route-station" data-station-id="<?php echo esc_attr( $station_id ); ?>"><?php esc_html_e( 'Remove', 'museum-railway-timetable' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				<tr class="mrt-new-route-station-row mrt-new-row">
					<td><?php echo esc_html( (string) ( count( $route_stations ) + 1 ) ); ?></td>
					<td>
						<select id="mrt-new-route-station" class="mrt-input mrt-input--meta">
							<option value=""><?php esc_html_e( '— Select Station —', 'museum-railway-timetable' ); ?></option>
							<?php foreach ( $all_stations as $station ) : ?>
								<option value="<?php echo esc_attr( $station->ID ); ?>" <?php selected( in_array( $station->ID, $route_stations ) ); ?>><?php echo esc_html( $station->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<button type="button" class="button button-primary button-small" id="mrt-add-route-station"><?php esc_html_e( 'Add', 'museum-railway-timetable' ); ?></button>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="mrt_route_stations" id="mrt_route_stations" value="<?php echo esc_attr( implode( ',', $route_stations ) ); ?>" />
	</div>
	<?php
}

/**
 * Render route meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_route_meta_box( $post ) {
	wp_nonce_field( 'mrt_save_route_meta', 'mrt_route_meta_nonce' );

	$route_stations = get_post_meta( $post->ID, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		$route_stations = array();
	}
	$route_start_station = get_post_meta( $post->ID, 'mrt_route_start_station', true );
	$route_end_station   = get_post_meta( $post->ID, 'mrt_route_end_station', true );
	$all_stations        = get_posts(
		array(
			'post_type'      => 'mrt_station',
			'posts_per_page' => -1,
			'orderby'        => array(
				'meta_value_num' => 'ASC',
				'title'          => 'ASC',
			),
			'meta_key'       => 'mrt_display_order',
			'fields'         => 'all',
		)
	);

	MRT_render_route_info_box();
	MRT_render_route_end_stations_section( $all_stations, $route_start_station, $route_end_station );
	MRT_render_route_stations_table( $route_stations, $all_stations );
	MRT_render_route_related_services( $post->ID );
}

/**
 * Save route meta box data
 *
 * @param int $post_id Post ID
 */
add_action(
	'save_post_mrt_route',
	function ( $post_id ) {
		if ( ! MRT_verify_meta_box_save( $post_id, 'mrt_route_meta_nonce', 'mrt_save_route_meta' ) ) {
			return;
		}

		// Save route stations
		if ( isset( $_POST['mrt_route_stations'] ) ) {
			$stations_str = sanitize_text_field( $_POST['mrt_route_stations'] );
			$stations     = array_filter( array_map( 'intval', explode( ',', $stations_str ) ) );
			update_post_meta( $post_id, 'mrt_route_stations', array_values( $stations ) );
		}

		// Save end stations
		if ( isset( $_POST['mrt_route_start_station'] ) ) {
			$start_station = intval( $_POST['mrt_route_start_station'] );
			update_post_meta( $post_id, 'mrt_route_start_station', $start_station > 0 ? $start_station : '' );
		}

		if ( isset( $_POST['mrt_route_end_station'] ) ) {
			$end_station = intval( $_POST['mrt_route_end_station'] );
			update_post_meta( $post_id, 'mrt_route_end_station', $end_station > 0 ? $end_station : '' );
		}
	}
);
