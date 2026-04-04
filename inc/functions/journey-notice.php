<?php
/**
 * Per-service public notice (post meta mrt_service_notice)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Notice text for a service (future: date-specific JSON in same meta)
 *
 * @param int         $service_id Service post ID
 * @param string|null $dateYmd Date YYYY-MM-DD (reserved for future use)
 * @return string Plain text, may be empty
 */
function MRT_get_service_notice($service_id, $dateYmd = null) {
    unset($dateYmd);
    if ($service_id <= 0) {
        return '';
    }
    $raw = get_post_meta($service_id, 'mrt_service_notice', true);
    if (!is_string($raw) || $raw === '') {
        return '';
    }
    return trim($raw);
}
