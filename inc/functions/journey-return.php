<?php
/**
 * Return-trip connection helpers
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Return connections (to → from) after outbound arrival + turnaround
 *
 * @param int    $from_station_id Outbound origin (becomes return destination)
 * @param int    $to_station_id Outbound destination (return origin)
 * @param string $dateYmd Date YYYY-MM-DD
 * @param string $outbound_arrival_hhmm Arrival at to_station on outbound
 * @param int    $min_turnaround_minutes Minimum minutes before return may depart
 * @return array<int, array<string, mixed>> Same shape as MRT_find_connections
 */
function MRT_find_return_connections(
    $from_station_id,
    $to_station_id,
    $dateYmd,
    $outbound_arrival_hhmm,
    $min_turnaround_minutes = 0
) {
    if ($from_station_id <= 0 || $to_station_id <= 0) {
        return [];
    }
    if (!MRT_validate_date($dateYmd) || MRT_time_hhmm_to_minutes($outbound_arrival_hhmm) === null) {
        return [];
    }
    $all = MRT_find_connections($to_station_id, $from_station_id, $dateYmd);
    if (empty($all)) {
        return [];
    }
    $earliest = MRT_add_minutes_to_hhmm($outbound_arrival_hhmm, (int) $min_turnaround_minutes);
    if ($earliest === null) {
        return [];
    }
    $filtered = [];
    foreach ($all as $row) {
        $dep = MRT_connection_row_departure_at_from($row);
        if ($dep === '' || !MRT_validate_time_hhmm($dep)) {
            continue;
        }
        if (MRT_compare_hhmm($dep, $earliest) >= 0) {
            $filtered[] = $row;
        }
    }
    return $filtered;
}
