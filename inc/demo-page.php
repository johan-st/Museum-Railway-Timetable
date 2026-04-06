<?php
/**
 * Create a WordPress page that showcases all public shortcodes (dev / QA)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/** Option key for stored demo page post ID */
define('MRT_OPTION_COMPONENTS_DEMO_PAGE_ID', 'mrt_components_demo_page_id');

/**
 * Admin submenu slug for the component demo screen (must match add_submenu_page)
 *
 * @return string
 */
function MRT_components_demo_menu_slug() {
    return 'mrt_components_demo';
}

/**
 * Redirect mistaken /wp-admin/{slug} to admin.php?page={slug}
 *
 * WordPress menu-header.php uses a raw slug as href when get_plugin_page_hook() is empty,
 * which resolves to a non-existent path and often shows the front-end 404 template.
 *
 * @return void
 */
function MRT_redirect_components_demo_admin_canonical_url() {
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }
    $slug = MRT_components_demo_menu_slug();
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($uri === '' || str_contains($uri, 'admin.php')) {
        return;
    }
    $path = rtrim((string) wp_parse_url($uri, PHP_URL_PATH), '/');
    $needle = '/wp-admin/' . $slug;
    if (!str_ends_with($path, $needle)) {
        return;
    }
    wp_safe_redirect(admin_url('admin.php?page=' . rawurlencode($slug)));
    exit;
}

add_action('admin_init', 'MRT_redirect_components_demo_admin_canonical_url', 0);

/**
 * Timetable post title used by Lennakatten import (overview shortcode)
 *
 * @return string
 */
function MRT_demo_lennakatten_timetable_title() {
    return 'GRÖN TIDTABELL 2026';
}

/**
 * Legend box: which blocks follow journey mockups vs classic shortcodes
 *
 * @return string HTML
 */
function MRT_get_components_demo_mockup_legend_html() {
    $items = [
        __('Journey wizard — mockup flow V1–V5 (route → date → trips → summary, see PUBLIC_JOURNEY_IMPLEMENTATION_PLAN).', 'museum-railway-timetable'),
        __('Month calendar & timetable overview — timetable/mockup views (not the full “book a trip” flow).', 'museum-railway-timetable'),
        __('Journey planner — simple one-screen search; not the multi-step mockup wizard.', 'museum-railway-timetable'),
    ];
    $out = '<div class="mrt-alert mrt-alert-info mrt-mb-lg"><p><strong>' .
        esc_html__('Mockup alignment', 'museum-railway-timetable') .
        '</strong></p><ul class="mrt-demo-mockup-list">';
    foreach ($items as $text) {
        $out .= '<li>' . esc_html($text) . '</li>';
    }
    $out .= '</ul></div>';
    return $out;
}

/**
 * Short line under a section heading describing mockup relation
 *
 * @param string $label Short label for screen readers / emphasis
 * @return string HTML paragraph
 */
function MRT_demo_mockup_caption($label) {
    return '<p class="mrt-text-secondary mrt-mb-sm mrt-demo-mockup-caption"><em>' . esc_html($label) . '</em></p>';
}

/**
 * How to get connections on the demo (Lennakatten import + example station pair)
 *
 * @return string HTML
 */
function MRT_get_components_demo_journey_test_data_html() {
    $dates = function_exists('MRT_import_get_timetable_dates') ? MRT_import_get_timetable_dates() : [];
    $example = !empty($dates[0]) ? $dates[0] : '2026-05-30';
    $date_list = !empty($dates) ? implode(', ', array_slice($dates, 0, 5)) : $example;
    if (count($dates) > 5) {
        $date_list .= ' …';
    }
    $out = '<div class="mrt-alert mrt-alert-info mrt-mb-lg"><p><strong>' .
        esc_html__('Trying the journey wizard', 'museum-railway-timetable') .
        '</strong></p><ul class="mrt-demo-mockup-list">';
    $out .= '<li>' . sprintf(
        /* translators: %s: timetable title e.g. GRÖN TIDTABELL 2026 */
        esc_html__('Run %s from the Railway Timetable admin menu so stations, routes, and services exist.', 'museum-railway-timetable'),
        '<code>' . esc_html(MRT_demo_lennakatten_timetable_title()) . '</code>'
    ) . '</li>';
    $out .= '<li>' . esc_html__(
        'In the wizard, choose stations on the same line (e.g. From: Uppsala Östra, To: Faringe). If either station is missing, the import did not finish.',
        'museum-railway-timetable'
    ) . '</li>';
    $out .= '<li>' . sprintf(
        /* translators: %1$s: example YYYY-MM-DD, %2$s: list of sample dates */
        esc_html__(
            'Pick a traffic day: the GRÖN import includes dates such as %2$s. Example first day: %1$s. Use the wizard calendar’s previous/next month buttons until that month appears, then choose a green (available) day for your pair.',
            'museum-railway-timetable'
        ),
        '<code>' . esc_html($example) . '</code>',
        esc_html($date_list)
    ) . '</li>';
    $out .= '<li>' . esc_html__(
        'If you see “No connections on this date”, the pair or date has no matching service — try the example stations and a listed traffic day.',
        'museum-railway-timetable'
    ) . '</li>';
    $out .= '<li>' . esc_html__(
        'The month calendar in section 1 has Previous month / Next month links (?mrt_month= in the URL).',
        'museum-railway-timetable'
    ) . '</li>';
    $out .= '</ul></div>';

    return $out;
}

/**
 * Page content: intro + all shortcodes
 *
 * @return string
 */
function MRT_get_components_demo_page_content() {
    $tt = MRT_demo_lennakatten_timetable_title();
    $intro = sprintf(
        /* translators: %s: timetable title after Lennakatten import */
        __(
            'This page lists all public timetable shortcodes. For realistic data, run Import Lennakatten (Railway Timetable menu). The timetable overview expects a timetable titled "%s".',
            'museum-railway-timetable'
        ),
        $tt
    );
    $lines = [
        '<p>' . esc_html($intro) . '</p>',
        MRT_get_components_demo_mockup_legend_html(),
        MRT_get_components_demo_journey_test_data_html(),
        '<h2>' . esc_html__('1. Month calendar', 'museum-railway-timetable') . '</h2>',
        MRT_demo_mockup_caption(__('Mockup: calendar / traffic days (timetable context, not the journey booking flow).', 'museum-railway-timetable')),
        '[museum_timetable_month show_counts="1" legend="1"]',
        '<h2>' . esc_html__('2. Timetable overview', 'museum-railway-timetable') . '</h2>',
        MRT_demo_mockup_caption(__('Mockup: printed-style overview (routes, directions, times).', 'museum-railway-timetable')),
        sprintf('[museum_timetable_overview timetable="%s"]', esc_attr($tt)),
        '<h2>' . esc_html__('3. Journey planner (simple search)', 'museum-railway-timetable') . '</h2>',
        MRT_demo_mockup_caption(__('Not mockup-based: single form + results table (legacy shortcut).', 'museum-railway-timetable')),
        '[museum_journey_planner]',
        '<h2>' . esc_html__('4. Journey wizard (multi-step)', 'museum-railway-timetable') . '</h2>',
        MRT_demo_mockup_caption(__('Mockup-based: full journey flow (V1–V5) with calendar, legs, optional return, prices in summary).', 'museum-railway-timetable')),
        '[museum_journey_wizard hero_subtitle="Step 1 — route and trip type (mockup: sok-din-resa)."]',
    ];
    return implode("\n\n", $lines);
}

/**
 * Insert or refresh the demo page
 *
 * @return int|WP_Error Post ID or error
 */
function MRT_ensure_components_demo_page() {
    if (!current_user_can('manage_options')) {
        return new WP_Error('mrt_cap', __('Permission denied.', 'museum-railway-timetable'));
    }
    $title = __('Museum Railway Timetable – component demo', 'museum-railway-timetable');
    $content = MRT_get_components_demo_page_content();
    $post_id = (int) get_option(MRT_OPTION_COMPONENTS_DEMO_PAGE_ID, 0);
    $postarr = [
        'post_type' => 'page',
        'post_status' => 'draft',
        'post_title' => $title,
        'post_content' => $content,
    ];
    if ($post_id > 0 && get_post($post_id) && get_post_type($post_id) === 'page') {
        $postarr['ID'] = $post_id;
        $result = wp_update_post(wp_slash($postarr), true);
    } else {
        $result = wp_insert_post(wp_slash($postarr), true);
        if (!is_wp_error($result)) {
            update_option(MRT_OPTION_COMPONENTS_DEMO_PAGE_ID, (int) $result);
        }
    }
    return $result;
}

/**
 * Edit / preview links for an existing demo page
 *
 * @param WP_Post|null $post Page post or null
 * @return void
 */
function MRT_render_demo_page_admin_links($post) {
    if (!$post || $post->post_type !== 'page') {
        return;
    }
    ?>
    <hr>
    <p>
        <strong><?php esc_html_e('Demo page', 'museum-railway-timetable'); ?>:</strong>
        <?php echo esc_html(get_the_title($post)); ?>
        (<?php echo esc_html($post->post_status); ?>)
    </p>
    <p>
        <a class="button" href="<?php echo esc_url(get_edit_post_link($post->ID, 'raw')); ?>">
            <?php esc_html_e('Edit page', 'museum-railway-timetable'); ?>
        </a>
        <?php if ($post->post_status === 'publish') : ?>
            <a class="button" href="<?php echo esc_url(get_permalink($post)); ?>">
                <?php esc_html_e('View page', 'museum-railway-timetable'); ?>
            </a>
        <?php else : ?>
            <a class="button" href="<?php echo esc_url(get_preview_post_link($post)); ?>">
                <?php esc_html_e('Preview draft', 'museum-railway-timetable'); ?>
            </a>
        <?php endif; ?>
    </p>
    <?php
}

/**
 * Admin screen: create / update demo page
 *
 * @return void
 */
function MRT_render_components_demo_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'museum-railway-timetable'));
    }
    $notice = '';
    $notice_type = '';
    if (isset($_POST['mrt_create_demo_page']) && check_admin_referer('mrt_components_demo', 'mrt_components_demo_nonce')) {
        $res = MRT_ensure_components_demo_page();
        if (is_wp_error($res)) {
            $notice_type = 'error';
            $notice = $res->get_error_message();
        } else {
            $notice_type = 'success';
            $notice = sprintf(
                /* translators: %d: WordPress page ID */
                __('Demo page saved successfully (page ID %d). Use the links below to edit or preview.', 'museum-railway-timetable'),
                (int) $res
            );
        }
    }
    $page_id = (int) get_option(MRT_OPTION_COMPONENTS_DEMO_PAGE_ID, 0);
    $post = ($page_id > 0) ? get_post($page_id) : null;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Component demo page', 'museum-railway-timetable'); ?></h1>
        <p><?php esc_html_e('Creates or updates a draft page that includes every public shortcode, so you can preview components in one place.', 'museum-railway-timetable'); ?></p>
        <?php if ($notice !== '') : ?>
            <?php $cls = ($notice_type === 'success') ? 'notice-success' : 'notice-error'; ?>
            <div class="notice <?php echo esc_attr($cls); ?> is-dismissible"><p><?php echo esc_html($notice); ?></p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('mrt_components_demo', 'mrt_components_demo_nonce'); ?>
            <p>
                <button type="submit" name="mrt_create_demo_page" class="button button-primary" value="1">
                    <?php esc_html_e('Create or update demo page', 'museum-railway-timetable'); ?>
                </button>
            </p>
        </form>
        <?php MRT_render_demo_page_admin_links($post); ?>
    </div>
    <?php
}
