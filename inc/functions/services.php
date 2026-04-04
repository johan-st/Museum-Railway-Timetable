<?php
/**
 * Service-related functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get train type for a service on a specific date
 * Checks date-specific train types first, then falls back to default train type
 *
 * @param int $service_id Service post ID
 * @param string $dateYmd Date in YYYY-MM-DD format (optional, defaults to today)
 * @return WP_Term|null Train type term object or null if not found
 */
function MRT_get_service_train_type_for_date($service_id, $dateYmd = null) {
    if (!$service_id) {
        return null;
    }
    
    // Use current date if not provided
    if ($dateYmd === null) {
        $datetime = MRT_get_current_datetime();
        $dateYmd = $datetime['date'];
    }
    
    // Validate date format
    if (!MRT_validate_date($dateYmd)) {
        return null;
    }
    
    // Check for date-specific train type
    $train_types_by_date = get_post_meta($service_id, 'mrt_service_train_types_by_date', true);
    if (is_array($train_types_by_date) && isset($train_types_by_date[$dateYmd])) {
        $train_type_id = intval($train_types_by_date[$dateYmd]);
        if ($train_type_id > 0) {
            $train_type = get_term($train_type_id, 'mrt_train_type');
            if ($train_type && !is_wp_error($train_type)) {
                return $train_type;
            }
        }
    }
    
    // Fall back to default train type from taxonomy
    $train_types = wp_get_post_terms($service_id, 'mrt_train_type', ['fields' => 'all']);
    if (!empty($train_types) && !is_wp_error($train_types)) {
        return $train_types[0];
    }
    
    return null;
}

/**
 * Get timetable IDs that include a given date
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return array Array of timetable post IDs
 */
function MRT_get_timetables_for_date($dateYmd) {
    return get_posts([
        'post_type' => 'mrt_timetable',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => 'mrt_timetable_dates',
            'value' => $dateYmd,
            'compare' => 'LIKE',
        ]],
    ]);
}

/**
 * Filter service IDs to those actually running on date (verify timetable dates)
 *
 * @param array $service_ids Candidate service IDs
 * @param array $timetable_ids Timetable IDs that include the date
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return array Valid service IDs
 */
function MRT_filter_services_verified_for_date($service_ids, $timetable_ids, $dateYmd) {
    $valid = [];
    foreach ($service_ids as $service_id) {
        $timetable_id = get_post_meta($service_id, 'mrt_service_timetable_id', true);
        if (!$timetable_id || !in_array($timetable_id, $timetable_ids)) {
            continue;
        }
        $timetable_dates = MRT_get_timetable_dates($timetable_id);
        if (in_array($dateYmd, $timetable_dates, true)) {
            $valid[] = $service_id;
        }
    }
    return $valid;
}

/**
 * Resolve which services run on a given date (using Timetables)
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param string $train_type_slug Optional train type taxonomy slug
 * @param string $service_title_exact Optional exact service title
 * @return array Array of service post IDs
 */
function MRT_services_running_on_date($dateYmd, $train_type_slug = '', $service_title_exact = '') {
    if (!MRT_validate_date($dateYmd)) {
        return [];
    }

    $timetables = MRT_get_timetables_for_date($dateYmd);
    if (empty($timetables)) {
        return [];
    }

    $service_ids = get_posts([
        'post_type' => 'mrt_service',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => 'mrt_service_timetable_id',
            'value' => $timetables,
            'compare' => 'IN',
        ]],
    ]);

    $valid_service_ids = MRT_filter_services_verified_for_date($service_ids, $timetables, $dateYmd);
    if (empty($valid_service_ids)) {
        return [];
    }

    if ($service_title_exact !== '') {
        $post = MRT_get_post_by_title($service_title_exact, 'mrt_service');
        if (!$post) return [];
        $valid_service_ids = array_values(array_intersect($valid_service_ids, [intval($post->ID)]));
        if (empty($valid_service_ids)) return [];
    }

    if ($train_type_slug) {
        $q = new WP_Query([
            'post_type' => 'mrt_service',
            'post__in' => $valid_service_ids,
            'fields' => 'ids',
            'nopaging' => true,
            'tax_query' => [[
                'taxonomy' => 'mrt_train_type',
                'field' => 'slug',
                'terms' => sanitize_title($train_type_slug),
            ]],
        ]);
        return $q->posts;
    }

    return array_values(array_unique($valid_service_ids));
}

/**
 * Map stoptime rows to departure result format
 *
 * @param array $rows Raw DB rows
 * @return array Formatted departure data
 */
function MRT_map_departure_rows_to_result($rows) {
    $out = [];
    foreach ($rows as $r) {
        $service_id = intval($r['service_post_id']);
        $destination_data = MRT_get_service_destination($service_id);
        $out[] = [
            'service_id' => $service_id,
            'service_name' => get_the_title($service_id) ?: ('#' . $service_id),
            'arrival_time' => $r['arrival_time'],
            'departure_time' => $r['departure_time'],
            'destination' => $destination_data['destination'],
            'direction' => $destination_data['direction'],
        ];
    }
    return $out;
}

/**
 * Get next departures from a station after a given time
 *
 * @param int    $station_id Station post ID
 * @param array  $service_ids Array of service post IDs
 * @param string $timeHHMM Time in HH:MM format
 * @param int    $limit Maximum number of departures to return
 * @param bool   $with_arrival Whether to include arrival times
 * @return array Array of departure data
 */
function MRT_next_departures_for_station($station_id, $service_ids, $timeHHMM, $limit = 5, $with_arrival = false) {
    global $wpdb;
    if (!$service_ids || $station_id <= 0 || $limit <= 0) {
        return [];
    }
    if (empty($timeHHMM) || !MRT_validate_time_hhmm($timeHHMM)) {
        return [];
    }

    $table = $wpdb->prefix . 'mrt_stoptimes';
    $in = implode(',', array_map('intval', $service_ids));
    $col_time = $with_arrival ? 'COALESCE(departure_time, arrival_time)' : 'departure_time';

    $sql = $wpdb->prepare("
        SELECT s.service_post_id, s.arrival_time, s.departure_time, s.stop_sequence
        FROM $table s
        WHERE s.station_post_id = %d
          AND s.service_post_id IN ($in)
          AND (
              (s.departure_time IS NOT NULL AND s.departure_time >= %s)
              OR (s.departure_time IS NULL AND s.arrival_time IS NOT NULL AND s.arrival_time >= %s)
          )
        ORDER BY $col_time ASC
        LIMIT %d
    ", $station_id, $timeHHMM, $timeHHMM, $limit);

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (MRT_check_db_error('MRT_next_departures_for_station') || !$rows) {
        return [];
    }

    return MRT_map_departure_rows_to_result($rows);
}

/**
 * Map connection DB rows to result format
 *
 * @param array $rows Raw DB rows
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return array Formatted connection data
 */
function MRT_map_connection_rows_to_result($rows, $dateYmd) {
    $connections = [];
    foreach ($rows as $r) {
        $service_id = intval($r['service_post_id']);
        $destination_data = MRT_get_service_destination($service_id);
        $train_type = MRT_get_service_train_type_for_date($service_id, $dateYmd);
        $route_id = get_post_meta($service_id, 'mrt_service_route_id', true);
        $connections[] = [
            'service_id' => $service_id,
            'service_name' => get_the_title($service_id) ?: ('#' . $service_id),
            'route_name' => $route_id ? get_the_title($route_id) : '',
            'destination' => $destination_data['destination'],
            'direction' => $destination_data['direction'],
            'train_type' => $train_type ? $train_type->name : '',
            'from_departure' => $r['from_departure'] ?: '',
            'from_arrival' => $r['from_arrival'] ?: '',
            'to_arrival' => $r['to_arrival'] ?: '',
            'to_departure' => $r['to_departure'] ?: '',
            'from_sequence' => intval($r['from_sequence']),
            'to_sequence' => intval($r['to_sequence']),
        ];
    }
    return $connections;
}

/**
 * First departure time at "from" leg of a connection row
 *
 * @param array<string, mixed> $conn Row from MRT_find_connections
 * @return string HH:MM or empty
 */
function MRT_connection_row_departure_at_from(array $conn) {
    if (!empty($conn['from_departure'])) {
        return (string) $conn['from_departure'];
    }
    return !empty($conn['from_arrival']) ? (string) $conn['from_arrival'] : '';
}

/**
 * Find connections (services) from one station to another on a specific date
 *
 * @param int    $from_station_id From station post ID
 * @param int    $to_station_id To station post ID
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @return array Array of connection data with service info, departure/arrival times
 */
function MRT_find_connections($from_station_id, $to_station_id, $dateYmd) {
    global $wpdb;
    if ($from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id) {
        return [];
    }
    if (!MRT_validate_date($dateYmd)) {
        return [];
    }

    $service_ids = MRT_services_running_on_date($dateYmd);
    if (empty($service_ids)) {
        return [];
    }

    $table = $wpdb->prefix . 'mrt_stoptimes';
    $in = implode(',', array_map('intval', $service_ids));
    $sql = $wpdb->prepare("
        SELECT from_st.service_post_id, from_st.departure_time as from_departure,
            from_st.arrival_time as from_arrival, from_st.stop_sequence as from_sequence,
            to_st.arrival_time as to_arrival, to_st.departure_time as to_departure,
            to_st.stop_sequence as to_sequence
        FROM $table from_st
        INNER JOIN $table to_st ON from_st.service_post_id = to_st.service_post_id
        WHERE from_st.station_post_id = %d AND to_st.station_post_id = %d
          AND from_st.service_post_id IN ($in) AND from_st.stop_sequence < to_st.stop_sequence
          AND ((from_st.pickup_allowed = 1 OR from_st.dropoff_allowed = 1)
               AND (to_st.pickup_allowed = 1 OR to_st.dropoff_allowed = 1))
        ORDER BY COALESCE(from_st.departure_time, from_st.arrival_time) ASC, from_st.stop_sequence ASC
    ", $from_station_id, $to_station_id);

    $rows = $wpdb->get_results($sql, ARRAY_A);
    if (MRT_check_db_error('MRT_find_connections') || !$rows) {
        return [];
    }

    return MRT_map_connection_rows_to_result($rows, $dateYmd);
}

/**
 * Connections where first leg departs not before a minimum time (same day)
 *
 * @param int    $from_station_id From station
 * @param int    $to_station_id To station
 * @param string $dateYmd Date YYYY-MM-DD
 * @param string $earliest_hhmm Minimum departure at from station
 * @return array<int, array<string, mixed>>
 */
function MRT_find_connections_departing_not_before($from_station_id, $to_station_id, $dateYmd, $earliest_hhmm) {
    if ($from_station_id <= 0 || $to_station_id <= 0 || $from_station_id === $to_station_id) {
        return [];
    }
    if (!MRT_validate_date($dateYmd) || !MRT_validate_time_hhmm($earliest_hhmm)) {
        return [];
    }
    $filtered = [];
    foreach (MRT_find_connections($from_station_id, $to_station_id, $dateYmd) as $row) {
        $dep = MRT_connection_row_departure_at_from($row);
        if ($dep !== '' && MRT_validate_time_hhmm($dep) && MRT_compare_hhmm($dep, $earliest_hhmm) >= 0) {
            $filtered[] = $row;
        }
    }
    return $filtered;
}

