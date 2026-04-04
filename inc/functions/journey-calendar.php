<?php
/**
 * Calendar day states for journey search (public / API)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Status for one day: no traffic, traffic but no connection, or ok
 *
 * @param int    $from_station_id From station
 * @param int    $to_station_id To station
 * @param string $ymd Date YYYY-MM-DD
 * @param array<string, array<int>> $services_cache Ref-filled cache date => service ids
 * @return string none|traffic_no_match|ok
 */
function MRT_journey_calendar_day_status($from_station_id, $to_station_id, $ymd, array &$services_cache) {
    if (!isset($services_cache[$ymd])) {
        $services_cache[$ymd] = MRT_services_running_on_date($ymd);
    }
    if (empty($services_cache[$ymd])) {
        return 'none';
    }
    $conns = MRT_find_connections($from_station_id, $to_station_id, $ymd);
    return !empty($conns) ? 'ok' : 'traffic_no_match';
}

/**
 * Per-day status for a calendar month (YYYY-MM-DD => state)
 *
 * @param int $from_station_id From station
 * @param int $to_station_id To station
 * @param int $year Year
 * @param int $month Month 1-12
 * @return array<string, string>
 */
function MRT_get_journey_calendar_month($from_station_id, $to_station_id, $year, $month) {
    $out = [];
    if ($from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id) {
        return $out;
    }
    $year = (int) $year;
    $month = (int) $month;
    if ($year < 1970 || $year > 2100 || $month < 1 || $month > 12) {
        return $out;
    }
    $days = (int) gmdate('t', gmmktime(0, 0, 0, $month, 1, $year));
    $services_cache = [];
    for ($d = 1; $d <= $days; $d++) {
        $ymd = sprintf('%04d-%02d-%02d', $year, $month, $d);
        if (!MRT_validate_date($ymd)) {
            continue;
        }
        $out[$ymd] = MRT_journey_calendar_day_status(
            $from_station_id,
            $to_station_id,
            $ymd,
            $services_cache
        );
    }
    return $out;
}

/**
 * All dates in [from_ymd, to_ymd] where any service runs (union of timetable dates)
 *
 * @param string $from_ymd Start YYYY-MM-DD
 * @param string $to_ymd End YYYY-MM-DD inclusive
 * @return string[] Sorted unique dates
 */
function MRT_get_all_traffic_dates_in_range($from_ymd, $to_ymd) {
    if (!MRT_validate_date($from_ymd) || !MRT_validate_date($to_ymd)) {
        return [];
    }
    if (strcmp($from_ymd, $to_ymd) > 0) {
        return [];
    }
    $timetables = get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);
    $set = [];
    foreach ($timetables as $tid) {
        foreach (MRT_get_timetable_dates($tid) as $d) {
            if (!MRT_validate_date($d)) {
                continue;
            }
            if ($d >= $from_ymd && $d <= $to_ymd) {
                $set[$d] = true;
            }
        }
    }
    $dates = array_keys($set);
    sort($dates);
    return $dates;
}
