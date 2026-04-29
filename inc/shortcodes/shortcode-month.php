<?php
/**
 * Shortcode: Month view [museum_timetable_month]
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Apply ?mrt_month=YYYY-MM from the request (for month navigation links)
 *
 * @param array<string, mixed> $atts Shortcode attributes
 * @return array<string, mixed>
 */
function MRT_month_shortcode_apply_query_month( array $atts ) {
	if ( empty( $_GET['mrt_month'] ) || ! is_string( $_GET['mrt_month'] ) ) {
		return $atts;
	}
	$gm = sanitize_text_field( wp_unslash( $_GET['mrt_month'] ) );
	if ( ! preg_match( '/^(\d{4})-(\d{2})$/', $gm, $m ) ) {
		return $atts;
	}
	$y  = (int) $m[1];
	$mo = (int) $m[2];
	if ( $y < 1970 || $y > 2100 || $mo < 1 || $mo > 12 ) {
		return $atts;
	}
	$atts['month'] = sprintf( '%04d-%02d', $y, $mo );

	return $atts;
}

/**
 * Prev/next month URLs for shortcode navigation (preserves other query args)
 *
 * @param int|false $first_ts Timestamp of first day of displayed month
 * @return array{0:string,1:string}
 */
function MRT_month_shortcode_nav_link_urls( $first_ts ) {
	if ( false === $first_ts ) {
		$t  = current_time( 'timestamp' );
		$ym = date( 'Y-m', $t );
		$u  = add_query_arg( 'mrt_month', $ym, home_url( '/' ) );

		return array( $u, $u );
	}
	$prev_ts = strtotime( '-1 month', $first_ts );
	$next_ts = strtotime( '+1 month', $first_ts );
	if ( false === $prev_ts ) {
		$prev_ts = $first_ts;
	}
	if ( false === $next_ts ) {
		$next_ts = $first_ts;
	}

	return array(
		add_query_arg( 'mrt_month', date( 'Y-m', $prev_ts ) ),
		add_query_arg( 'mrt_month', date( 'Y-m', $next_ts ) ),
	);
}

/**
 * Output one calendar day cell (inactive or service day button)
 *
 * @param int                                      $day_num Day of month 1–31
 * @param array{ymd:string,count:int,running:bool} $info Day meta
 * @param array<string, mixed>                     $atts Shortcode atts (show_counts)
 * @return void
 */
function MRT_month_shortcode_render_day_cell( int $day_num, array $info, array $atts ): void {
	if ( ! $info['running'] ) {
		echo '<td class="mrt-day-cell mrt-day-cell--inactive"><span class="mrt-daynum">' . intval( $day_num ) . '</span></td>';
		return;
	}

	$date_label = date_i18n( get_option( 'date_format' ), strtotime( $info['ymd'] ) );
	if ( ! empty( $atts['show_counts'] ) ) {
		/* translators: 1: formatted date, 2: number of services */
		$aria = sprintf( __( 'Show timetable for %1$s, %2$d services', 'museum-railway-timetable' ), $date_label, $info['count'] );
	} else {
		$aria = sprintf( __( 'Show timetable for %s', 'museum-railway-timetable' ), $date_label );
	}

	echo '<td class="mrt-day-cell mrt-day-cell--running">';
	echo '<button type="button" class="mrt-day mrt-running mrt-day-clickable mrt-cursor-pointer" data-date="' . esc_attr( $info['ymd'] ) . '" aria-pressed="false" aria-label="' . esc_attr( $aria ) . '">';
	echo '<span class="mrt-daynum" aria-hidden="true">' . intval( $day_num ) . '</span>';
	if ( ! empty( $atts['show_counts'] ) ) {
		echo '<span class="mrt-dot" aria-hidden="true">' . intval( $info['count'] ) . '</span>';
	} else {
		echo '<span class="mrt-dot" aria-hidden="true">&bull;</span>';
	}
	echo '</button></td>';
}

/**
 * Build per-day running/count data for the month grid
 *
 * @param int                  $year Year
 * @param int                  $month Month 1–12
 * @param int                  $daysInMonth Length of month
 * @param array<string, mixed> $atts Shortcode atts
 * @return array<int, array{ymd:string,count:int,running:bool}>
 */
function MRT_month_shortcode_collect_day_meta( int $year, int $month, int $daysInMonth, array $atts ): array {
	$dates = array();
	for ( $d = 1; $d <= $daysInMonth; $d++ ) {
		$ymd         = sprintf( '%04d-%02d-%02d', $year, $month, $d );
		$service_ids = MRT_services_running_on_date( $ymd, $atts['train_type'], $atts['service'] );
		$dates[ $d ] = array(
			'ymd'     => $ymd,
			'count'   => count( $service_ids ),
			'running' => ! empty( $service_ids ),
		);
	}

	return $dates;
}

/**
 * Output tbody row(s) for the month grid (weeks and day cells)
 *
 * @param int|false                                            $first_ts First-of-month timestamp
 * @param int                                                  $weekdayFirst ISO weekday of first day
 * @param bool                                                 $startMonday Week layout
 * @param int                                                  $daysInMonth Days in month
 * @param array<int, array{ymd:string,count:int,running:bool}> $dates Day meta
 * @param array<string, mixed>                                 $atts Shortcode atts
 * @return void
 */
function MRT_month_shortcode_echo_calendar_rows( $first_ts, $weekdayFirst, $startMonday, $daysInMonth, array $dates, array $atts ): void {
	$emptyCells = $startMonday ? ( $weekdayFirst - 1 ) : ( intval( date( 'w', $first_ts ) ) );
	echo '<tr>';
	for ( $i = 0; $i < $emptyCells; $i++ ) {
		echo '<td class="mrt-empty"></td>';
	}

	$colIndex = $emptyCells;
	for ( $d = 1; $d <= $daysInMonth; $d++ ) {
		$info = $dates[ $d ];
		MRT_month_shortcode_render_day_cell( $d, $info, $atts );
		++$colIndex;
		if ( $colIndex % 7 === 0 && $d < $daysInMonth ) {
			echo '</tr><tr>';
		}
	}

	$remaining = ( 7 - ( $colIndex % 7 ) ) % 7;
	for ( $i = 0; $i < $remaining; $i++ ) {
		echo '<td class="mrt-empty"></td>';
	}
	echo '</tr>';
}

/**
 * Month navigation + title row
 *
 * @param int|false $first_ts First day of month timestamp
 * @param string    $month_title Localized month and year
 * @param int       $nav         Shortcode nav attribute (0|1)
 * @return void
 */
function MRT_month_shortcode_echo_nav( $first_ts, $month_title, $nav ): void {
	if ( ! empty( $nav ) ) {
		$nav_urls = MRT_month_shortcode_nav_link_urls( $first_ts );
		echo '<div class="mrt-month-nav" role="navigation" aria-label="' . esc_attr__( 'Month navigation', 'museum-railway-timetable' ) . '">';
		echo '<a class="mrt-btn mrt-btn--secondary mrt-month-nav__prev" href="' . esc_url( $nav_urls[0] ) . '">';
		echo '<span class="mrt-month-nav__chev" aria-hidden="true">‹</span> ';
		echo esc_html__( 'Previous month', 'museum-railway-timetable' );
		echo '</a>';
		echo '<h2 class="mrt-month-nav__title mrt-heading mrt-heading--lg mrt-font-semibold">' .
			esc_html( $month_title ) . '</h2>';
		echo '<a class="mrt-btn mrt-btn--secondary mrt-month-nav__next" href="' . esc_url( $nav_urls[1] ) . '">';
		echo esc_html__( 'Next month', 'museum-railway-timetable' );
		echo ' <span class="mrt-month-nav__chev" aria-hidden="true">›</span>';
		echo '</a></div>';
		return;
	}
	echo '<div class="mrt-heading mrt-heading--lg mrt-font-semibold">' . esc_html( $month_title ) . '</div>';
}

/**
 * Full month markup (region, table, legend, timetable panel)
 *
 * @param int|false                                            $first_ts First day of month
 * @param array<string, mixed>                                 $atts Shortcode attributes
 * @param array<int, array{ymd:string,count:int,running:bool}> $dates Per-day meta
 * @param int                                                  $daysInMonth Days in month
 * @param int                                                  $weekdayFirst Weekday of first (1–7 or 0–6 context)
 * @param bool                                                 $startMonday Week starts Monday
 * @param string                                               $month_uid Unique id for timetable panel
 * @param string                                               $month_title Localized month and year
 * @return string HTML
 */
function MRT_month_shortcode_render_full( $first_ts, array $atts, array $dates, $daysInMonth, $weekdayFirst, $startMonday, $month_uid, $month_title ): string {
	ob_start();
	echo '<div class="mrt-month mrt-my-1" role="region" aria-label="' . esc_attr(
		sprintf(
			/* translators: %s: month and year, e.g. April 2026 */
			__( 'Timetable month view, %s', 'museum-railway-timetable' ),
			$month_title
		)
	) . '" data-train-type="' . esc_attr( $atts['train_type'] ) . '">';
	MRT_month_shortcode_echo_nav( $first_ts, $month_title, (int) $atts['nav'] );
	echo '<table class="mrt-month-table">';
	echo '<caption class="mrt-month-table__caption">' . esc_html(
		sprintf(
			/* translators: %s: month and year */
			__( 'Operating days for %s', 'museum-railway-timetable' ),
			$month_title
		)
	) . '</caption>';
	echo '<thead><tr>';
	$headers = $startMonday
		? array( __( 'Mon' ), __( 'Tue' ), __( 'Wed' ), __( 'Thu' ), __( 'Fri' ), __( 'Sat' ), __( 'Sun' ) )
		: array( __( 'Sun' ), __( 'Mon' ), __( 'Tue' ), __( 'Wed' ), __( 'Thu' ), __( 'Fri' ), __( 'Sat' ) );
	foreach ( $headers as $h ) {
		echo '<th scope="col">' . esc_html( $h ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	MRT_month_shortcode_echo_calendar_rows( $first_ts, $weekdayFirst, $startMonday, $daysInMonth, $dates, $atts );
	echo '</tbody></table>';

	if ( ! empty( $atts['legend'] ) ) {
		echo '<div class="mrt-legend mrt-text-base mrt-text-primary mrt-mt-sm">';
		echo '<span class="mrt-legend-item mrt-inline-flex mrt-items-center mrt-gap-xs mrt-mr-sm"><span class="mrt-dot mrt-dot--green" aria-hidden="true"></span> ' . esc_html__( 'Service day', 'museum-railway-timetable' ) . '</span>';
		if ( ! empty( $atts['show_counts'] ) ) {
			echo ' <span class="mrt-text-small mrt-opacity-85">(' . esc_html__( 'count per day', 'museum-railway-timetable' ) . ')</span>';
		}
		echo ' <span class="mrt-text-tertiary mrt-text-small">(' . esc_html__( 'Click to view timetable', 'museum-railway-timetable' ) . ')</span>';
		echo '</div>';
	}
	echo '<div class="mrt-box mrt-day-timetable-container mrt-mt-xl mrt-hidden" id="' . esc_attr( $month_uid ) . '-panel" role="region" aria-live="polite" aria-relevant="additions text" aria-busy="false" tabindex="-1" aria-label="' . esc_attr__( 'Selected day timetable', 'museum-railway-timetable' ) . '"></div>';
	echo '</div>';

	return ob_get_clean();
}

/**
 * Render month view shortcode output
 *
 * @param array $atts Shortcode attributes
 * @return string HTML
 */
/**
 * Resolve calendar month start timestamp from shortcode atts.
 *
 * @param array<string, mixed> $atts
 */
function MRT_month_shortcode_resolve_month_start( array $atts, int $now_ts ): int|false {
	if ( ! empty( $atts['month'] ) && preg_match( '/^\d{4}-\d{2}$/', (string) $atts['month'] ) ) {
		$firstDay = $atts['month'] . '-01';
		$first_ts = strtotime( $firstDay . ' 00:00:00', $now_ts );
		if ( false === $first_ts ) {
			return strtotime( date( 'Y-m-01', $now_ts ) );
		}
		return $first_ts;
	}
	return strtotime( date( 'Y-m-01', $now_ts ) );
}

function MRT_render_shortcode_month( $atts ) {
	$atts = shortcode_atts(
		array(
			'month'        => '',
			'train_type'   => '',
			'service'      => '',
			'legend'       => 1,
			'show_counts'  => 1,
			'start_monday' => 1,
			'nav'          => 1,
		),
		$atts,
		'museum_timetable_month'
	);

	$atts = MRT_month_shortcode_apply_query_month( $atts );

	$datetime = MRT_get_current_datetime();
	$now_ts   = $datetime['timestamp'];
	$first_ts = MRT_month_shortcode_resolve_month_start( $atts, $now_ts );

	if ( false === $first_ts ) {
		return MRT_render_alert( __( 'Invalid date.', 'museum-railway-timetable' ), 'error' );
	}

	$year        = (int) date( 'Y', $first_ts );
	$month       = (int) date( 'm', $first_ts );
	$daysInMonth = (int) date( 't', $first_ts );
	if ( $year <= 0 || $month <= 0 || $month > 12 || $daysInMonth <= 0 ) {
		return MRT_render_alert( __( 'Invalid date.', 'museum-railway-timetable' ), 'error' );
	}

	$weekdayFirst = (int) date( 'N', $first_ts );
	$startMonday  = ! empty( $atts['start_monday'] );

	$dates = MRT_month_shortcode_collect_day_meta( $year, $month, $daysInMonth, $atts );

	$month_uid   = wp_unique_id( 'mrtmonth' );
	$month_title = date_i18n( 'F Y', $first_ts );

	return MRT_month_shortcode_render_full(
		$first_ts,
		$atts,
		$dates,
		$daysInMonth,
		$weekdayFirst,
		$startMonday,
		$month_uid,
		$month_title
	);
}
