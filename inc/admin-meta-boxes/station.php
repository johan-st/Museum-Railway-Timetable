<?php
/**
 * Station meta box
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Routes that include this station (by station post ID).
 *
 * @return array<int, WP_Post>
 */
if (!function_exists('MRT_get_routes_using_station')) {
    function MRT_get_routes_using_station(int $station_id): array {
        $all_routes = get_posts([
            'post_type' => 'mrt_route',
            'posts_per_page' => -1,
            'fields' => 'all',
        ]);
        $routes_using_station = [];
        foreach ($all_routes as $route) {
            $route_stations = get_post_meta($route->ID, 'mrt_route_stations', true);
            if (is_array($route_stations) && in_array($station_id, $route_stations, true)) {
                $routes_using_station[] = $route;
            }
        }
        return $routes_using_station;
    }
}

/**
 * Info box listing routes that use this station.
 */
function MRT_render_station_meta_box_routes_list(int $station_id): void {
    $routes_using_station = MRT_get_routes_using_station($station_id);
    if ($routes_using_station === []) {
        return;
    }
    $content = '<p class="description">' . esc_html__('This station is used in the following routes:', 'museum-railway-timetable') . '</p>';
    $content .= '<ul class="mrt-list-indent">';
    foreach ($routes_using_station as $route) {
        $content .= '<li><a href="' . esc_url(get_edit_post_link($route->ID)) . '">' . esc_html($route->post_title) . '</a></li>';
    }
    $content .= '</ul>';
    MRT_render_info_box(__('Used in Routes:', 'museum-railway-timetable'), $content, 'mrt-mb-1');
}

/**
 * Station type and bus-marker rows (inside form-table).
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_station_meta_box_rows_type_bus(WP_Post $post): void {
    $station_type = get_post_meta($post->ID, 'mrt_station_type', true);
    $bus_suffix = get_post_meta($post->ID, 'mrt_station_bus_suffix', true);
    ?>
        <tr>
            <th><label for="mrt_station_type"><?php esc_html_e('Station Type', 'museum-railway-timetable'); ?></label></th>
            <td>
                <select name="mrt_station_type" id="mrt_station_type" class="mrt-input mrt-input--meta">
                    <option value=""><?php esc_html_e('— Select —', 'museum-railway-timetable'); ?></option>
                    <option value="station" <?php selected($station_type, 'station'); ?>><?php esc_html_e('Station', 'museum-railway-timetable'); ?></option>
                    <option value="halt" <?php selected($station_type, 'halt'); ?>><?php esc_html_e('Halt', 'museum-railway-timetable'); ?></option>
                    <option value="depot" <?php selected($station_type, 'depot'); ?>><?php esc_html_e('Depot', 'museum-railway-timetable'); ?></option>
                    <option value="museum" <?php selected($station_type, 'museum'); ?>><?php esc_html_e('Museum', 'museum-railway-timetable'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Type of station (station, halt, depot, or museum).', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_station_bus_suffix"><?php esc_html_e('Bus stop marker', 'museum-railway-timetable'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" name="mrt_station_bus_suffix" id="mrt_station_bus_suffix" value="1" <?php checked($bus_suffix, '1'); ?> />
                    <?php esc_html_e('Show asterisk (*) in timetable (e.g. "Från Selknä*" for bus connections)', 'museum-railway-timetable'); ?>
                </label>
            </td>
        </tr>
    <?php
}

/**
 * Lat/lng/display order rows (inside form-table).
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_station_meta_box_rows_geo_order(WP_Post $post): void {
    $lat = get_post_meta($post->ID, 'mrt_lat', true);
    $lng = get_post_meta($post->ID, 'mrt_lng', true);
    $display_order = get_post_meta($post->ID, 'mrt_display_order', true);
    ?>
        <tr>
            <th><label for="mrt_lat"><?php esc_html_e('Latitude', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_lat" id="mrt_lat" value="<?php echo esc_attr($lat); ?>" step="any" class="mrt-input mrt-input--meta" placeholder="<?php esc_attr_e('e.g., 57.486', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Latitude coordinate (e.g., 57.486). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_lng"><?php esc_html_e('Longitude', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_lng" id="mrt_lng" value="<?php echo esc_attr($lng); ?>" step="any" class="mrt-input mrt-input--meta" placeholder="<?php esc_attr_e('e.g., 15.842', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Longitude coordinate (e.g., 15.842). Optional.', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="mrt_display_order"><?php esc_html_e('Display Order', 'museum-railway-timetable'); ?></label></th>
            <td>
                <input type="number" name="mrt_display_order" id="mrt_display_order" value="<?php echo esc_attr($display_order ?: 0); ?>" min="0" class="mrt-input mrt-input--meta" placeholder="<?php esc_attr_e('e.g., 1, 2, 3', 'museum-railway-timetable'); ?>" />
                <p class="description"><?php esc_html_e('Order for sorting in lists (lower numbers appear first). Example: 1, 2, 3...', 'museum-railway-timetable'); ?></p>
            </td>
        </tr>
    <?php
}

/**
 * Station type, coordinates, display order fields.
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_station_meta_box_details_form(WP_Post $post): void {
    ?>
    <div class="mrt-box mrt-mt-1">
    <h3 class="mrt-heading mrt-mt-0"><?php esc_html_e('Station Details', 'museum-railway-timetable'); ?></h3>
    <table class="form-table">
        <?php MRT_render_station_meta_box_rows_type_bus($post); ?>
        <?php MRT_render_station_meta_box_rows_geo_order($post); ?>
    </table>
    </div>
    <?php
}

/**
 * Render station meta box
 *
 * @param WP_Post $post Current post object
 */
function MRT_render_station_meta_box($post): void {
    wp_nonce_field('mrt_save_station_meta', 'mrt_station_meta_nonce');

    ?>
    <?php MRT_render_info_box(
        __('💡 What is a Station?', 'museum-railway-timetable'),
        '<p>' . esc_html__('A station is a physical location where trains can stop. Stations are used in Routes and Stop Times to define where trains travel and when they arrive/depart.', 'museum-railway-timetable') . '</p>'
    ); ?>
    <?php
    MRT_render_station_meta_box_routes_list((int) $post->ID);
    MRT_render_station_meta_box_details_form($post);
}

/**
 * Persist station meta box fields.
 */
function MRT_save_station_meta_box(int $post_id): void {
    if (!MRT_verify_meta_box_save($post_id, 'mrt_station_meta_nonce', 'mrt_save_station_meta')) {
        return;
    }
    if (isset($_POST['mrt_station_type'])) {
        $type = sanitize_text_field(wp_unslash($_POST['mrt_station_type']));
        $allowed_types = ['station', 'halt', 'depot', 'museum', ''];
        if (in_array($type, $allowed_types, true)) {
            update_post_meta($post_id, 'mrt_station_type', $type);
        }
    }
    if (isset($_POST['mrt_station_bus_suffix'])) {
        update_post_meta($post_id, 'mrt_station_bus_suffix', '1');
    } else {
        update_post_meta($post_id, 'mrt_station_bus_suffix', '0');
    }
    if (isset($_POST['mrt_lat'])) {
        $lat = sanitize_text_field(wp_unslash($_POST['mrt_lat']));
        if ($lat === '') {
            delete_post_meta($post_id, 'mrt_lat');
        } else {
            update_post_meta($post_id, 'mrt_lat', floatval($lat));
        }
    }
    if (isset($_POST['mrt_lng'])) {
        $lng = sanitize_text_field(wp_unslash($_POST['mrt_lng']));
        if ($lng === '') {
            delete_post_meta($post_id, 'mrt_lng');
        } else {
            update_post_meta($post_id, 'mrt_lng', floatval($lng));
        }
    }
    if (isset($_POST['mrt_display_order'])) {
        $order = (int) wp_unslash($_POST['mrt_display_order']);
        update_post_meta($post_id, 'mrt_display_order', $order);
    }
}

add_action('save_post_mrt_station', 'MRT_save_station_meta_box');
