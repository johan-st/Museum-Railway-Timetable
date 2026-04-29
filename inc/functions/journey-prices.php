<?php
/**
 * Public price matrix (option mrt_price_matrix)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Ticket-type keys (rows in mockup price table)
 *
 * @return string[]
 */
function MRT_price_ticket_type_keys() {
    return ['single', 'return', 'day'];
}

/**
 * Passenger category keys (columns)
 *
 * @return string[]
 */
function MRT_price_category_keys() {
    return ['adult', 'child_4_15', 'child_0_3', 'student_senior'];
}

/**
 * Price zone keys.
 *
 * @return int[]
 */
function MRT_price_zone_keys() {
    return [1, 2, 3, 4];
}

/**
 * 2025 Lennakatten fare table from Taxa 2025.
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_builtin_price_matrix() {
    return [
        'single' => [
            'adult' => [1 => 70, 2 => 110, 3 => 120, 4 => 130],
            'child_4_15' => [1 => 35, 2 => 55, 3 => 60, 4 => 65],
            'child_0_3' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'student_senior' => [1 => 60, 2 => 100, 3 => 110, 4 => 120],
        ],
        'return' => [
            'adult' => [1 => 140, 2 => 220, 3 => 240, 4 => 260],
            'child_4_15' => [1 => 70, 2 => 110, 3 => 120, 4 => 130],
            'child_0_3' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'student_senior' => [1 => 120, 2 => 200, 3 => 220, 4 => 240],
        ],
        'day' => [
            'adult' => [1 => 280, 2 => 280, 3 => 280, 4 => 280],
            'child_4_15' => [1 => 140, 2 => 140, 3 => 140, 4 => 140],
            'child_0_3' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'student_senior' => [1 => 260, 2 => 260, 3 => 260, 4 => 260],
        ],
    ];
}

/**
 * Default matrix.
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_default_price_matrix() {
    return MRT_get_builtin_price_matrix();
}

/**
 * Sanitize price matrix from settings form
 *
 * @param mixed $input Raw input
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_sanitize_price_matrix($input) {
    $out = MRT_get_default_price_matrix();
    if (!is_array($input)) {
        return $out;
    }
    foreach (MRT_price_ticket_type_keys() as $t) {
        if (!isset($input[$t]) || !is_array($input[$t])) {
            continue;
        }
        foreach (MRT_price_category_keys() as $c) {
            if (!array_key_exists($c, $input[$t])) {
                continue;
            }
            if (is_array($input[$t][$c])) {
                foreach (MRT_price_zone_keys() as $z) {
                    if (!array_key_exists($z, $input[$t][$c])) {
                        continue;
                    }
                    $out[$t][$c][$z] = MRT_sanitize_price_value($input[$t][$c][$z]);
                }
                continue;
            }

            $legacy = MRT_sanitize_price_value($input[$t][$c]);
            foreach (MRT_price_zone_keys() as $z) {
                $out[$t][$c][$z] = $legacy;
            }
        }
    }
    return $out;
}

/**
 * Sanitize one price value from settings.
 *
 * @param mixed $value Raw value
 * @return int|null
 */
function MRT_sanitize_price_value($value) {
    if ($value === '' || $value === null) {
        return null;
    }
    $n = (int) $value;
    return ($n >= 0) ? $n : null;
}

/**
 * Stored matrix merged with defaults
 *
 * @return array<string, array<string, array<int, int|null>>>
 */
function MRT_get_price_matrix() {
    $stored = get_option('mrt_price_matrix', []);
    if (!is_array($stored)) {
        return MRT_get_default_price_matrix();
    }
    return MRT_sanitize_price_matrix($stored);
}

/**
 * Full zone matrix plus active trip and zone.
 *
 * @param array<string, mixed> $args trip => single|return|day, from_station_id, to_station_id
 * @return array<string, mixed> matrix, active_ticket_type, active_row
 */
function MRT_get_prices_for_context($args = []) {
    $trip = isset($args['trip']) ? sanitize_key((string) $args['trip']) : 'single';
    if (!in_array($trip, MRT_price_ticket_type_keys(), true)) {
        $trip = 'single';
    }
    $full = MRT_get_price_matrix();
    $zones = MRT_price_zones_for_station_pair(
        (int) ($args['from_station_id'] ?? 0),
        (int) ($args['to_station_id'] ?? 0)
    );
    return [
        'matrix' => $full,
        'active_ticket_type' => $trip,
        'active_zone' => $zones,
        'active_row' => MRT_price_matrix_for_zone($full, $zones)[$trip],
    ];
}

/**
 * Select one zone column from the stored zone matrix.
 *
 * @param array<string, array<string, array<int, int|null>>> $matrix Full zone matrix
 * @param int                                                   $zones Number of zones
 * @return array<string, array<string, int|null>>
 */
function MRT_price_matrix_for_zone(array $matrix, int $zones): array {
    $zone_key = max(1, min(4, $zones));
    $out = [];
    foreach (MRT_price_ticket_type_keys() as $t) {
        $out[$t] = [];
        foreach (MRT_price_category_keys() as $c) {
            $out[$t][$c] = $matrix[$t][$c][$zone_key] ?? null;
        }
    }
    return $out;
}

/**
 * Default station zones. Boundary stations are listed in both neighboring zones.
 *
 * @return array<string, int[]>
 */
function MRT_default_station_price_zones_by_title(): array {
    return [
        'Uppsala Östra' => [1],
        'Fyrislund' => [1],
        'Årsta' => [1, 2],
        'Skölsta' => [2],
        'Bärby' => [2],
        'Gunsta' => [2, 3],
        'Marielund' => [3],
        'Lövstahagen' => [3],
        'Selknä' => [3],
        'Löt' => [3],
        'Länna' => [3],
        'Fjällnora' => [3],
        'Almunge' => [3, 4],
        'Moga' => [4],
        'Faringe' => [4],
        'Linnés Hammarby' => [4],
    ];
}

/**
 * Station zones keyed by WordPress station ID for frontend localization.
 *
 * @return array<int, int[]>
 */
function MRT_get_station_price_zones_map(): array {
    $map = [];
    if (!function_exists('MRT_get_all_stations')) {
        return $map;
    }
    $by_title = MRT_default_station_price_zones_by_title();
    foreach (MRT_get_all_stations() as $station_id) {
        $title = get_the_title((int) $station_id);
        if (isset($by_title[$title])) {
            $map[(int) $station_id] = $by_title[$title];
        }
    }
    return $map;
}

/**
 * Cheapest zone count between two station zone sets.
 *
 * Boundary station rule: a station on a zone line counts as either zone, while
 * passing the boundary means entering the next zone.
 *
 * @param int[] $from_zones Possible zones for origin
 * @param int[] $to_zones Possible zones for destination
 * @return int
 */
function MRT_price_zones_between_zone_sets(array $from_zones, array $to_zones): int {
    $best = 4;
    foreach ($from_zones as $from_zone) {
        foreach ($to_zones as $to_zone) {
            $span = abs((int) $to_zone - (int) $from_zone) + 1;
            $best = min($best, $span);
        }
    }
    return max(1, min(4, $best));
}

/**
 * Number of price zones for a station pair.
 *
 * @return int
 */
function MRT_price_zones_for_station_pair(int $from_station_id, int $to_station_id): int {
    $map = MRT_get_station_price_zones_map();
    if (!isset($map[$from_station_id], $map[$to_station_id])) {
        return 4;
    }
    return MRT_price_zones_between_zone_sets($map[$from_station_id], $map[$to_station_id]);
}

/**
 * Human labels for ticket-type rows (admin + public wizard)
 *
 * @return array<string, string>
 */
function MRT_price_ticket_type_labels() {
    return [
        'single' => __('Single ticket', 'museum-railway-timetable'),
        'return' => __('Return ticket', 'museum-railway-timetable'),
        'day' => __('Day pass', 'museum-railway-timetable'),
    ];
}

/**
 * Human labels for passenger columns
 *
 * @return array<string, string>
 */
function MRT_price_category_labels() {
    return [
        'adult' => __('Adult', 'museum-railway-timetable'),
        'child_4_15' => __('Child 7–15', 'museum-railway-timetable'),
        'child_0_3' => __('Child 0–6', 'museum-railway-timetable'),
        'student_senior' => __('Student / senior 65+', 'museum-railway-timetable'),
    ];
}
