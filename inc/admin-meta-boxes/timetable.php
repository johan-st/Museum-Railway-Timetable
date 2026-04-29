<?php
/**
 * Timetable meta box
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/admin-meta-boxes/timetable-dates-script.php';

/**
 * Timetable colour type field.
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_timetable_type_field( WP_Post $post ): void {
	$timetable_type = get_post_meta( $post->ID, 'mrt_timetable_type', true );
	?>
	<table class="form-table mrt-mb-lg">
		<tr>
			<th><label for="mrt_timetable_type"><?php esc_html_e( 'Timetable type', 'museum-railway-timetable' ); ?></label></th>
			<td>
				<select name="mrt_timetable_type" id="mrt_timetable_type" class="mrt-input mrt-input--meta">
					<option value=""><?php esc_html_e( '— None —', 'museum-railway-timetable' ); ?></option>
					<option value="green" <?php selected( $timetable_type, 'green' ); ?>><?php esc_html_e( 'Grön (Green)', 'museum-railway-timetable' ); ?></option>
					<option value="red" <?php selected( $timetable_type, 'red' ); ?>><?php esc_html_e( 'Röd (Red)', 'museum-railway-timetable' ); ?></option>
					<option value="yellow" <?php selected( $timetable_type, 'yellow' ); ?>><?php esc_html_e( 'Gul (Yellow)', 'museum-railway-timetable' ); ?></option>
					<option value="orange" <?php selected( $timetable_type, 'orange' ); ?>><?php esc_html_e( 'Orange', 'museum-railway-timetable' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Shows as "GRÖN TIDTABELL", "RÖD TIDTABELL" etc. in the timetable overview.', 'museum-railway-timetable' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Pattern + single-date UI blocks (dates meta box).
 */
function MRT_render_timetable_date_pattern_and_single_blocks(): void {
	?>
	<div class="mrt-box mrt-date-pattern-section">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Add Dates from Pattern', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Select a day of the week and a date range to automatically add all matching dates.', 'museum-railway-timetable' ); ?></p>
		<table class="form-table mrt-mt-sm">
			<tr>
				<th class="mrt-w-150"><label for="mrt-pattern-weekday"><?php esc_html_e( 'Day of Week', 'museum-railway-timetable' ); ?></label></th>
				<td>
					<select id="mrt-pattern-weekday" class="mrt-input mrt-input--meta">
						<option value=""><?php esc_html_e( '— Select Day —', 'museum-railway-timetable' ); ?></option>
						<option value="1"><?php esc_html_e( 'Monday', 'museum-railway-timetable' ); ?></option>
						<option value="2"><?php esc_html_e( 'Tuesday', 'museum-railway-timetable' ); ?></option>
						<option value="3"><?php esc_html_e( 'Wednesday', 'museum-railway-timetable' ); ?></option>
						<option value="4"><?php esc_html_e( 'Thursday', 'museum-railway-timetable' ); ?></option>
						<option value="5"><?php esc_html_e( 'Friday', 'museum-railway-timetable' ); ?></option>
						<option value="6"><?php esc_html_e( 'Saturday', 'museum-railway-timetable' ); ?></option>
						<option value="0"><?php esc_html_e( 'Sunday', 'museum-railway-timetable' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="mrt-pattern-start-date"><?php esc_html_e( 'From Date', 'museum-railway-timetable' ); ?></label></th>
				<td><input type="date" id="mrt-pattern-start-date" class="mrt-input mrt-input--meta" /></td>
			</tr>
			<tr>
				<th><label for="mrt-pattern-end-date"><?php esc_html_e( 'To Date', 'museum-railway-timetable' ); ?></label></th>
				<td><input type="date" id="mrt-pattern-end-date" class="mrt-input mrt-input--meta" /></td>
			</tr>
		</table>
		<button type="button" id="mrt-add-pattern-dates" class="button button-primary"><?php esc_html_e( 'Add Dates from Pattern', 'museum-railway-timetable' ); ?></button>
	</div>
	<div class="mrt-box mrt-date-single-section">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Add Single Date', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Add a specific date manually.', 'museum-railway-timetable' ); ?></p>
		<p>
			<input type="date" id="mrt-single-date" class="mrt-input mrt-input--meta mrt-mr-sm" />
			<button type="button" id="mrt-add-single-date" class="button"><?php esc_html_e( 'Add Date', 'museum-railway-timetable' ); ?></button>
		</p>
	</div>
	<?php
}

/**
 * Selected dates list (server-rendered rows).
 *
 * @param array<int, string> $dates Validated YYYY-MM-DD strings
 */
function MRT_render_timetable_selected_dates_list( array $dates ): void {
	?>
	<div class="mrt-box mrt-mt-lg">
		<h3 class="mrt-heading mrt-mt-0"><?php esc_html_e( 'Selected Dates', 'museum-railway-timetable' ); ?></h3>
		<p class="description"><?php esc_html_e( 'All dates when this timetable applies. Click "Remove" to remove individual dates.', 'museum-railway-timetable' ); ?></p>
		<div id="mrt-timetable-dates-container" class="mrt-mt-sm">
			<?php foreach ( $dates as $date ) : ?>
				<div class="mrt-box mrt-box-sm mrt-form-row mrt-date-row" data-date="<?php echo esc_attr( $date ); ?>">
					<input type="hidden" name="mrt_timetable_dates[]" value="<?php echo esc_attr( $date ); ?>" />
					<span class="mrt-font-medium mrt-flex-1"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?></span>
					<span class="mrt-text-tertiary mrt-ml-sm">(<?php echo esc_html( $date ); ?>)</span>
					<button type="button" class="button button-small mrt-remove-date mrt-ml-1"><?php esc_html_e( 'Remove', 'museum-railway-timetable' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $dates === array() ) : ?>
			<p class="description" id="mrt-no-dates-message"><?php esc_html_e( 'No dates selected. Add dates using patterns or single date selection above.', 'museum-railway-timetable' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render timetable type and date sections (HTML only, no script)
 *
 * @param WP_Post            $post Current post object
 * @param array<int, string> $dates Validated dates array
 */
function MRT_render_timetable_date_sections( WP_Post $post, array $dates ): void {
	MRT_render_timetable_type_field( $post );
	MRT_render_timetable_date_pattern_and_single_blocks();
	MRT_render_timetable_selected_dates_list( $dates );
}

/**
 * Render timetable meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_timetable_meta_box( $post ) {
	wp_nonce_field( 'mrt_save_timetable_meta', 'mrt_timetable_meta_nonce' );

	$dates = MRT_get_timetable_dates( $post->ID );
	if ( ! is_array( $dates ) ) {
		$dates = array();
	}
	$dates = array_values(
		array_filter(
			$dates,
			function ( $d ) {
				return ! empty( $d ) && MRT_validate_date( $d );
			}
		)
	);

	wp_enqueue_script( 'jquery' );
	?>
	<?php
	MRT_render_info_box(
		__( '💡 What is a Timetable?', 'museum-railway-timetable' ),
		'<p>' . esc_html__( 'A timetable defines which days (dates) trains run. You can add dates using patterns (e.g., all Wednesdays in June-September) or add specific dates. You can also remove individual dates from patterns.', 'museum-railway-timetable' ) . '</p>'
	);
	?>
	<?php MRT_render_timetable_date_sections( $post, $dates ); ?>
	<?php MRT_render_timetable_dates_script( $dates ); ?>
	<?php
}

/**
 * Save timetable meta box data
 *
 * @param int $post_id Post ID
 */
add_action(
	'save_post_mrt_timetable',
	function ( $post_id ) {
		if ( ! MRT_verify_meta_box_save( $post_id, 'mrt_timetable_meta_nonce', 'mrt_save_timetable_meta' ) ) {
			return;
		}

		// Save timetable type (green, red, yellow, orange)
		if ( isset( $_POST['mrt_timetable_type'] ) ) {
			$type    = sanitize_text_field( $_POST['mrt_timetable_type'] );
			$allowed = array( 'green', 'red', 'yellow', 'orange', '' );
			if ( in_array( $type, $allowed, true ) ) {
				update_post_meta( $post_id, 'mrt_timetable_type', $type );
			}
		}

		// Save timetable dates (array)
		if ( isset( $_POST['mrt_timetable_dates'] ) && is_array( $_POST['mrt_timetable_dates'] ) ) {
			$dates = array();
			foreach ( $_POST['mrt_timetable_dates'] as $date ) {
				$date = sanitize_text_field( $date );
				if ( MRT_validate_date( $date ) ) {
					$dates[] = $date;
				}
			}
			// Remove duplicates and sort
			$dates = array_unique( $dates );
			sort( $dates );
			if ( ! empty( $dates ) ) {
				update_post_meta( $post_id, 'mrt_timetable_dates', $dates );
				// Remove old single date field if it exists
				delete_post_meta( $post_id, 'mrt_timetable_date' );
			} else {
				// Only delete if explicitly empty array was sent
				// Don't delete if field wasn't sent at all (might be autosave or other issue)
				delete_post_meta( $post_id, 'mrt_timetable_dates' );
			}
		}
	}
);
