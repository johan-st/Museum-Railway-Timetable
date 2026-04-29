<?php
/**
 * Timetable grid rendering (body, group)
 * Requires: grid-helpers.php, grid-rows.php
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

$grid_dir = __DIR__ . '/';
require_once $grid_dir . 'grid-helpers.php';
require_once $grid_dir . 'grid-rows.php';

/**
 * Render timetable grid body using CSS Grid
 */
function MRT_render_timetable_table_body($station_posts, $services_list, $service_classes, $service_info, $all_connections) {
    $regular_stations = !empty($station_posts) ? array_slice($station_posts, 1, -1) : [];

    $html = '<div class="mrt-grid-body">';
    if (!empty($station_posts)) {
        $html .= MRT_render_grid_from_row($station_posts[0], $services_list, $service_classes, $service_info);
    }
    $html .= MRT_render_grid_regular_station_rows($regular_stations, $services_list, $service_classes, $service_info);
    if (!empty($station_posts)) {
        $html .= MRT_render_grid_to_row(end($station_posts), $services_list, $service_classes, $service_info);
    }
    if (!empty($all_connections)) {
        $html .= MRT_render_grid_transfer_rows($services_list, $service_classes, $all_connections, $service_info);
    }
    $html .= '</div>';

    return $html;
}

/**
 * Sort service columns by time at the first station, matching printed timetables.
 *
 * @param array<int, array<string, mixed>> $services_list
 * @param int $first_station_id
 * @return array<int, array<string, mixed>>
 */
function MRT_sort_timetable_services_by_first_station_time(array $services_list, int $first_station_id): array {
    usort($services_list, function($a, $b) use ($first_station_id) {
        $a_stop = $a['stop_times'][$first_station_id] ?? [];
        $b_stop = $b['stop_times'][$first_station_id] ?? [];
        $a_time = MRT_stop_effective_departure(is_array($a_stop) ? $a_stop : []);
        $b_time = MRT_stop_effective_departure(is_array($b_stop) ? $b_stop : []);
        return strcmp($a_time, $b_time);
    });
    return $services_list;
}

/**
 * Render a single timetable group (route)
 */
function MRT_render_timetable_group($group, $dateYmd) {
    $route = $group['route'];
    $direction = $group['direction'];
    $stations = $group['stations'];
    $services_list = $group['services'];

    $station_posts = [];
    if (!empty($stations)) {
        $station_posts = get_posts([
            'post_type' => 'mrt_station',
            'post__in' => $stations,
            'posts_per_page' => -1,
            'orderby' => 'post__in',
            'fields' => 'all',
        ]);
    }

    $route_label = MRT_get_route_label($route, $direction, $services_list, $station_posts);
    $from_station = !empty($station_posts) ? $station_posts[0] : null;
    $to_station = !empty($station_posts) ? end($station_posts) : null;
    if ($from_station) {
        $services_list = MRT_sort_timetable_services_by_first_station_time($services_list, (int) $from_station->ID);
    }

    $prepared = MRT_prepare_service_info($services_list, $dateYmd);
    $service_classes = $prepared['service_classes'];
    $service_info = $prepared['service_info'];
    $all_connections = $prepared['all_connections'];

    $service_count = count($services_list);
    $group_heading_id = wp_unique_id('mrtgrh');
    ob_start();
    ?>
    <div class="mrt-timetable-group">
        <div class="mrt-route-header">
            <h3 class="mrt-route-header-main" id="<?php echo esc_attr($group_heading_id); ?>"><?php echo esc_html($route_label); ?></h3>
            <?php if ($from_station && $to_station): ?>
                <div class="mrt-route-header-details">
                    <span class="mrt-route-from"><?php printf(esc_html__('Från %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($from_station))); ?></span>
                    <span class="mrt-route-separator" aria-hidden="true">→</span>
                    <span class="mrt-route-to"><?php printf(esc_html__('Till %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($to_station))); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="mrt-overview-grid" style="--service-count: <?php echo (int) $service_count; ?>;" role="group" aria-labelledby="<?php echo esc_attr($group_heading_id); ?>">
            <?php echo MRT_render_timetable_table_header($services_list, $service_classes, $service_info); ?>
            <?php echo MRT_render_timetable_table_body($station_posts, $services_list, $service_classes, $service_info, $all_connections); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
