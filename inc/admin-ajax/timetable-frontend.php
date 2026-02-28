<?php
/**
 * AJAX handler for timetable by date (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get timetable for a specific date via AJAX (frontend)
 */
function MRT_ajax_get_timetable_for_date() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'mrt_frontend')) {
        wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable')]);
        return;
    }
    $date = sanitize_text_field($_POST['date'] ?? '');
    $train_type = sanitize_text_field($_POST['train_type'] ?? '');
    
    if (empty($date) || !MRT_validate_date($date)) {
        wp_send_json_error(['message' => __('Please select a valid date.', 'museum-railway-timetable')]);
        return;
    }
    
    $html = MRT_render_timetable_for_date($date, $train_type);
    
    wp_send_json_success(['html' => $html]);
}
