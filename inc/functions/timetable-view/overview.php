<?php
/**
 * Timetable overview and date-specific rendering
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render overview timetable view (like the green timetable image)
 * Groups services by route and direction, shows train types
 *
 * @param int $timetable_id Timetable post ID
 * @param string|null $dateYmd Optional date in YYYY-MM-DD format to show date-specific train types
 * @return string HTML output
 */
function MRT_render_timetable_overview($timetable_id, $dateYmd = null) {
    global $wpdb;

    if (!$timetable_id || $timetable_id <= 0) {
        return MRT_render_alert(__('Invalid timetable.', 'museum-railway-timetable'), 'error');
    }

    // Use current date if not provided
    if ($dateYmd === null) {
        $datetime = MRT_get_current_datetime();
        $dateYmd = $datetime['date'];
    }

    // Get all services for this timetable
    $services = get_posts([
        'post_type' => 'mrt_service',
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'mrt_service_timetable_id',
            'value' => $timetable_id,
            'compare' => '=',
        ]],
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);

    if (empty($services)) {
        return MRT_render_alert(__('No trips in this timetable.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    // Group services by route and direction using helper function
    $grouped_services = MRT_group_services_by_route($services, $dateYmd);

    if (empty($grouped_services)) {
        return MRT_render_alert(__('No valid trips in this timetable.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    // Get timetable type label (GRÖN, RÖD, etc.)
    $timetable_type = get_post_meta($timetable_id, 'mrt_timetable_type', true);
    $type_labels = [
        'green' => 'GRÖN TIDTABELL',
        'red' => 'RÖD TIDTABELL',
        'yellow' => 'GUL TIDTABELL',
        'orange' => 'ORANGE TIDTABELL',
    ];
    $timetable_type_label = isset($type_labels[$timetable_type]) ? $type_labels[$timetable_type] : '';

    $overview_label = sprintf(
        /* translators: %s: timetable post title */
        __('Timetable overview: %s', 'museum-railway-timetable'),
        get_the_title($timetable_id)
    );

    // Render HTML
    ob_start();
    ?>
    <div class="mrt-timetable-overview" role="region" aria-label="<?php echo esc_attr($overview_label); ?>">
        <?php if (!empty($timetable_type_label)): ?>
            <div class="mrt-heading mrt-heading--lg mrt-font-bold mrt-text-primary mrt-mb-sm mrt-py-sm mrt-border-b-2"><?php echo esc_html($timetable_type_label); ?></div>
        <?php endif; ?>
        <?php
        $group_count = count($grouped_services);
        $group_index = 0;
        foreach ($grouped_services as $group):
            $group_index++;
            echo MRT_render_timetable_group($group, $dateYmd);
            if ($group_index < $group_count):
        ?>
            <div class="mrt-timetable-separator" aria-hidden="true"></div>
        <?php
            endif;
        endforeach;
        ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render timetable for a specific date
 * Shows all services running on that date, grouped by route and direction
 * Uses the same component as timetable overview for consistency
 *
 * @param string $dateYmd Date in YYYY-MM-DD format
 * @param string $train_type_slug Optional train type filter
 * @return string HTML output
 */
function MRT_render_timetable_for_date($dateYmd, $train_type_slug = '') {
    global $wpdb;

    if (!MRT_validate_date($dateYmd)) {
        return MRT_render_alert(__('Invalid date.', 'museum-railway-timetable'), 'error');
    }

    // Get all services running on this date
    $service_ids = MRT_services_running_on_date($dateYmd, $train_type_slug);

    if (empty($service_ids)) {
        return MRT_render_alert(__('No services running on this date.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    // Get service posts
    $services = get_posts([
        'post_type' => 'mrt_service',
        'post__in' => $service_ids,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);

    if (empty($services)) {
        return MRT_render_alert(__('No services found.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    // Group services by route and direction using helper function
    $grouped_services = MRT_group_services_by_route($services, $dateYmd);

    if (empty($grouped_services)) {
        return MRT_render_alert(__('No valid services found for this date.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    $day_heading_id = wp_unique_id('mrtdayh');

    // Render HTML using the same component as timetable overview
    ob_start();
    ?>
    <div class="mrt-day-timetable mrt-my-1" role="region" aria-labelledby="<?php echo esc_attr($day_heading_id); ?>">
        <h3 class="mrt-heading mrt-heading--xl mrt-mb-1" id="<?php echo esc_attr($day_heading_id); ?>">
            <?php
            printf(
                esc_html__('Timetable for %s', 'museum-railway-timetable'),
                esc_html(date_i18n(get_option('date_format'), strtotime($dateYmd)))
            );
            ?>
        </h3>
        <div class="mrt-timetable-overview">
            <?php
            $group_count = count($grouped_services);
            $group_index = 0;
            foreach ($grouped_services as $group):
                $group_index++;
                echo MRT_render_timetable_group($group, $dateYmd);
                if ($group_index < $group_count):
            ?>
                <div class="mrt-timetable-separator" aria-hidden="true"></div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
