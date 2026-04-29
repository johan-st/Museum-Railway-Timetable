<?php
/**
 * Timetable services meta box (trips within timetable)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render a single service row in timetable services table
 *
 * @param WP_Post $service Service post
 * @param int     $timetable_id Timetable post ID
 */
function MRT_render_timetable_service_row( $service, $timetable_id ) {
	$route_id         = get_post_meta( $service->ID, 'mrt_service_route_id', true );
	$train_types      = wp_get_post_terms( $service->ID, 'mrt_train_type', array( 'fields' => 'ids' ) );
	$train_type_id    = ! empty( $train_types ) ? $train_types[0] : 0;
	$route            = $route_id ? get_post( $route_id ) : null;
	$train_type       = $train_type_id ? get_term( $train_type_id, 'mrt_train_type' ) : null;
	$destination_data = MRT_get_service_destination( $service->ID );
	$destination      = ! empty( $destination_data['destination'] ) ? $destination_data['destination'] : '—';
	?>
	<tr class="mrt-row-hover" data-service-id="<?php echo esc_attr( (string) $service->ID ); ?>">
		<td><?php echo $route ? esc_html( $route->post_title ) : '—'; ?></td>
		<td><?php echo $train_type ? esc_html( $train_type->name ) : '—'; ?></td>
		<td><?php echo esc_html( $destination ); ?></td>
		<td>
			<a href="<?php echo esc_url( add_query_arg( 'timetable_id', $timetable_id, get_edit_post_link( $service->ID ) ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Edit', 'museum-railway-timetable' ); ?>
			</a>
			<input type="hidden" name="mrt_service_timetable_id" value="<?php echo esc_attr( (string) $timetable_id ); ?>" />
			<button type="button" class="button button-small mrt-delete-service-from-timetable" data-service-id="<?php echo esc_attr( (string) $service->ID ); ?>">
				<?php esc_html_e( 'Remove', 'museum-railway-timetable' ); ?>
			</button>
		</td>
	</tr>
	<?php
}

/**
 * Render new service row (add trip form)
 *
 * @param array $routes Route posts
 * @param array $all_train_types Train type terms
 * @param int   $timetable_id Timetable post ID
 */
function MRT_render_timetable_new_service_row( $routes, $all_train_types, $timetable_id ) {
	?>
	<tr class="mrt-new-service-row mrt-new-row">
		<td>
			<select id="mrt-new-service-route" class="mrt-input mrt-w-full" required>
				<option value=""><?php esc_html_e( '— Select Route —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $routes as $route ) : ?>
					<option value="<?php echo esc_attr( (string) $route->ID ); ?>"><?php echo esc_html( $route->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<select id="mrt-new-service-train-type" class="mrt-input mrt-w-full">
				<option value=""><?php esc_html_e( '— Select —', 'museum-railway-timetable' ); ?></option>
				<?php foreach ( $all_train_types as $train_type ) : ?>
					<option value="<?php echo esc_attr( (string) $train_type->term_id ); ?>"><?php echo esc_html( $train_type->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<select id="mrt-new-service-end-station" class="mrt-input mrt-w-full">
				<option value=""><?php esc_html_e( '— Select Destination —', 'museum-railway-timetable' ); ?></option>
				<option value="" disabled><?php esc_html_e( 'Select a route first', 'museum-railway-timetable' ); ?></option>
			</select>
			<p class="description mrt-text-small mrt-mt-xs"><?php esc_html_e( 'Select route first to see available destinations', 'museum-railway-timetable' ); ?></p>
		</td>
		<td>
			<button type="button" class="button button-primary button-small" id="mrt-add-service-to-timetable" data-timetable-id="<?php echo esc_attr( (string) $timetable_id ); ?>">
				<?php esc_html_e( 'Add Trip', 'museum-railway-timetable' ); ?>
			</button>
		</td>
	</tr>
	<?php
}

/**
 * Render timetable services meta box (to manage trips within timetable)
 *
 * @param WP_Post $post Current post object (Timetable)
 */
function MRT_render_timetable_services_box( $post ) {
	$services        = get_posts(
		array(
			'post_type'      => 'mrt_service',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'mrt_service_timetable_id',
					'value'   => $post->ID,
					'compare' => '=',
				),
			),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'all',
		)
	);
	$routes          = get_posts(
		array(
			'post_type'      => 'mrt_route',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'all',
		)
	);
	$all_train_types = get_terms(
		array(
			'taxonomy'   => 'mrt_train_type',
			'hide_empty' => false,
		)
	);
	?>
	<div id="mrt-timetable-services-container" class="mrt-box mrt-timetable-services-box mrt-my-1">
		<?php wp_nonce_field( 'mrt_timetable_services_nonce', 'mrt_timetable_services_nonce' ); ?>
		<p class="description">
			<?php esc_html_e( 'Manage trips (services) for this timetable. Add, edit, or remove trips directly here.', 'museum-railway-timetable' ); ?>
		</p>
		<table class="widefat striped" id="mrt-timetable-services-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Route', 'museum-railway-timetable' ); ?></th>
					<th class="mrt-w-150"><?php esc_html_e( 'Train Type', 'museum-railway-timetable' ); ?></th>
					<th class="mrt-w-150"><?php esc_html_e( 'Destination', 'museum-railway-timetable' ); ?></th>
					<th class="mrt-w-150"><?php esc_html_e( 'Actions', 'museum-railway-timetable' ); ?></th>
				</tr>
			</thead>
			<tbody id="mrt-timetable-services-tbody">
				<?php
				foreach ( $services as $service ) {
					MRT_render_timetable_service_row( $service, $post->ID );
				}
				?>
				<?php MRT_render_timetable_new_service_row( $routes, $all_train_types, $post->ID ); ?>
			</tbody>
		</table>
	</div>
	<?php
}
