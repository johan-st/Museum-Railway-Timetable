<?php
/**
 * AJAX handler for journey search (frontend)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Search for journey connections via AJAX (frontend)
 */
function MRT_ajax_search_journey() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'mrt_frontend')) {
        wp_send_json_error(['message' => __('Security check failed. Please refresh the page.', 'museum-railway-timetable')]);
        return;
    }
    $from_station_id = intval($_POST['from_station'] ?? 0);
    $to_station_id = intval($_POST['to_station'] ?? 0);
    $date = sanitize_text_field($_POST['date'] ?? '');
    
    if ($from_station_id <= 0 || $to_station_id <= 0) {
        wp_send_json_error(['message' => __('Please select both departure and arrival stations.', 'museum-railway-timetable')]);
        return;
    }
    
    if ($from_station_id === $to_station_id) {
        wp_send_json_error(['message' => __('Please select different stations for departure and arrival.', 'museum-railway-timetable')]);
        return;
    }
    
    if (empty($date) || !MRT_validate_date($date)) {
        wp_send_json_error(['message' => __('Please select a valid date.', 'museum-railway-timetable')]);
        return;
    }
    
    $services_on_date = MRT_services_running_on_date($date);
    if (empty($services_on_date)) {
        $html = MRT_render_alert(__('No services are running on the selected date.', 'museum-railway-timetable'), 'error');
        wp_send_json_success(['html' => $html]);
        return;
    }
    
    $connections = MRT_find_connections($from_station_id, $to_station_id, $date);
    $from_station_name = get_the_title($from_station_id);
    $to_station_name = get_the_title($to_station_id);
    
    ob_start();
    ?>
    <h3 class="mrt-heading mrt-heading--xl mrt-mb-1">
        <?php 
        printf(
            esc_html__('Connections from %s to %s on %s', 'museum-railway-timetable'),
            esc_html($from_station_name),
            esc_html($to_station_name),
            esc_html(date_i18n(get_option('date_format'), strtotime($date)))
        );
        ?>
    </h3>
    
    <?php if (empty($connections)): ?>
        <div class="mrt-alert mrt-alert-info mrt-empty">
            <p><strong><?php esc_html_e('No connections found.', 'museum-railway-timetable'); ?></strong></p>
            <p><?php esc_html_e('There are no direct connections between these stations on the selected date. Please try a different date or different stations.', 'museum-railway-timetable'); ?></p>
        </div>
    <?php else: ?>
        <div class="mrt-journey-table-container mrt-overflow-x-auto">
            <table class="mrt-table mrt-journey-table mrt-mt-sm">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Service', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Train Type', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Departure', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Arrival', 'museum-railway-timetable'); ?></th>
                        <th><?php esc_html_e('Destination', 'museum-railway-timetable'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($connections as $conn): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($conn['service_name']); ?></strong>
                                <?php if (!empty($conn['route_name'])): ?>
                                    <br><small class="mrt-text-tertiary mrt-font-italic"><?php echo esc_html($conn['route_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($conn['train_type']); ?></td>
                            <td>
                                <strong><?php echo esc_html($conn['from_departure'] ?: ($conn['from_arrival'] ?: '—')); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo esc_html($conn['to_arrival'] ?: ($conn['to_departure'] ?: '—')); ?></strong>
                            </td>
                            <td><?php echo esc_html(!empty($conn['destination']) ? $conn['destination'] : (!empty($conn['direction']) ? $conn['direction'] : '—')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php
    $html = ob_get_clean();
    
    wp_send_json_success(['html' => $html]);
}
