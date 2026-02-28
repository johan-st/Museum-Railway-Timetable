<?php

declare(strict_types=1);

/**
 * Asset enqueuing for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if admin assets should be loaded for current page
 *
 * @param string $hook Current admin page hook
 */
function MRT_should_load_admin_assets(string $hook): bool {
    $allowed_post_types = MRT_POST_TYPES;

    $is_plugin_page = (strpos($hook, 'mrt_') !== false);
    $is_edit_page = in_array($hook, ['post.php', 'post-new.php']);
    $is_list_page = ($hook === 'edit.php');

    if (!$is_plugin_page && !$is_edit_page && !$is_list_page) {
        return false;
    }
    if ($is_edit_page) {
        $post_type = get_post_type();
        if (!in_array($post_type, $allowed_post_types, true)) {
            return false;
        }
    }
    if ($is_list_page) {
        $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : 'post';
        if (!in_array($post_type, $allowed_post_types, true)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if timetable overview CSS should load (admin)
 * Load on: timetable edit, station overview view
 *
 * @param string $hook Current admin page hook
 * @return bool
 */
function MRT_should_load_admin_timetable_overview(string $hook): bool {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen) {
        return false;
    }
    // Timetable edit (post.php / post-new.php)
    if (in_array($hook, ['post.php', 'post-new.php'], true) && $screen->post_type === MRT_POST_TYPE_TIMETABLE) {
        return true;
    }
    // Station overview (edit.php?post_type=mrt_station&mrt_view=overview)
    if ($screen->id === 'edit-' . MRT_POST_TYPE_STATION) {
        $mrt_view = isset($_GET['mrt_view']) ? sanitize_text_field(wp_unslash($_GET['mrt_view'])) : '';
        if ($mrt_view === 'overview') {
            return true;
        }
    }
    return false;
}

/**
 * Check if dashboard CSS should load (admin)
 *
 * @param string $hook Current admin page hook
 * @return bool
 */
function MRT_should_load_admin_dashboard(string $hook): bool {
    return $hook === 'toplevel_page_mrt_settings';
}

/**
 * Enqueue admin CSS files
 *
 * @param string $hook Current admin page hook (from admin_enqueue_scripts)
 */
function MRT_enqueue_admin_css(string $hook): void {
    $base = MRT_URL . 'assets/';
    wp_enqueue_style('mrt-admin-base', $base . 'admin-base.css', [], MRT_VERSION);
    wp_enqueue_style('mrt-admin-components', $base . 'admin-components.css', ['mrt-admin-base'], MRT_VERSION);
    wp_enqueue_style('mrt-admin-timetable', $base . 'admin-timetable.css', ['mrt-admin-components'], MRT_VERSION);

    $meta_deps = ['mrt-admin-timetable'];
    if (MRT_should_load_admin_timetable_overview($hook)) {
        wp_enqueue_style('mrt-admin-timetable-overview', $base . 'admin-timetable-overview.css', ['mrt-admin-timetable'], MRT_VERSION);
        $meta_deps[] = 'mrt-admin-timetable-overview';
    }
    wp_enqueue_style('mrt-admin-meta-boxes', $base . 'admin-meta-boxes.css', $meta_deps, MRT_VERSION);

    $ui_deps = ['mrt-admin-meta-boxes'];
    if (MRT_should_load_admin_dashboard($hook)) {
        wp_enqueue_style('mrt-admin-dashboard', $base . 'admin-dashboard.css', ['mrt-admin-meta-boxes'], MRT_VERSION);
        $ui_deps[] = 'mrt-admin-dashboard';
    }
    wp_enqueue_style('mrt-admin-ui', $base . 'admin-ui.css', $ui_deps, MRT_VERSION);
    wp_enqueue_style('mrt-admin-responsive', $base . 'admin-responsive.css', ['mrt-admin-ui'], MRT_VERSION);
}

/**
 * Enqueue admin JavaScript files
 */
function MRT_enqueue_admin_js() {
    wp_enqueue_script('mrt-admin-utils', MRT_URL . 'assets/admin-utils.js', ['jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-route-ui', MRT_URL . 'assets/admin-route-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-stoptimes-ui', MRT_URL . 'assets/admin-stoptimes-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-timetable-services', MRT_URL . 'assets/admin-timetable-services-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-service-edit', MRT_URL . 'assets/admin-service-edit.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin', MRT_URL . 'assets/admin.js', [
        'mrt-admin-utils', 'mrt-admin-route-ui', 'mrt-admin-stoptimes-ui', 'mrt-admin-timetable-services', 'mrt-admin-service-edit', 'jquery'
    ], MRT_VERSION, true);
}

/**
 * Localize admin script with translations and AJAX URL
 */
function MRT_localize_admin_script() {
    wp_localize_script('mrt-admin', 'mrtAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'invalidTimeFormat' => __('Invalid format. Use HH:MM (e.g., 09:15)', MRT_TEXT_DOMAIN),
        'fixTimeFormats' => __('Please fix invalid time formats before saving. Use HH:MM format (e.g., 09:15).', MRT_TEXT_DOMAIN),
        'saveServiceToUpdateStations' => __('Please save the service to update available stations from the selected route.', MRT_TEXT_DOMAIN),
        'pleaseSelectStation' => __('Please select a station.', MRT_TEXT_DOMAIN),
        'stationAlreadyOnRoute' => __('This station is already on the route.', MRT_TEXT_DOMAIN),
        'pleaseFillStationAndSequence' => __('Please fill in Station and Sequence.', MRT_TEXT_DOMAIN),
        'errorSavingStopTime' => __('Error saving stop time.', MRT_TEXT_DOMAIN),
        'errorAddingStopTime' => __('Error adding stop time.', MRT_TEXT_DOMAIN),
        'confirmDeleteStopTime' => __('Are you sure you want to delete this stop time?', MRT_TEXT_DOMAIN),
        'errorDeletingStopTime' => __('Error deleting stop time.', MRT_TEXT_DOMAIN),
        'pleaseSelectRoute' => __('Please select a route.', MRT_TEXT_DOMAIN),
        'securityTokenMissing' => __('Security token missing. Please refresh the page.', MRT_TEXT_DOMAIN),
        'confirmRemoveTrip' => __('Are you sure you want to remove this trip from the timetable?', MRT_TEXT_DOMAIN),
        'errorRemovingTrip' => __('Error removing trip.', MRT_TEXT_DOMAIN),
        'networkError' => __('Network error. Please try again.', MRT_TEXT_DOMAIN),
        'moveUp' => __('Move up', MRT_TEXT_DOMAIN),
        'moveDown' => __('Move down', MRT_TEXT_DOMAIN),
        'remove' => __('Remove', MRT_TEXT_DOMAIN),
        'loadingStations' => __('Loading stations...', MRT_TEXT_DOMAIN),
        'noRouteSelected' => __('No route selected. Select a route to configure stop times.', MRT_TEXT_DOMAIN),
        'noStationsOnRoute' => __('No stations found on this route.', MRT_TEXT_DOMAIN),
        'errorLoadingStations' => __('Error loading stations. Please refresh the page.', MRT_TEXT_DOMAIN),
        'stopTimeSavedSuccessfully' => __('Stop time saved successfully.', MRT_TEXT_DOMAIN),
        'stopTimeAddedSuccessfully' => __('Stop time added successfully.', MRT_TEXT_DOMAIN),
        'endStationsSavedSuccessfully' => __('End stations saved successfully.', MRT_TEXT_DOMAIN),
        'selectDestination' => __('— Select Destination —', MRT_TEXT_DOMAIN),
        'selectRouteFirst' => __('Select a route first', MRT_TEXT_DOMAIN),
        'loading' => __('Loading...', MRT_TEXT_DOMAIN),
        'errorLoadingDestinations' => __('Error loading destinations', MRT_TEXT_DOMAIN),
        'saving' => __('Saving...', MRT_TEXT_DOMAIN),
        'adding' => __('Adding...', MRT_TEXT_DOMAIN),
        'timeHint' => __('Leave empty if train stops but time is not fixed', MRT_TEXT_DOMAIN),
        'pickup' => __('Pickup', MRT_TEXT_DOMAIN),
        'dropoff' => __('Dropoff', MRT_TEXT_DOMAIN),
        'edit' => __('Edit', MRT_TEXT_DOMAIN),
        'tripAdded' => __('Trip added successfully.', MRT_TEXT_DOMAIN),
        'tripRemoved' => __('Trip removed successfully.', MRT_TEXT_DOMAIN),
        'saved' => __('✓ Saved', MRT_TEXT_DOMAIN),
    ]);
}

/**
 * Enqueue admin assets
 *
 * @param string $hook Current admin page hook
 */
function MRT_enqueue_admin_assets(string $hook): void {
    if (!MRT_should_load_admin_assets($hook)) {
        return;
    }
    MRT_enqueue_admin_css($hook);
    MRT_enqueue_admin_js();
    MRT_localize_admin_script();
}
add_action('admin_enqueue_scripts', 'MRT_enqueue_admin_assets');

/**
 * Enqueue frontend assets for shortcodes
 */
function MRT_enqueue_frontend_assets(): void {
    // Check if any of our shortcodes are used on the page
    global $post;

    $shortcodes = ['museum_timetable_month', 'museum_timetable_overview', 'museum_journey_planner'];
    $has_shortcode = false;
    $has_overview_shortcode = false;

    // Check in post content
    if (is_a($post, 'WP_Post') && !empty($post->post_content)) {
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $has_shortcode = true;
                if ($shortcode === 'museum_timetable_overview') {
                    $has_overview_shortcode = true;
                }
            }
        }
    }

    if (!$has_shortcode) {
        $has_shortcode = apply_filters('mrt_should_enqueue_frontend_assets', false);
    }
    $has_overview_shortcode = apply_filters('mrt_should_enqueue_frontend_overview_css', $has_overview_shortcode);

    if (!$has_shortcode) {
        return;
    }

    // Enqueue frontend CSS (same structure as admin)
    wp_enqueue_style(
        'mrt-frontend-base',
        MRT_URL . 'assets/admin-base.css',
        [],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-components',
        MRT_URL . 'assets/admin-components.css',
        ['mrt-frontend-base'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-timetable',
        MRT_URL . 'assets/admin-timetable.css',
        ['mrt-frontend-components'],
        MRT_VERSION
    );

    $frontend_meta_deps = ['mrt-frontend-timetable'];
    if ($has_overview_shortcode) {
        wp_enqueue_style(
            'mrt-frontend-timetable-overview',
            MRT_URL . 'assets/admin-timetable-overview.css',
            ['mrt-frontend-timetable'],
            MRT_VERSION
        );
        $frontend_meta_deps[] = 'mrt-frontend-timetable-overview';
    }
    wp_enqueue_style(
        'mrt-frontend-meta-boxes',
        MRT_URL . 'assets/admin-meta-boxes.css',
        $frontend_meta_deps,
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-dashboard',
        MRT_URL . 'assets/admin-dashboard.css',
        ['mrt-frontend-meta-boxes'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-ui',
        MRT_URL . 'assets/admin-ui.css',
        ['mrt-frontend-dashboard'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-responsive',
        MRT_URL . 'assets/admin-responsive.css',
        ['mrt-frontend-ui'],
        MRT_VERSION
    );
    
    // Enqueue frontend JavaScript
    wp_enqueue_script(
        'mrt-frontend',
        MRT_URL . 'assets/frontend.js',
        ['jquery'],
        MRT_VERSION,
        true
    );
    
    // Localize script for AJAX and translations
    wp_localize_script('mrt-frontend', 'mrtFrontend', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mrt_frontend'),
        'search' => __('Search', MRT_TEXT_DOMAIN),
        'searching' => __('Searching...', MRT_TEXT_DOMAIN),
        'loading' => __('Loading...', MRT_TEXT_DOMAIN),
        'errorSearching' => __('Error searching for connections.', MRT_TEXT_DOMAIN),
        'errorLoading' => __('Error loading timetable.', MRT_TEXT_DOMAIN),
        'errorSameStations' => __('Please select different stations for departure and arrival.', MRT_TEXT_DOMAIN),
        'networkError' => __('Network error. Please try again.', MRT_TEXT_DOMAIN),
    ]);
}
add_action('wp_enqueue_scripts', 'MRT_enqueue_frontend_assets');

