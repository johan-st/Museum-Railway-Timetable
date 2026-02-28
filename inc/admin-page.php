<?php
/**
 * Admin Page – Menu, Settings, Dashboard
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

require_once MRT_PATH . 'inc/admin-page/dashboard.php';
require_once MRT_PATH . 'inc/admin-page/clear-db.php';
require_once MRT_PATH . 'inc/admin-page/admin-list.php';

// Add a top-level menu for the plugin settings
add_action('admin_menu', function () {
    // Main menu page
    add_menu_page(
        __('Museum Railway Timetable', 'museum-railway-timetable'),
        __('Railway Timetable', 'museum-railway-timetable'),
        'manage_options',
        'mrt_settings',
        'MRT_render_admin_page',
        'dashicons-calendar-alt'
    );

    // Add CPTs as submenus under main menu (WordPress automatically adds "Add New" links)
    add_submenu_page(
        'mrt_settings',
        __('Timetables', 'museum-railway-timetable'),
        __('Timetables', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_timetable'
    );

    add_submenu_page(
        'mrt_settings',
        __('Stations', 'museum-railway-timetable'),
        __('Stations', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_station'
    );

    add_submenu_page(
        'mrt_settings',
        __('Routes', 'museum-railway-timetable'),
        __('Routes', 'museum-railway-timetable'),
        'edit_posts',
        'edit.php?post_type=mrt_route'
    );

    // Train Types taxonomy
    add_submenu_page(
        'mrt_settings',
        __('Train Types', 'museum-railway-timetable'),
        __('Train Types', 'museum-railway-timetable'),
        'manage_categories',
        'edit-tags.php?taxonomy=mrt_train_type&post_type=mrt_service'
    );
});

// Register basic settings
add_action('admin_init', function () {
    register_setting('mrt_group', 'mrt_settings', [
        'type' => 'array',
        'sanitize_callback' => 'MRT_sanitize_settings',
        'default' => ['enabled' => true, 'note' => '']
    ]);

    add_settings_section(
        'mrt_main',
        __('General Settings', 'museum-railway-timetable'),
        function(){ echo '<p>' . esc_html__('Configure timetable display.', 'museum-railway-timetable') . '</p>'; },
        'mrt_settings'
    );

    add_settings_field(
        'mrt_enabled',
        __('Enable Plugin', 'museum-railway-timetable'),
        'MRT_render_enabled_field',
        'mrt_settings',
        'mrt_main'
    );

    add_settings_field(
        'mrt_note',
        __('Note', 'museum-railway-timetable'),
        'MRT_render_note_field',
        'mrt_settings',
        'mrt_main'
    );
});
