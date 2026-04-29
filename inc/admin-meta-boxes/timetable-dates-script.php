<?php

declare(strict_types=1);

/**
 * Timetable dates UI script (inline for locale/translations)
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Escaped JS strings for the timetable dates UI.
 *
 * @return array<string, string>
 */
function MRT_timetable_dates_script_esc_strings(): array {
	return array(
		'no_dates_msg'       => esc_js( __( 'No dates selected. Add dates using patterns or single date selection above.', MRT_TEXT_DOMAIN ) ),
		'remove_label'       => esc_js( __( 'Remove', MRT_TEXT_DOMAIN ) ),
		'please_select'      => esc_js( __( 'Please select a day of week, start date, and end date.', MRT_TEXT_DOMAIN ) ),
		'start_before_end'   => esc_js( __( 'Start date must be before or equal to end date.', MRT_TEXT_DOMAIN ) ),
		'added_dates'        => esc_js( __( 'Added', MRT_TEXT_DOMAIN ) ),
		'dates_from_pattern' => esc_js( __( 'dates from pattern.', MRT_TEXT_DOMAIN ) ),
		'no_dates_found'     => esc_js( __( 'No dates found matching the pattern.', MRT_TEXT_DOMAIN ) ),
		'please_select_date' => esc_js( __( 'Please select a date.', MRT_TEXT_DOMAIN ) ),
		'date_already_added' => esc_js( __( 'This date is already added.', MRT_TEXT_DOMAIN ) ),
		'date_added'         => esc_js( __( 'Date added successfully.', MRT_TEXT_DOMAIN ) ),
	);
}

/**
 * Populate selectedDates from existing rows and PHP.
 *
 * @param array<int|string, mixed> $dates
 */
function MRT_render_timetable_dates_js_bootstrap_sets( array $dates ): void {
	?>
		var selectedDates = new Set();
		$('#mrt-timetable-dates-container .mrt-date-row').each(function() {
			var date = $(this).data('date');
			if (date) selectedDates.add(date);
		});
		var phpDates = <?php echo json_encode( $dates ); ?>;
		if (Array.isArray(phpDates)) {
			phpDates.forEach(function(date) {
				if (date) selectedDates.add(date);
			});
		}
	<?php
}

/**
 * JS function updateDatesList() — renders rows from selectedDates.
 *
 * @param array<string, string> $s
 */
function MRT_render_timetable_dates_js_update_list_fn( array $s, string $locale ): void {
	?>
		function updateDatesList() {
			var $container = $('#mrt-timetable-dates-container');
			$container.empty();
			if (selectedDates.size === 0) {
				$container.after('<p class="description" id="mrt-no-dates-message"><?php echo $s['no_dates_msg']; ?></p>');
				return;
			}
			$('#mrt-no-dates-message').remove();
			var sortedDates = Array.from(selectedDates).sort();
			sortedDates.forEach(function(date) {
				if (!date || typeof date !== 'string') return;
				var dateObj = new Date(date + 'T00:00:00');
				var formattedDate;
				if (isNaN(dateObj.getTime())) {
					formattedDate = date;
				} else {
					try {
						formattedDate = dateObj.toLocaleDateString('<?php echo esc_js( $locale ); ?>', {
							year: 'numeric', month: 'long', day: 'numeric', weekday: 'long'
						});
						if (!formattedDate || formattedDate === 'Invalid Date') {
							formattedDate = dateObj.toLocaleDateString();
						}
					} catch (e) {
						formattedDate = dateObj.toLocaleDateString();
					}
				}
				var $row = $('<div class="mrt-box mrt-box-sm mrt-form-row mrt-date-row" data-date="' + date.replace(/"/g, '&quot;') + '">' +
					'<input type="hidden" name="mrt_timetable_dates[]" value="' + date.replace(/"/g, '&quot;') + '" />' +
					'<span class="mrt-font-medium mrt-flex-1">' + formattedDate + '</span> ' +
					'<span class="mrt-text-tertiary mrt-ml-sm">(' + date + ')</span> ' +
					'<button type="button" class="button button-small mrt-remove-date mrt-ml-1"><?php echo $s['remove_label']; ?></button>' +
					'</div>');
				$container.append($row);
			});
		}
	<?php
}

/**
 * Init Set from DOM + PHP, and updateDatesList().
 *
 * @param array<int|string, mixed> $dates
 * @param array<string, string>    $s
 */
function MRT_render_timetable_dates_js_block_init( array $dates, array $s, string $locale ): void {
	MRT_render_timetable_dates_js_bootstrap_sets( $dates );
	MRT_render_timetable_dates_js_update_list_fn( $s, $locale );
}

/**
 * Pattern-based date add handler.
 *
 * @param array<string, string> $s
 */
function MRT_render_timetable_dates_js_block_pattern( array $s ): void {
	?>
		$('#mrt-add-pattern-dates').on('click', function() {
			var weekday = parseInt($('#mrt-pattern-weekday').val());
			var startDate = $('#mrt-pattern-start-date').val();
			var endDate = $('#mrt-pattern-end-date').val();
			if (!weekday || weekday === '' || !startDate || !endDate) {
				alert('<?php echo $s['please_select']; ?>');
				return;
			}
			var start = new Date(startDate + 'T00:00:00');
			var end = new Date(endDate + 'T00:00:00');
			if (start > end) {
				alert('<?php echo $s['start_before_end']; ?>');
				return;
			}
			var current = new Date(start);
			var added = 0;
			while (current <= end) {
				if (current.getDay() === weekday) {
					selectedDates.add(current.toISOString().split('T')[0]);
					added++;
				}
				current.setDate(current.getDate() + 1);
			}
			updateDatesList();
			if (added > 0) {
				var $msg = $('<div class="mrt-success-message notice notice-success is-dismissible"><p><?php echo $s['added_dates']; ?> ' + added + ' <?php echo $s['dates_from_pattern']; ?></p></div>');
				$('#mrt-timetable-dates-container').before($msg);
				setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
			} else {
				alert('<?php echo $s['no_dates_found']; ?>');
			}
		});
	<?php
}

/**
 * Single date add, remove handler, initial updateDatesList.
 *
 * @param array<string, string> $s
 */
function MRT_render_timetable_dates_js_block_single_and_remove( array $s ): void {
	?>
		$('#mrt-add-single-date').on('click', function() {
			var date = $('#mrt-single-date').val();
			if (!date) {
				alert('<?php echo $s['please_select_date']; ?>');
				return;
			}
			if (selectedDates.has(date)) {
				alert('<?php echo $s['date_already_added']; ?>');
				return;
			}
			selectedDates.add(date);
			updateDatesList();
			$('#mrt-single-date').val('');
			var $msg = $('<div class="mrt-success-message notice notice-success is-dismissible mrt-my-1"><p><?php echo $s['date_added']; ?></p></div>');
			$('#mrt-timetable-dates-container').before($msg);
			setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
		});

		$(document).on('click', '.mrt-remove-date', function() {
			var $row = $(this).closest('.mrt-date-row');
			selectedDates.delete($row.data('date'));
			updateDatesList();
		});

		updateDatesList();
	<?php
}

/**
 * Output the timetable dates JavaScript
 *
 * @param array<int|string, mixed> $dates Array of date strings (YYYY-MM-DD)
 */
function MRT_render_timetable_dates_script( array $dates ): void {
	$s      = MRT_timetable_dates_script_esc_strings();
	$locale = get_locale();
	?>
	<script>
	jQuery(document).ready(function($) {
	<?php
	MRT_render_timetable_dates_js_block_init( $dates, $s, $locale );
	MRT_render_timetable_dates_js_block_pattern( $s );
	MRT_render_timetable_dates_js_block_single_and_remove( $s );
	?>
	});
	</script>
	<?php
}
