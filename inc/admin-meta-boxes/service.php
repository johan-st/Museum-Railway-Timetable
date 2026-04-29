<?php
/**
 * Service meta box
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Setup hooks when editing service from timetable context
 *
 * @param int $timetable_id Timetable ID
 */
function MRT_service_meta_box_setup_editing_from_timetable( $timetable_id ) {
	add_filter(
		'admin_body_class',
		function ( $classes ) {
			global $post_type;
			if ( $post_type === 'mrt_service' && isset( $_GET['timetable_id'] ) ) {
				$classes .= ' mrt-editing-from-timetable';
			}
			return $classes;
		}
	);
	add_action(
		'edit_form_top',
		function () use ( $timetable_id ) {
			global $post_type;
			if ( $post_type === 'mrt_service' && isset( $_GET['timetable_id'] ) ) {
				$timetable_edit_link = get_edit_post_link( $timetable_id );
				if ( $timetable_edit_link ) {
					echo '<div class="mrt-alert mrt-alert-info mrt-info-box mrt-mb-1">';
					echo '<a href="' . esc_url( $timetable_edit_link ) . '" class="button mrt-mr-sm">← ' . esc_html__( 'Back to Timetable', 'museum-railway-timetable' ) . '</a>';
					echo '<span class="description">' . esc_html__( 'This trip belongs to a timetable. The title is automatically generated from Route + Destination.', 'museum-railway-timetable' ) . '</span>';
					echo '</div>';
				}
			}
		}
	);
}

/**
 * Get available end stations for a route (for destination dropdown)
 *
 * @param int $route_id Route post ID
 * @return array [station_id => display_name]
 */
function MRT_get_service_available_end_stations( $route_id ) {
	$available = array();
	if ( ! $route_id ) {
		return $available;
	}
	$end_stations   = MRT_get_route_end_stations( $route_id );
	$route_stations = get_post_meta( $route_id, 'mrt_route_stations', true );
	if ( ! is_array( $route_stations ) ) {
		$route_stations = array();
	}
	if ( $end_stations['start'] > 0 ) {
		$s = get_post( $end_stations['start'] );
		if ( $s ) {
			$available[ $end_stations['start'] ] = $s->post_title . ' (' . __( 'Start', 'museum-railway-timetable' ) . ')';
		}
	}
	if ( $end_stations['end'] > 0 ) {
		$s = get_post( $end_stations['end'] );
		if ( $s ) {
			$available[ $end_stations['end'] ] = $s->post_title . ' (' . __( 'End', 'museum-railway-timetable' ) . ')';
		}
	}
	foreach ( $route_stations as $station_id ) {
		if ( ! isset( $available[ $station_id ] ) ) {
			$s = get_post( $station_id );
			if ( $s ) {
				$available[ $station_id ] = $s->post_title;
			}
		}
	}
	return $available;
}

/**
 * Render destination station field for service meta box
 *
 * @param int $route_id Route ID
 * @param int $end_station_id Selected end station ID
 */
function MRT_render_service_destination_field( $route_id, $end_station_id ) {
	$available_end_stations = MRT_get_service_available_end_stations( $route_id );
	if ( empty( $available_end_stations ) ) {
		$all_stations           = get_posts(
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
		$available_end_stations = array();
		foreach ( $all_stations as $s ) {
			$available_end_stations[ $s->ID ] = $s->post_title;
		}
	}
	$stations = $available_end_stations;
	?>
	<tr>
		<th><label for="mrt_service_end_station_id"><?php esc_html_e( 'Destination Station', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<select name="mrt_service_end_station_id" id="mrt_service_end_station_id" class="mrt-input mrt-input--meta">
				<option value=""><?php esc_html_e( '— Select Destination —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $stations as $sid => $sname ) : ?>
					<option value="<?php echo esc_attr( $sid ); ?>" <?php selected( $end_station_id, $sid ); ?>><?php echo esc_html( $sname ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Select the destination station for this trip. The direction will be calculated automatically based on the route and destination.', 'museum-railway-timetable' ); ?></p>
			<?php
			if ( $end_station_id && $route_id ) :
				$calculated_direction = MRT_calculate_direction_from_end_station( $route_id, $end_station_id );
				if ( $calculated_direction ) :
					?>
				<p class="description mrt-mt-sm mrt-text-tertiary">
					<strong><?php esc_html_e( 'Calculated direction:', 'museum-railway-timetable' ); ?></strong>
					<?php echo $calculated_direction === 'dit' ? esc_html__( 'Dit', 'museum-railway-timetable' ) : esc_html__( 'Från', 'museum-railway-timetable' ); ?>
				</p>
					<?php
				endif;
			endif;
			?>
		</td>
	</tr>
	<?php
}

/**
 * Get formatted timetable display label for dropdown
 *
 * @param int          $timetable_id Timetable ID
 * @param WP_Post|null $timetable Timetable post (optional)
 * @return string Display label
 */
function MRT_get_timetable_display_label( $timetable_id, $timetable = null ) {
	$timetable       = $timetable ?: get_post( $timetable_id );
	$display         = $timetable ? ( $timetable->post_title ?: __( 'Timetable', 'museum-railway-timetable' ) . ' #' . $timetable_id ) : '';
	$timetable_dates = MRT_get_timetable_dates( $timetable_id );
	if ( ! empty( $timetable_dates ) ) {
		$date_count = count( $timetable_dates );
		$first_date = date_i18n( get_option( 'date_format' ), strtotime( $timetable_dates[0] ) );
		$display   .= ( $date_count === 1 ) ? ' (' . $first_date . ')' : ' (' . $first_date . ' + ' . ( $date_count - 1 ) . ' ' . __( 'more', 'museum-railway-timetable' ) . ')';
	}
	return $display;
}

/**
 * Render service info box
 */
function MRT_render_service_info_box() {
	MRT_render_info_box(
		__( '💡 What is a Trip (Service)?', 'museum-railway-timetable' ),
		'<p>' . esc_html__( 'A trip represents one train journey. It belongs to a Timetable (which defines which days it runs) and uses a Route (which defines which stations are available). After selecting a Route, you can configure Stop Times to set arrival/departure times for each station.', 'museum-railway-timetable' ) . '</p>'
	);
}

/**
 * Render timetable row for service meta box
 */
function MRT_render_service_timetable_row( $timetable_id, $timetables, $editing_from_timetable ) {
	?>
	<tr>
		<th><label for="mrt_service_timetable_id"><?php esc_html_e( 'Timetable', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<?php if ( $editing_from_timetable ) : ?>
				<input type="hidden" name="mrt_service_timetable_id" value="<?php echo esc_attr( $timetable_id ); ?>" />
				<strong><?php echo esc_html( MRT_get_timetable_display_label( $timetable_id ) ); ?></strong>
				<p class="description"><?php esc_html_e( 'This trip belongs to the timetable you are editing. To change the timetable, go back to the timetable and remove this trip, then add it to another timetable.', 'museum-railway-timetable' ); ?></p>
			<?php else : ?>
				<select name="mrt_service_timetable_id" id="mrt_service_timetable_id" class="mrt-input mrt-input--meta" required>
					<option value=""><?php esc_html_e( '— Select Timetable —', 'museum-railway-timetable' ); ?></option>
					<?php foreach ( $timetables as $timetable ) : ?>
						<option value="<?php echo esc_attr( $timetable->ID ); ?>" <?php selected( $timetable_id, $timetable->ID ); ?>><?php echo esc_html( MRT_get_timetable_display_label( $timetable->ID, $timetable ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( '⚠️ Required: Select the timetable this trip belongs to. The timetable defines which days (dates) the trip runs.', 'museum-railway-timetable' ); ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render route row for service meta box
 */
function MRT_render_service_route_row( $routes, $route_id ) {
	?>
	<tr>
		<th><label for="mrt_service_route_id"><?php esc_html_e( 'Route', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<select name="mrt_service_route_id" id="mrt_service_route_id" class="mrt-input mrt-input--meta" required>
				<option value=""><?php esc_html_e( '— Select Route —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $routes as $route ) : ?>
					<option value="<?php echo esc_attr( $route->ID ); ?>" <?php selected( $route_id, $route->ID ); ?>><?php echo esc_html( $route->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( '⚠️ Required: Select the route this trip runs on. After selecting a route and saving, you can configure Stop Times below. Example: "Hultsfred - Västervik" or "Main Line".', 'museum-railway-timetable' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Render train type row for service meta box
 */
function MRT_render_service_train_type_row( $train_types, $all_train_types ) {
	?>
	<tr>
		<th><label for="mrt_service_train_type"><?php esc_html_e( 'Train Type', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<select name="mrt_service_train_type" id="mrt_service_train_type" class="mrt-input mrt-input--meta">
				<option value=""><?php esc_html_e( '— Select Train Type —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $all_train_types as $term ) : ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( in_array( $term->term_id, $train_types ) ); ?>><?php echo esc_html( $term->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Select the default train type for this service. You can override this for specific dates below. Example: "Steam", "Diesel", "Electric".', 'museum-railway-timetable' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Render train number row for service meta box
 */
function MRT_render_service_number_row( $post ) {
	$service_number = get_post_meta( $post->ID, 'mrt_service_number', true );
	?>
	<tr>
		<th><label for="mrt_service_number"><?php esc_html_e( 'Train Number', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<input type="text" name="mrt_service_number" id="mrt_service_number" value="<?php echo esc_attr( $service_number ); ?>" class="mrt-input mrt-input--meta" placeholder="<?php esc_attr_e( 'e.g., 71, 91, 73', 'museum-railway-timetable' ); ?>" />
			<p class="description"><?php esc_html_e( 'Enter the train number displayed in timetables (e.g., 71, 91, 73). If left empty, the service ID will be used as fallback.', 'museum-railway-timetable' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Public notice row (traffic messages for journey API / front)
 *
 * @param WP_Post $post Service post
 */
function MRT_render_service_notice_row( $post ) {
	$notice = get_post_meta( $post->ID, 'mrt_service_notice', true );
	if ( ! is_string( $notice ) ) {
		$notice = '';
	}
	?>
	<tr>
		<th><label for="mrt_service_notice"><?php esc_html_e( 'Public notice', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<textarea name="mrt_service_notice" id="mrt_service_notice" class="large-text" rows="3"><?php echo esc_textarea( $notice ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Optional message for this trip (e.g. replaced locomotive). Shown in public journey data.', 'museum-railway-timetable' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Render date-specific train types row for service meta box
 */
function MRT_render_service_train_types_by_date_row( $post, $timetable_id, $all_train_types ) {
	$train_types_by_date = get_post_meta( $post->ID, 'mrt_service_train_types_by_date', true );
	if ( ! is_array( $train_types_by_date ) ) {
		$train_types_by_date = array();
	}
	$timetable_dates = $timetable_id ? MRT_get_timetable_dates( $timetable_id ) : array();
	sort( $timetable_dates );
	?>
	<tr>
		<th><label><?php esc_html_e( 'Date-Specific Train Types', 'museum-railway-timetable' ); ?></label></th>
		<td>
			<p class="description"><?php esc_html_e( 'Override the default train type for specific dates. Leave empty to use the default train type.', 'museum-railway-timetable' ); ?></p>
			<?php if ( empty( $timetable_dates ) ) : ?>
				<p class="description mrt-text-error mrt-font-semibold">
					<?php esc_html_e( '⚠️ Please select a timetable first to see available dates.', 'museum-railway-timetable' ); ?>
				</p>
			<?php else : ?>
				<div id="mrt-train-types-by-date-container" class="mrt-mt-1">
					<?php
					foreach ( $timetable_dates as $date ) :
						$date_formatted = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
						$train_type_id  = isset( $train_types_by_date[ $date ] ) ? intval( $train_types_by_date[ $date ] ) : 0;
						?>
						<div class="mrt-box mrt-box-sm mrt-train-type-date-row">
							<label class="mrt-form-label mrt-form-label--inline">
								<?php echo esc_html( $date_formatted ); ?>
								<span class="mrt-form-label__hint">(<?php echo esc_html( $date ); ?>)</span>
							</label>
							<select name="mrt_train_types_by_date[<?php echo esc_attr( $date ); ?>]" class="mrt-input mrt-input--meta mrt-w-200 mrt-ml-sm">
								<option value=""><?php esc_html_e( '— Use Default —', 'museum-railway-timetable' ); ?></option>
								<?php foreach ( $all_train_types as $term ) : ?>
									<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $train_type_id, $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render service meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_service_meta_box( $post ) {
	wp_nonce_field( 'mrt_save_service_meta', 'mrt_service_meta_nonce' );

	$timetable_id = get_post_meta( $post->ID, 'mrt_service_timetable_id', true );
	if ( empty( $timetable_id ) && isset( $_GET['timetable_id'] ) ) {
		$timetable_id = intval( $_GET['timetable_id'] );
	}
	$route_id       = get_post_meta( $post->ID, 'mrt_service_route_id', true );
	$end_station_id = get_post_meta( $post->ID, 'mrt_service_end_station_id', true );

	$timetables             = get_posts(
		array(
			'post_type'      => 'mrt_timetable',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'all',
		)
	);
	$routes                 = get_posts(
		array(
			'post_type'      => 'mrt_route',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'all',
		)
	);
	$train_types            = wp_get_post_terms( $post->ID, 'mrt_train_type', array( 'fields' => 'ids' ) );
	$all_train_types        = get_terms(
		array(
			'taxonomy'   => 'mrt_train_type',
			'hide_empty' => false,
		)
	);
	$editing_from_timetable = isset( $_GET['timetable_id'] ) && intval( $_GET['timetable_id'] ) === intval( $timetable_id );

	if ( $editing_from_timetable && $timetable_id ) {
		MRT_service_meta_box_setup_editing_from_timetable( $timetable_id );
	}

	MRT_render_service_info_box();
	?>
	<div class="mrt-box mrt-mt-1">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Trip Details', 'museum-railway-timetable' ); ?></h3>
		<table class="form-table">
			<?php
			MRT_render_service_timetable_row( $timetable_id, $timetables, $editing_from_timetable );
			MRT_render_service_route_row( $routes, $route_id );
			MRT_render_service_train_type_row( $train_types, $all_train_types );
			MRT_render_service_number_row( $post );
			MRT_render_service_notice_row( $post );
			MRT_render_service_train_types_by_date_row( $post, $timetable_id, $all_train_types );
			MRT_render_service_destination_field( $route_id, $end_station_id );
			?>
		</table>
	</div>
	<?php
}
