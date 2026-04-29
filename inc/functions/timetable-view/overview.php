<?php
/**
 * Timetable overview and date-specific rendering
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * HTML for grouped route/direction blocks with separators (shared by overview and day view).
 *
 * @param array<string, mixed> $grouped_services From MRT_group_services_by_route
 */
function MRT_render_timetable_groups_inner_html(array $grouped_services, string $dateYmd): string {
    $group_count = count($grouped_services);
    $group_index = 0;
    ob_start();
    foreach ($grouped_services as $group) {
        $group_index++;
        echo MRT_render_timetable_group($group, $dateYmd);
        if ($group_index < $group_count) {
            echo '<div class="mrt-timetable-separator" aria-hidden="true"></div>';
        }
    }
    return ob_get_clean();
}

/**
 * Label banner for printed-style timetable type (GRÖN / RÖD / …).
 */
/**
 * @return array<int, WP_Post>
 */
function MRT_get_services_for_timetable(int $timetable_id): array {
    return get_posts([
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
}

/**
 * @param array<int> $service_ids
 * @return array<int, WP_Post>
 */
function MRT_get_services_by_post_ids(array $service_ids): array {
    if ($service_ids === []) {
        return [];
    }
    return get_posts([
        'post_type' => 'mrt_service',
        'post__in' => $service_ids,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'all',
    ]);
}

function MRT_timetable_type_banner_html(string $timetable_type): string {
    $type_labels = [
        'green' => ['title' => 'Grön tidtabell', 'meta' => 'Lördagar'],
        'red' => ['title' => 'Röd tidtabell', 'meta' => ''],
        'yellow' => ['title' => 'Gul tidtabell', 'meta' => ''],
        'orange' => ['title' => 'Orange tidtabell', 'meta' => ''],
    ];
    $timetable_type_label = $type_labels[$timetable_type] ?? null;
    if (!$timetable_type_label) {
        return '';
    }
    return sprintf(
        '<div class="mrt-heading mrt-heading--lg mrt-font-bold mrt-text-primary mrt-mb-sm mrt-py-sm mrt-border-b-2 mrt-timetable-type-banner mrt-timetable-type-banner--%1$s"><span class="mrt-timetable-type-banner__title">%2$s</span>%3$s</div>',
        esc_attr($timetable_type),
        esc_html($timetable_type_label['title']),
        $timetable_type_label['meta'] !== ''
            ? '<span class="mrt-timetable-type-banner__meta">' . esc_html($timetable_type_label['meta']) . '</span>'
            : ''
    );
}

/**
 * Render overview timetable view (like the green timetable image)
 * Groups services by route and direction, shows train types
 *
 * @param int $timetable_id Timetable post ID
 * @param string|null $dateYmd Optional date in YYYY-MM-DD format to show date-specific train types
 * @return string HTML output
 */
function MRT_render_timetable_overview($timetable_id, $dateYmd = null) {
    if (!$timetable_id || $timetable_id <= 0) {
        return MRT_render_alert(__('Invalid timetable.', 'museum-railway-timetable'), 'error');
    }

    if ($dateYmd === null) {
        $datetime = MRT_get_current_datetime();
        $dateYmd = $datetime['date'];
    }

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

    $grouped_services = MRT_group_services_by_route($services, $dateYmd);

    if (empty($grouped_services)) {
        return MRT_render_alert(__('No valid trips in this timetable.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    $inner = MRT_render_timetable_groups_inner_html($grouped_services, $dateYmd);
    $tt = (string) get_post_meta($timetable_id, 'mrt_timetable_type', true);

    return sprintf(
        '<div class="mrt-timetable-overview" role="region" aria-label="%s">%s%s</div>',
        esc_attr(sprintf(
            /* translators: %s: timetable post title */
            __('Timetable overview: %s', 'museum-railway-timetable'),
            get_the_title($timetable_id)
        )),
        MRT_timetable_type_banner_html($tt),
        $inner
    );
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
    if (!MRT_validate_date($dateYmd)) {
        return MRT_render_alert(__('Invalid date.', 'museum-railway-timetable'), 'error');
    }

    $service_ids = MRT_services_running_on_date($dateYmd, $train_type_slug);

    if (empty($service_ids)) {
        return MRT_render_alert(__('No services running on this date.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    $services = MRT_get_services_by_post_ids($service_ids);

    if ($services === []) {
        return MRT_render_alert(__('No services found.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    $grouped_services = MRT_group_services_by_route($services, $dateYmd);

    if (empty($grouped_services)) {
        return MRT_render_alert(__('No valid services found for this date.', 'museum-railway-timetable'), 'info', 'mrt-empty');
    }

    $day_heading_id = wp_unique_id('mrtdayh');
    $inner = MRT_render_timetable_groups_inner_html($grouped_services, $dateYmd);

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
            <?php echo $inner; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
