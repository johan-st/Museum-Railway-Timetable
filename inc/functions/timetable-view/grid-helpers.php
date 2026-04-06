<?php
/**
 * Timetable grid – helper functions
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Accessible name for overview grid time cells (station row + service + time text)
 *
 * @param string        $station_row_label First-column label for this row
 * @param array<string> $label_parts       Train type and/or service number fragments
 * @param string        $time_display      Visible time/symbol text
 * @return string
 */
function MRT_overview_grid_cell_aria_label(string $station_row_label, array $label_parts, string $time_display): string {
    $svc = trim(implode(' ', array_filter($label_parts)));
    $time_display = trim(wp_strip_all_tags($time_display));

    return sprintf(
        /* translators: 1: station row, 2: train/service label, 3: time or symbol */
        __('%1$s. %2$s: %3$s', 'museum-railway-timetable'),
        $station_row_label,
        $svc,
        $time_display
    );
}

/**
 * Render a time cell for the timetable
 */
function MRT_render_time_cell($stop_time, $service_classes, $service_info, $idx) {
    $time_display = MRT_format_stop_time_display($stop_time);
    $label_parts = [];
    if ($service_info[$idx]['train_type']) {
        $label_parts[] = $service_info[$idx]['train_type']->name;
    }
    $label_parts[] = $service_info[$idx]['service_number'];

    $html = '<td class="mrt-time-cell ' . esc_attr(implode(' ', $service_classes[$idx])) . '" ';
    $html .= 'data-service-number="' . esc_attr($service_info[$idx]['service_number']) . '" ';
    $html .= 'data-service-label="' . esc_attr(implode(' ', $label_parts)) . '">';
    $html .= esc_html($time_display);
    $html .= '</td>';

    return $html;
}

/**
 * Get time display for "Från" row (departure time)
 */
function MRT_get_from_row_display_stop_time($stop_time) {
    if (!$stop_time) return null;
    $time_to_show = !empty($stop_time['departure_time']) ? $stop_time['departure_time'] : ($stop_time['arrival_time'] ?? '');
    if (!$time_to_show) return $stop_time;
    return [
        'arrival_time' => '',
        'departure_time' => MRT_format_time_display($time_to_show),
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get time display for "Till" row (arrival time)
 */
function MRT_get_to_row_display_stop_time($stop_time) {
    if (!$stop_time) return null;
    $time_to_show = !empty($stop_time['arrival_time']) ? $stop_time['arrival_time'] : ($stop_time['departure_time'] ?? '');
    if (!$time_to_show) return $stop_time;
    return [
        'arrival_time' => MRT_format_time_display($time_to_show),
        'departure_time' => '',
        'pickup_allowed' => true,
        'dropoff_allowed' => true,
    ];
}

/**
 * Get label parts for a service (train type + service number)
 */
function MRT_get_service_label_parts($info) {
    $parts = [];
    if (!empty($info['train_type'])) {
        $parts[] = $info['train_type']->name;
    }
    $parts[] = $info['service_number'];
    return $parts;
}

/**
 * Build HTML and plain transfer connection strings for one service column
 *
 * @param array<int, array<string, mixed>> $connections Connection rows
 * @return array{0: array<int, string>, 1: array<int, string>} HTML fragments, plain text
 */
function MRT_render_grid_transfer_conn_chunks(array $connections): array {
    $conn_text = [];
    $conn_plain = [];
    foreach ($connections as $conn) {
        $plain_num = $conn['service_number'];
        $time_bit = '';
        if (!empty($conn['to_departure'])) {
            $time_bit = MRT_format_time_display($conn['to_departure']);
        } elseif (!empty($conn['departure_time'])) {
            $time_bit = MRT_format_time_display($conn['departure_time']);
        }
        $conn_plain[] = trim($plain_num . ' ' . $time_bit);
        $conn_str = esc_html($conn['service_number']);
        if (!empty($conn['to_departure'])) {
            $conn_str .= ' ' . esc_html(MRT_format_time_display($conn['to_departure']));
        } elseif (!empty($conn['departure_time'])) {
            $conn_str .= ' ' . esc_html(MRT_format_time_display($conn['departure_time']));
        }
        $conn_text[] = $conn_str;
    }

    return [$conn_text, $conn_plain];
}
