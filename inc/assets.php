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
 * Base URL for plugin assets directory (trailing slash).
 */
function MRT_assets_base_url(): string {
    return MRT_URL . 'assets/';
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
    $is_taxonomy_page = in_array($hook, ['edit-tags.php', 'term.php'], true);

    if ($is_taxonomy_page) {
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field(wp_unslash($_GET['taxonomy'])) : '';
        if ($taxonomy === MRT_TAXONOMY_TRAIN_TYPE) {
            return true;
        }
    }

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
    $base = MRT_assets_base_url();
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
function MRT_enqueue_admin_js(): void {
    $a = MRT_assets_base_url();
    wp_register_script('mrt-string-utils', $a . 'mrt-string-utils.js', [], MRT_VERSION, true);
    wp_register_script('mrt-date-utils', $a . 'mrt-date-utils.js', [], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-utils', $a . 'admin-utils.js', ['jquery', 'mrt-date-utils', 'mrt-string-utils'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-route-ui', $a . 'admin-route-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-stoptimes-ui', $a . 'admin-stoptimes-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-timetable-services', $a . 'admin-timetable-services-ui.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin-service-edit', $a . 'admin-service-edit.js', ['mrt-admin-utils', 'jquery'], MRT_VERSION, true);
    wp_enqueue_script('mrt-admin', $a . 'admin.js', [
        'mrt-admin-utils', 'mrt-admin-route-ui', 'mrt-admin-stoptimes-ui', 'mrt-admin-timetable-services', 'mrt-admin-service-edit', 'jquery'
    ], MRT_VERSION, true);
}

/**
 * Strings passed to mrtAdmin (admin bundle).
 *
 * @return array<string, string>
 */
function MRT_admin_script_localization(): array {
    return [
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
    ];
}

/**
 * Localize admin script with translations and AJAX URL
 */
function MRT_localize_admin_script(): void {
    wp_localize_script('mrt-admin', 'mrtAdmin', MRT_admin_script_localization());
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
 * Month names and weekday abbreviations for the journey wizard calendar.
 *
 * @return array{monthNames: array<int, string>, weekdayAbbrev: array<int, string>}
 */
function MRT_journey_wizard_calendar_i18n_arrays(): array {
    $month_names = [];
    for ($m = 1; $m <= 12; $m++) {
        $month_names[] = date_i18n('F', mktime(0, 0, 0, $m, 15, 2020));
    }
    $weekday_abbrev = [];
    $sun = strtotime('2024-01-07 UTC');
    for ($d = 0; $d < 7; $d++) {
        $weekday_abbrev[] = date_i18n('D', $sun + $d * DAY_IN_SECONDS);
    }
    return [
        'monthNames' => $month_names,
        'weekdayAbbrev' => $weekday_abbrev,
    ];
}

/**
 * Wizard step labels and connection UI strings.
 *
 * @return array<string, string>
 */
function MRT_journey_wizard_l10n_steps_and_trip(): array {
    return [
        'stepRoute' => __('Sök resa', MRT_TEXT_DOMAIN),
        'stepDate' => __('Datum', MRT_TEXT_DOMAIN),
        'stepOutbound' => __('Utresa', MRT_TEXT_DOMAIN),
        'stepReturn' => __('Återresa', MRT_TEXT_DOMAIN),
        'stepSummary' => __('Sammanfattning', MRT_TEXT_DOMAIN),
        'loading' => __('Loading...', MRT_TEXT_DOMAIN),
        'errorGeneric' => __('Something went wrong. Please try again.', MRT_TEXT_DOMAIN),
        'noConnections' => __('No connections on this date.', MRT_TEXT_DOMAIN),
        'showStops' => __('Visa passerade stationer', MRT_TEXT_DOMAIN),
        'hideStops' => __('Dölj passerade stationer', MRT_TEXT_DOMAIN),
        'selectTrip' => __('Välj →', MRT_TEXT_DOMAIN),
        'noticeLabel' => __('Trafikmeddelande', MRT_TEXT_DOMAIN),
        'durationMinutes' => __('%d min', MRT_TEXT_DOMAIN),
        'outboundHeading' => __('Utresa', MRT_TEXT_DOMAIN),
        'returnHeading' => __('Återresa', MRT_TEXT_DOMAIN),
        'onDate' => __('on %s', MRT_TEXT_DOMAIN),
        'pleaseStations' => __('Please select both departure and arrival stations.', MRT_TEXT_DOMAIN),
        'tripTypeSingle' => __('Enkel', MRT_TEXT_DOMAIN),
        'tripTypeReturn' => __('Tur- och retur', MRT_TEXT_DOMAIN),
        'routeContext' => __('%1$s → %2$s | %3$s', MRT_TEXT_DOMAIN),
        'routeDateContext' => __('%1$s → %2$s | %3$s\n%4$s', MRT_TEXT_DOMAIN),
        'directTrip' => __('Direktresa', MRT_TEXT_DOMAIN),
        'transferTrip' => __('Byte', MRT_TEXT_DOMAIN),
        'selectedOutbound' => __('Vald utresa', MRT_TEXT_DOMAIN),
        'towards' => __('mot %s', MRT_TEXT_DOMAIN),
        'changeAt' => __('Byte vid %s', MRT_TEXT_DOMAIN),
        'transferWait' => __('%d min byte', MRT_TEXT_DOMAIN),
        'passedStations' => __('passerade stationer', MRT_TEXT_DOMAIN),
    ];
}

/**
 * Table column labels, calendar day strings, and trip captions.
 *
 * @return array<string, string>
 */
function MRT_journey_wizard_l10n_table_calendar(): array {
    return [
        'colService' => __('Service', MRT_TEXT_DOMAIN),
        'colTrainType' => __('Train Type', MRT_TEXT_DOMAIN),
        'colDeparture' => __('Departure', MRT_TEXT_DOMAIN),
        'colArrival' => __('Arrival', MRT_TEXT_DOMAIN),
        'colStation' => __('Station', MRT_TEXT_DOMAIN),
        'colActions' => __('Actions', MRT_TEXT_DOMAIN),
        'calendarGridLabel' => __('Travel dates calendar', MRT_TEXT_DOMAIN),
        'dayDateOk' => __('%s — connection available', MRT_TEXT_DOMAIN),
        'dayDateTraffic' => __('%s — traffic, no direct connection on this route', MRT_TEXT_DOMAIN),
        'dayDateNone' => __('%s — no traffic', MRT_TEXT_DOMAIN),
        'tripsCaptionOutbound' => __('Outbound trips for %s', MRT_TEXT_DOMAIN),
        'tripsCaptionReturn' => __('Return trips on the same day', MRT_TEXT_DOMAIN),
        'btnChooseTripAria' => __('Choose this trip: %1$s, departure %2$s, arrival %3$s', MRT_TEXT_DOMAIN),
        'btnShowStopsAria' => __('Visa hållplatser och tider för %s', MRT_TEXT_DOMAIN),
        'legSegmentLabel' => __('Delsträcka %d', MRT_TEXT_DOMAIN),
    ];
}

/**
 * Price matrix labels for the wizard summary.
 *
 * @return array<string, mixed>
 */
function MRT_journey_wizard_l10n_price(): array {
    return [
        'priceTableTypeColumn' => __('Ticket type', MRT_TEXT_DOMAIN),
        'priceTitle' => __('Priser', MRT_TEXT_DOMAIN),
        'priceNote' => __('Amounts are configured in timetable settings (same units as there).', MRT_TEXT_DOMAIN),
        'priceDash' => '—',
        'priceMatrix' => MRT_get_price_matrix(),
        'priceTickets' => MRT_price_ticket_type_labels(),
        'priceCategories' => MRT_price_category_labels(),
    ];
}

/**
 * Localized strings and labels for [museum_journey_wizard] script
 *
 * @return array<string, mixed>
 */
function MRT_journey_wizard_script_localization(): array {
    $cal = MRT_journey_wizard_calendar_i18n_arrays();
    return array_merge(
        [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mrt_frontend'),
            'monthNames' => $cal['monthNames'],
            'weekdayAbbrev' => $cal['weekdayAbbrev'],
        ],
        MRT_journey_wizard_l10n_steps_and_trip(),
        MRT_journey_wizard_l10n_table_calendar(),
        MRT_journey_wizard_l10n_price()
    );
}

/**
 * Detect which frontend shortcodes are present in post content.
 *
 * @return array{has_any: bool, has_overview: bool, has_journey_wizard: bool}
 */
function MRT_frontend_shortcode_flags_from_post(): array {
    global $post;
    $shortcodes = [
        'museum_timetable_month',
        'museum_timetable_overview',
        'museum_journey_planner',
        'museum_journey_wizard',
    ];
    $has_shortcode = false;
    $has_overview_shortcode = false;
    $has_journey_wizard = false;

    if (is_a($post, 'WP_Post') && !empty($post->post_content)) {
        foreach ($shortcodes as $shortcode) {
            if (!has_shortcode($post->post_content, $shortcode)) {
                continue;
            }
            $has_shortcode = true;
            if ($shortcode === 'museum_timetable_overview') {
                $has_overview_shortcode = true;
            }
            if ($shortcode === 'museum_journey_wizard') {
                $has_journey_wizard = true;
            }
        }
    }

    if (!$has_shortcode) {
        $has_shortcode = (bool) apply_filters('mrt_should_enqueue_frontend_assets', false);
    }
    $has_overview_shortcode = (bool) apply_filters('mrt_should_enqueue_frontend_overview_css', $has_overview_shortcode);

    return [
        'has_any' => $has_shortcode,
        'has_overview' => $has_overview_shortcode,
        'has_journey_wizard' => $has_journey_wizard,
    ];
}

/**
 * Base + components + timetable (frontend mirrors admin stack).
 */
function MRT_enqueue_frontend_style_admin_triplet(): void {
    $a = MRT_assets_base_url();
    wp_enqueue_style(
        'mrt-frontend-base',
        $a . 'admin-base.css',
        [],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-components',
        $a . 'admin-components.css',
        ['mrt-frontend-base'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-timetable',
        $a . 'admin-timetable.css',
        ['mrt-frontend-components'],
        MRT_VERSION
    );
}

/**
 * Optional overview layer; returns dependency list for meta-boxes handle.
 *
 * @return array<int, string>
 */
function MRT_enqueue_frontend_overview_style_maybe(bool $has_overview_shortcode): array {
    $deps = ['mrt-frontend-timetable'];
    if ($has_overview_shortcode) {
        wp_enqueue_style(
            'mrt-frontend-timetable-overview',
            MRT_assets_base_url() . 'admin-timetable-overview.css',
            ['mrt-frontend-timetable'],
            MRT_VERSION
        );
        $deps[] = 'mrt-frontend-timetable-overview';
    }
    return $deps;
}

/**
 * Meta-boxes, dashboard, UI, responsive (depends on meta deps array).
 *
 * @param array<int, string> $meta_box_deps
 */
function MRT_enqueue_frontend_style_upper_stack(array $meta_box_deps): void {
    $a = MRT_assets_base_url();
    wp_enqueue_style(
        'mrt-frontend-meta-boxes',
        $a . 'admin-meta-boxes.css',
        $meta_box_deps,
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-dashboard',
        $a . 'admin-dashboard.css',
        ['mrt-frontend-meta-boxes'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-ui',
        $a . 'admin-ui.css',
        ['mrt-frontend-dashboard'],
        MRT_VERSION
    );
    wp_enqueue_style(
        'mrt-frontend-responsive',
        $a . 'admin-responsive.css',
        ['mrt-frontend-ui'],
        MRT_VERSION
    );
}

/**
 * Enqueue stacked frontend CSS (shared shortcode bundle).
 */
function MRT_enqueue_frontend_shortcode_styles(bool $has_overview_shortcode): void {
    MRT_enqueue_frontend_style_admin_triplet();
    $meta_deps = MRT_enqueue_frontend_overview_style_maybe($has_overview_shortcode);
    MRT_enqueue_frontend_style_upper_stack($meta_deps);
}

/**
 * Enqueue shared frontend JS and mrtFrontend localization.
 */
function MRT_enqueue_frontend_base_scripts(): void {
    $a = MRT_assets_base_url();
    wp_register_script('mrt-string-utils', $a . 'mrt-string-utils.js', [], MRT_VERSION, true);
    wp_register_script('mrt-frontend-api', $a . 'mrt-frontend-api.js', ['jquery'], MRT_VERSION, true);

    wp_enqueue_script(
        'mrt-frontend',
        $a . 'frontend.js',
        ['jquery', 'mrt-string-utils', 'mrt-frontend-api'],
        MRT_VERSION,
        true
    );

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

/**
 * Enqueue journey wizard CSS/JS and localization (depends on frontend bundle).
 */
function MRT_enqueue_journey_wizard_assets(): void {
    $a = MRT_assets_base_url();
    wp_register_script('mrt-date-utils', $a . 'mrt-date-utils.js', [], MRT_VERSION, true);
    wp_enqueue_style(
        'mrt-journey-wizard',
        $a . 'journey-wizard.css',
        ['mrt-frontend-responsive'],
        MRT_VERSION
    );
    wp_enqueue_script(
        'mrt-journey-wizard',
        $a . 'journey-wizard.js',
        ['jquery', 'mrt-frontend', 'mrt-date-utils', 'mrt-string-utils', 'mrt-frontend-api'],
        MRT_VERSION,
        true
    );
    wp_localize_script('mrt-journey-wizard', 'mrtJourneyWizard', MRT_journey_wizard_script_localization());
}

/**
 * Enqueue frontend assets for shortcodes
 */
function MRT_enqueue_frontend_assets(): void {
    $flags = MRT_frontend_shortcode_flags_from_post();
    if (!$flags['has_any']) {
        return;
    }

    MRT_enqueue_frontend_shortcode_styles($flags['has_overview']);
    MRT_enqueue_frontend_base_scripts();

    if ($flags['has_journey_wizard']) {
        MRT_enqueue_journey_wizard_assets();
    }
}
add_action('wp_enqueue_scripts', 'MRT_enqueue_frontend_assets');
