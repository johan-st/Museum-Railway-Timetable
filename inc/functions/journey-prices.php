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
 * Default matrix (all null = unknown / not configured)
 *
 * @return array<string, array<string, null>>
 */
function MRT_get_default_price_matrix() {
    $row = array_fill_keys(MRT_price_category_keys(), null);
    $out = [];
    foreach (MRT_price_ticket_type_keys() as $t) {
        $out[$t] = $row;
    }
    return $out;
}

/**
 * Sanitize price matrix from settings form
 *
 * @param mixed $input Raw input
 * @return array<string, array<string, int|null>>
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
            $v = $input[$t][$c];
            if ($v === '' || $v === null) {
                $out[$t][$c] = null;
            } else {
                $n = (int) $v;
                $out[$t][$c] = ($n >= 0) ? $n : null;
            }
        }
    }
    return $out;
}

/**
 * Stored matrix merged with defaults
 *
 * @return array<string, array<string, int|null>>
 */
function MRT_get_price_matrix() {
    $stored = get_option('mrt_price_matrix', []);
    if (!is_array($stored)) {
        return MRT_get_default_price_matrix();
    }
    return MRT_sanitize_price_matrix($stored);
}

/**
 * Full matrix plus active row for a trip type (mockup table)
 *
 * @param array<string, mixed> $args trip => single|return|day (default single)
 * @return array<string, mixed> matrix, active_ticket_type, active_row
 */
function MRT_get_prices_for_context($args = []) {
    $trip = isset($args['trip']) ? sanitize_key((string) $args['trip']) : 'single';
    if (!in_array($trip, MRT_price_ticket_type_keys(), true)) {
        $trip = 'single';
    }
    $full = MRT_get_price_matrix();
    return [
        'matrix' => $full,
        'active_ticket_type' => $trip,
        'active_row' => $full[$trip],
    ];
}
