<?php
/**
 * Service helper functions for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get train type for a service, with optional date-specific support
 *
 * @param int $service_id Service post ID
 * @param string|null $dateYmd Optional date in YYYY-MM-DD format for date-specific train types
 * @return WP_Term|null Train type term object or null if not found
 */
function MRT_get_service_train_type($service_id, $dateYmd = null) {
    if (!$service_id || $service_id <= 0) {
        return null;
    }
    
    // Use date-specific train type if date provided and function exists
    if ($dateYmd && function_exists('MRT_get_service_train_type_for_date')) {
        return MRT_get_service_train_type_for_date($service_id, $dateYmd);
    }
    
    // Fall back to default train type from taxonomy
    $train_types = wp_get_post_terms($service_id, 'mrt_train_type', ['fields' => 'all']);
    if (!empty($train_types) && !is_wp_error($train_types)) {
        return $train_types[0];
    }
    
    return null;
}

/**
 * Get destination station name for a service
 * Returns the end station name if set, otherwise falls back to direction (dit/från)
 *
 * @param int $service_id Service post ID
 * @return array Array with 'destination' (station name or direction), 'direction' (for backward compatibility), and 'end_station_id'
 */
function MRT_get_service_destination($service_id) {
    if (!$service_id || $service_id <= 0) {
        return [
            'destination' => '',
            'direction' => '',
            'end_station_id' => 0,
        ];
    }
    
    $destination = '';
    $direction = '';
    $end_station_id = get_post_meta($service_id, 'mrt_service_end_station_id', true);
    
    if ($end_station_id) {
        $end_station = get_post($end_station_id);
        if ($end_station) {
            $destination = $end_station->post_title;
        }
    }
    
    // Fallback to direction if no end station (backward compatibility)
    if (empty($destination)) {
        $direction = get_post_meta($service_id, 'mrt_direction', true);
        if ($direction === 'dit') {
            $destination = __('Dit', 'museum-railway-timetable');
        } elseif ($direction === 'från') {
            $destination = __('Från', 'museum-railway-timetable');
        }
    }
    
    return [
        'destination' => $destination,
        'direction' => $direction !== '' ? $direction : '',
        'end_station_id' => $end_station_id ? intval($end_station_id) : 0,
    ];
}

/**
 * Get stop times for a service, indexed by station ID
 *
 * @param int $service_id Service post ID
 * @return array Array of stop times indexed by station_post_id
 */
function MRT_get_service_stop_times($service_id) {
    global $wpdb;
    
    if (!$service_id || $service_id <= 0) {
        return [];
    }
    
    $stoptimes_table = $wpdb->prefix . 'mrt_stoptimes';
    $stop_times = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $stoptimes_table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
        $service_id
    ), ARRAY_A);
    
    if (MRT_check_db_error('MRT_get_service_stop_times')) {
        return [];
    }
    
    $stop_times_by_station = [];
    foreach ($stop_times as $st) {
        $stop_times_by_station[$st['station_post_id']] = $st;
    }
    
    return $stop_times_by_station;
}

/**
 * Stop times for a service ordered by stop_sequence (list rows)
 *
 * @param int $service_id Service post ID
 * @return array<int, array<string, mixed>> List of DB row arrays
 */
function MRT_get_service_stop_times_ordered($service_id) {
    global $wpdb;
    if (!$service_id || $service_id <= 0) {
        return [];
    }
    $table = $wpdb->prefix . 'mrt_stoptimes';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE service_post_id = %d ORDER BY stop_sequence ASC",
        $service_id
    ), ARRAY_A);
    if (MRT_check_db_error('MRT_get_service_stop_times_ordered') || !$rows) {
        return [];
    }
    return $rows;
}

/**
 * Effective departure time at a stop (for boarding / first movement)
 *
 * @param array<string, mixed> $row Stoptime row
 * @return string Empty or HH:MM
 */
function MRT_stop_effective_departure(array $row) {
    if (!empty($row['departure_time'])) {
        return (string) $row['departure_time'];
    }
    return !empty($row['arrival_time']) ? (string) $row['arrival_time'] : '';
}

/**
 * Effective arrival time at a stop (for alighting)
 *
 * @param array<string, mixed> $row Stoptime row
 * @return string Empty or HH:MM
 */
function MRT_stop_effective_arrival(array $row) {
    if (!empty($row['arrival_time'])) {
        return (string) $row['arrival_time'];
    }
    return !empty($row['departure_time']) ? (string) $row['departure_time'] : '';
}

/**
 * Get timetable dates, handling both array and legacy single date format
 *
 * @param int $timetable_id Timetable post ID
 * @return array Array of dates in YYYY-MM-DD format
 */
function MRT_get_timetable_dates($timetable_id) {
    if (!$timetable_id || $timetable_id <= 0) {
        return [];
    }
    
    $timetable_dates = get_post_meta($timetable_id, 'mrt_timetable_dates', true);
    
    // Handle array format (new)
    if (is_array($timetable_dates)) {
        return $timetable_dates;
    }
    
    // Handle legacy single date field (old)
    $old_date = get_post_meta($timetable_id, 'mrt_timetable_date', true);
    if (!empty($old_date)) {
        return [$old_date];
    }
    
    return [];
}

/**
 * Group services by route and direction
 * Prepares services for timetable rendering
 *
 * @param array $services Array of service post objects
 * @param string|null $dateYmd Optional date for date-specific train types
 * @return array Grouped services array
 */
function MRT_group_services_by_route($services, $dateYmd = null) {
    global $wpdb;
    
    if (empty($services)) {
        return [];
    }
    
    $grouped_services = [];
    $stoptimes_table = $wpdb->prefix . 'mrt_stoptimes';
    
    foreach ($services as $service) {
        $route_id = get_post_meta($service->ID, 'mrt_service_route_id', true);
        $direction = get_post_meta($service->ID, 'mrt_direction', true);
        
        if (!$route_id) {
            continue;
        }
        
        // Get route info
        $route = get_post($route_id);
        if (!$route) {
            continue;
        }
        
        // Get route stations using helper function
        $route_stations = MRT_get_route_stations($route_id);
        
        // Get train type using helper function
        $train_type = MRT_get_service_train_type($service->ID, $dateYmd);
        
        // Create group key: route_id + direction
        $group_key = $route_id . '_' . $direction;
        
        if (!isset($grouped_services[$group_key])) {
            $grouped_services[$group_key] = [
                'route' => $route,
                'route_id' => $route_id,
                'direction' => $direction,
                'stations' => $route_stations,
                'services' => [],
            ];
        }
        
        // Get stop times using helper function
        $stop_times_by_station = MRT_get_service_stop_times($service->ID);
        
        $grouped_services[$group_key]['services'][] = [
            'service' => $service,
            'train_type' => $train_type,
            'stop_times' => $stop_times_by_station,
        ];
    }
    
    return $grouped_services;
}
