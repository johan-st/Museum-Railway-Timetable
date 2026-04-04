<?php
/**
 * Normalize journey results for JSON API / frontends
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Sum leg durations; null if any segment missing times
 *
 * @param array<int, array<string, mixed>> $legs Leg payloads
 * @return int|null
 */
function MRT_normalize_total_duration_from_legs(array $legs) {
    $total = 0;
    foreach ($legs as $leg) {
        $dep = $leg['from_departure'] ?? '';
        $arr = $leg['to_arrival'] ?? '';
        $m = MRT_format_duration_minutes($dep, $arr);
        if ($m === null) {
            return null;
        }
        $total += $m;
    }
    return $total;
}

/**
 * Build segments / notice for one service connection
 *
 * @param int    $service_id Service
 * @param int    $from_id From station
 * @param int    $to_id To station
 * @param string $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_segments_single_service($service_id, $from_id, $to_id, $dateYmd) {
    $detail = MRT_get_connection_journey_detail($service_id, $from_id, $to_id);
    return [
        'segments' => $detail['stops'],
        'duration_minutes' => $detail['duration_minutes'],
        'notice' => MRT_get_service_notice($service_id, $dateYmd),
    ];
}

/**
 * One-leg wrapped direct → flat connection row for normalizer
 *
 * @param array<string, mixed> $item Wrapped direct multi
 * @return array<string, mixed>|null
 */
function MRT_flatten_wrapped_direct_connection(array $item) {
    if (($item['connection_type'] ?? '') !== 'direct' || empty($item['legs'][0])) {
        return null;
    }
    $leg = $item['legs'][0];
    $sid = (int) ($leg['service_id'] ?? 0);
    if ($sid <= 0) {
        return null;
    }
    $route_id = get_post_meta($sid, 'mrt_service_route_id', true);
    $dest = MRT_get_service_destination($sid);
    return [
        'service_id' => $sid,
        'service_name' => get_the_title($sid) ?: ('#' . $sid),
        'route_name' => $route_id ? get_the_title((int) $route_id) : '',
        'destination' => $dest['destination'],
        'direction' => $dest['direction'],
        'train_type' => (string) ($leg['train_type'] ?? ''),
        'from_departure' => (string) ($leg['from_departure'] ?? ''),
        'from_arrival' => '',
        'to_arrival' => (string) ($leg['to_arrival'] ?? ''),
        'to_departure' => '',
        'from_sequence' => 0,
        'to_sequence' => 0,
    ];
}

/**
 * Normalize multi-leg bundle for API
 *
 * @param array<string, mixed> $item Must contain legs[]
 * @param string               $dateYmd Date
 * @return array<string, mixed>
 */
function MRT_normalize_multi_leg_for_api(array $item, $dateYmd) {
    $legs = $item['legs'];
    $duration = MRT_normalize_total_duration_from_legs($legs);
    $notices = [];
    foreach ($legs as $leg) {
        $nid = isset($leg['service_id']) ? (int) $leg['service_id'] : 0;
        if ($nid <= 0) {
            continue;
        }
        $n = MRT_get_service_notice($nid, $dateYmd);
        if ($n !== '') {
            $notices[] = $n;
        }
    }
    $last = count($legs) - 1;
    return [
        'connection_type' => $item['connection_type'] ?? 'transfer',
        'transfer_station_id' => $item['transfer_station_id'] ?? null,
        'legs' => $legs,
        'duration_minutes' => $duration,
        'segments' => [],
        'notice' => implode("\n", array_unique($notices)),
        'service_id' => isset($legs[0]['service_id']) ? (int) $legs[0]['service_id'] : 0,
        'departure' => $legs[0]['from_departure'] ?? '',
        'arrival' => $legs[$last]['to_arrival'] ?? '',
        'train_type' => $legs[0]['train_type'] ?? '',
    ];
}

/**
 * Unified connection payload (direct row or multi-leg bundle)
 *
 * @param array<string, mixed> $item Either flat connection or multi-leg array
 * @param string               $dateYmd Date
 * @param int                  $from_station_id Search from
 * @param int                  $to_station_id Search to
 * @return array<string, mixed>
 */
function MRT_normalize_connection_for_api($item, $dateYmd, $from_station_id, $to_station_id) {
    $flat = MRT_flatten_wrapped_direct_connection($item);
    if ($flat !== null) {
        $item = $flat;
    }
    if (isset($item['legs']) && is_array($item['legs']) && count($item['legs']) > 1) {
        return MRT_normalize_multi_leg_for_api($item, $dateYmd);
    }
    $conn = $item;
    $sid = intval($conn['service_id'] ?? 0);
    $dep = MRT_connection_row_departure_at_from($conn);
    $arr = !empty($conn['to_arrival']) ? (string) $conn['to_arrival'] : (string) ($conn['to_departure'] ?? '');
    $extra = MRT_normalize_segments_single_service($sid, $from_station_id, $to_station_id, $dateYmd);
    $dur = $extra['duration_minutes'];
    if ($dur === null) {
        $dur = MRT_format_duration_minutes($dep, $arr);
    }
    return [
        'connection_type' => 'direct',
        'transfer_station_id' => null,
        'legs' => [],
        'service_id' => $sid,
        'departure' => $dep,
        'arrival' => $arr,
        'duration_minutes' => $dur,
        'train_type' => (string) ($conn['train_type'] ?? ''),
        'service_name' => (string) ($conn['service_name'] ?? ''),
        'route_name' => (string) ($conn['route_name'] ?? ''),
        'destination' => (string) ($conn['destination'] ?? ''),
        'segments' => $extra['segments'],
        'notice' => $extra['notice'],
    ];
}
