<?php
/**
 * Dashboard: public journey price matrix (option mrt_price_matrix)
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Human labels for ticket-type rows
 *
 * @return array<string, string>
 */
function MRT_price_ticket_type_labels() {
    return [
        'single' => __('Single ticket', 'museum-railway-timetable'),
        'return' => __('Return ticket', 'museum-railway-timetable'),
        'day' => __('Day pass', 'museum-railway-timetable'),
    ];
}

/**
 * Human labels for passenger columns
 *
 * @return array<string, string>
 */
function MRT_price_category_labels() {
    return [
        'adult' => __('Adult', 'museum-railway-timetable'),
        'child_4_15' => __('Child 4–15', 'museum-railway-timetable'),
        'child_0_3' => __('Child 0–3', 'museum-railway-timetable'),
        'student_senior' => __('Student / senior 65+', 'museum-railway-timetable'),
    ];
}

/**
 * Render price matrix settings fields
 */
function MRT_render_price_matrix_field() {
    $matrix = MRT_get_price_matrix();
    $tlabels = MRT_price_ticket_type_labels();
    $clabels = MRT_price_category_labels();
    ?>
    <p class="description"><?php esc_html_e('Prices in minor units (e.g. SEK). Leave empty if unknown. Used by the public journey API.', 'museum-railway-timetable'); ?></p>
    <table class="widefat striped mrt-price-matrix-table mrt-mt-sm">
        <thead>
            <tr>
                <th><?php esc_html_e('Ticket type', 'museum-railway-timetable'); ?></th>
                <?php foreach ($clabels as $ckey => $clabel) : ?>
                    <th><?php echo esc_html($clabel); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (MRT_price_ticket_type_keys() as $tkey) : ?>
                <tr>
                    <th scope="row"><?php echo esc_html($tlabels[$tkey] ?? $tkey); ?></th>
                    <?php foreach (MRT_price_category_keys() as $ckey) :
                        $name = sprintf('mrt_price_matrix[%s][%s]', $tkey, $ckey);
                        $val = isset($matrix[$tkey][$ckey]) && $matrix[$tkey][$ckey] !== null
                            ? (int) $matrix[$tkey][$ckey] : '';
                        ?>
                        <td>
                            <input type="number" min="0" step="1" class="small-text"
                                name="<?php echo esc_attr($name); ?>"
                                value="<?php echo esc_attr((string) $val); ?>"
                                placeholder="—" />
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
