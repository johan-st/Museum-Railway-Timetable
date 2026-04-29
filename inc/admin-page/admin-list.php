<?php
/**
 * Admin: Stations Overview list
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Stations Overview: view tab under Stations list.
 * Shows each station with type, display order, services count, and next running day (within X days).
 */

/**
 * Add "Overview" view tab to Stations list
 */
add_filter(
	'views_edit-mrt_station',
	function ( $views ) {
		$current_view = isset( $_GET['mrt_view'] ) ? sanitize_text_field( wp_unslash( $_GET['mrt_view'] ) ) : '';
		$overview_url = admin_url( 'edit.php?post_type=mrt_station&mrt_view=overview' );
		$list_url     = admin_url( 'edit.php?post_type=mrt_station' );

		$class = ( $current_view === 'overview' ) ? 'current' : '';
		$count = wp_count_posts( 'mrt_station' )->publish;

		$views['overview'] = sprintf(
			'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
			esc_url( $overview_url ),
			esc_attr( $class ),
			esc_html__( 'Overview', 'museum-railway-timetable' ),
			(int) $count
		);

		return $views;
	}
);

/**
 * Show overview table when Overview view is active
 */
add_action(
	'admin_init',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'edit-mrt_station' ) {
			return;
		}

		$current_view = isset( $_GET['mrt_view'] ) ? sanitize_text_field( wp_unslash( $_GET['mrt_view'] ) ) : '';
		if ( $current_view === 'overview' ) {
			// Hide default list table and show overview instead
			add_action( 'admin_footer', 'MRT_render_stations_overview_inline' );
		}
	}
);

/**
 * Render stations overview filter form
 */
function MRT_render_stations_overview_filter_form() {
	$terms        = get_terms(
		array(
			'taxonomy'   => 'mrt_train_type',
			'hide_empty' => false,
		)
	);
	$current_slug = isset( $_GET['train_type'] ) ? sanitize_title( wp_unslash( $_GET['train_type'] ) ) : '';
	?>
	<div class="mrt-box mrt-mb-1">
		<form method="get" class="mrt-mt-sm mrt-mb-1">
			<input type="hidden" name="post_type" value="mrt_station" />
			<input type="hidden" name="mrt_view" value="overview" />
			<label><?php echo esc_html__( 'Train type:', 'museum-railway-timetable' ); ?></label>
			<select name="train_type">
				<option value=""><?php echo esc_html__( 'All types', 'museum-railway-timetable' ); ?></option>
				<?php
				if ( ! is_wp_error( $terms ) ) :
					foreach ( $terms as $t ) :
						?>
					<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_slug, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
									<?php
				endforeach;
endif;
				?>
			</select>
			<button class="button"><?php echo esc_html__( 'Filter', 'museum-railway-timetable' ); ?></button>
			<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=mrt_station&mrt_view=overview' ) ); ?>"><?php echo esc_html__( 'Reset', 'museum-railway-timetable' ); ?></a>
		</form>
	</div>
	<?php
}

/**
 * Get routes that include a station
 *
 * @param int $sid Station post ID
 * @return array Route posts
 */
function MRT_get_routes_using_station( int $sid ): array {
	$all_routes           = get_posts(
		array(
			'post_type'      => 'mrt_route',
			'posts_per_page' => -1,
			'fields'         => 'all',
		)
	);
	$routes_using_station = array();
	foreach ( $all_routes as $route ) {
		$route_stations = get_post_meta( $route->ID, 'mrt_route_stations', true );
		if ( is_array( $route_stations ) && in_array( $sid, $route_stations, true ) ) {
			$routes_using_station[] = $route;
		}
	}
	return $routes_using_station;
}

/**
 * Render a single station overview row
 *
 * @param int    $sid         Station post ID
 * @param string $train_type Train type slug filter
 */
function MRT_render_stations_overview_row( $sid, $train_type ) {
	$title                = get_the_title( $sid );
	$type                 = get_post_meta( $sid, 'mrt_station_type', true );
	$order                = intval( get_post_meta( $sid, 'mrt_display_order', true ) );
	$routes_using_station = MRT_get_routes_using_station( $sid );
	$services             = MRT_get_services_for_station( $sid );
	$count                = count( $services );
	$next                 = MRT_next_running_day_for_station( $sid, $train_type );
	$edit_link            = get_edit_post_link( $sid, '' );
	?>
	<tr class="mrt-row-hover">
		<td><?php echo esc_html( $title ?: ( '#' . $sid ) ); ?></td>
		<td><?php echo esc_html( $type ?: '' ); ?></td>
		<td><?php echo esc_html( (string) $order ); ?></td>
		<td>
			<?php if ( ! empty( $routes_using_station ) ) : ?>
				<?php
				$route_names = array();
				foreach ( $routes_using_station as $route ) {
					$route_names[] = '<a href="' . esc_url( get_edit_post_link( $route->ID ) ) . '">' . esc_html( $route->post_title ) . '</a>';
				}
				echo implode( ', ', $route_names );
				?>
			<?php else : ?>
				<span class="description"><?php echo esc_html__( 'None', 'museum-railway-timetable' ); ?></span>
			<?php endif; ?>
		</td>
		<td><?php echo esc_html( (string) $count ); ?></td>
		<td>
			<?php
			if ( $next ) {
				$datetime = MRT_get_current_datetime();
				echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $next, $datetime['timestamp'] ) ) );
			} else {
				echo '<span class="mrt-opacity-70">' . esc_html__( '— none within range —', 'museum-railway-timetable' ) . '</span>';
			}
			?>
		</td>
		<td>
			<?php if ( $edit_link ) : ?>
				<a class="button button-small" href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html__( 'Edit', 'museum-railway-timetable' ); ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Render stations overview table body rows
 *
 * @param array  $station_ids Station post IDs
 * @param string $train_type  Train type slug filter
 */
function MRT_render_stations_overview_table_rows( $station_ids, $train_type ) {
	foreach ( $station_ids as $sid ) {
		MRT_render_stations_overview_row( $sid, $train_type );
	}
}

/**
 * Render stations overview table (header + body)
 *
 * @param array  $station_ids Station post IDs
 * @param string $train_type  Train type slug filter
 */
function MRT_render_stations_overview_table( $station_ids, $train_type ) {
	?>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'Station', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Type', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Display order', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Routes', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Services (count)', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Next running day', 'museum-railway-timetable' ); ?></th>
				<th><?php echo esc_html__( 'Actions', 'museum-railway-timetable' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $station_ids ) : ?>
				<tr><td colspan="7"><?php echo esc_html__( 'No stations found.', 'museum-railway-timetable' ); ?></td></tr>
			<?php else : ?>
				<?php MRT_render_stations_overview_table_rows( $station_ids, $train_type ); ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render stations overview inline in the list screen
 */
function MRT_render_stations_overview_inline() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$q           = new WP_Query(
		array(
			'post_type'      => 'mrt_station',
			'posts_per_page' => -1,
			'orderby'        => array(
				'meta_value_num' => 'ASC',
				'title'          => 'ASC',
			),
			'meta_key'       => 'mrt_display_order',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'nopaging'       => true,
		)
	);
	$station_ids = $q->posts;
	$train_type  = isset( $_GET['train_type'] ) ? sanitize_title( wp_unslash( $_GET['train_type'] ) ) : '';
	?>
	<script>
	jQuery(document).ready(function($) {
		$('.wp-list-table, .tablenav.top, .tablenav.bottom').hide();
		$('.wrap h1').text('<?php echo esc_js( __( 'Stations Overview', 'museum-railway-timetable' ) ); ?>');
	});
	</script>
	<div class="mrt-section mrt-mt-1">
		<?php MRT_render_stations_overview_filter_form(); ?>
		<?php MRT_render_stations_overview_table( $station_ids, $train_type ); ?>
		<p class="mrt-alert mrt-alert-info mrt-mt-sm mrt-text-quaternary">
			<?php echo esc_html__( 'Note: Next running day looks ahead up to 60 days. Use the filter above to limit by train type slug.', 'museum-railway-timetable' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Get all service IDs that stop at a given station
 *
 * @param int $station_id Station post ID
 * @return array Array of service post IDs
 */
function MRT_get_services_for_station( $station_id ) {
	global $wpdb;

	// Validate input
	$station_id = intval( $station_id );
	if ( $station_id <= 0 ) {
		return array();
	}

	$table = $wpdb->prefix . 'mrt_stoptimes';
	$sql   = $wpdb->prepare( "SELECT DISTINCT service_post_id FROM $table WHERE station_post_id = %d", $station_id );
	$ids   = $wpdb->get_col( $sql );

	// Check for database errors
	if ( MRT_check_db_error( 'MRT_get_services_for_station' ) ) {
		return array();
	}

	return array_map( 'intval', $ids ?: array() );
}

/**
 * Find next running day for a station by checking timetables for services that stop at the station.
 * Checks from 'today' up to +60 days (configurable via filter 'mrt_overview_days_ahead').
 *
 * @param int    $station_id    Station post ID
 * @param string $train_type_slug Optional train type taxonomy slug
 * @return string Date in YYYY-MM-DD format or empty string if none found
 */
function MRT_next_running_day_for_station( $station_id, $train_type_slug = '' ) {
	$days_ahead    = apply_filters( 'mrt_overview_days_ahead', 60 );
	$datetime      = MRT_get_current_datetime();
	$tz_ts         = $datetime['timestamp'];
	$services_here = MRT_get_services_for_station( $station_id );
	if ( ! $services_here ) {
		return '';
	}

	for ( $i = 0; $i <= $days_ahead; $i++ ) {
		$dateYmd = date( 'Y-m-d', strtotime( "+$i day", $tz_ts ) );
		$running = MRT_services_running_on_date( $dateYmd, $train_type_slug );
		if ( $running ) {
			$intersect = array_intersect( $services_here, $running );
			if ( ! empty( $intersect ) ) {
				return $dateYmd;
			}
		}
	}
	return '';
}
