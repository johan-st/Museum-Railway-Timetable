<?php
/**
 * Import Lennakatten 2026 test data from PDF
 * Creates stations, routes, train types, timetables and services
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

require_once MRT_PATH . 'inc/import-lennakatten/import-data.php';
require_once MRT_PATH . 'inc/import-lennakatten/import-run.php';

add_action('admin_menu', function() {
    add_submenu_page(
        'mrt_settings',
        __('Import Lennakatten', 'museum-railway-timetable'),
        __('Import Lennakatten', 'museum-railway-timetable'),
        'manage_options',
        'mrt_import_lennakatten',
        'MRT_render_import_page'
    );
});

/**
 * Render import page
 */
function MRT_render_import_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';
    if (isset($_POST['mrt_import_lennakatten']) && check_admin_referer('mrt_import_lennakatten', 'mrt_import_nonce')) {
        $message = MRT_run_lennakatten_import();
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Import Lennakatten 2026 Test Data', 'museum-railway-timetable'); ?></h1>
        <p><?php esc_html_e('This imports test data from the Lennakatten folder 2026 PDF: stations, routes, train types, GRÖN timetable with services and stop times.', 'museum-railway-timetable'); ?></p>
        <?php if ($message): ?>
            <div class="notice notice-success"><p><?php echo wp_kses_post($message); ?></p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('mrt_import_lennakatten', 'mrt_import_nonce'); ?>
            <p>
                <input type="submit" name="mrt_import_lennakatten" class="button button-primary" value="<?php esc_attr_e('Run Import', 'museum-railway-timetable'); ?>" />
            </p>
        </form>
    </div>
    <?php
}
