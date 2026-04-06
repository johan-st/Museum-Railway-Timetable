<?php
/**
 * Timetable grid – header and row rendering
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render timetable grid header (2 rows: train types and train numbers)
 */
function MRT_render_timetable_table_header($services_list, $service_classes, $service_info) {
    ob_start();
    ?>
    <div class="mrt-grid-header">
        <div class="mrt-grid-cell mrt-station-col-header">
            <?php esc_html_e('Station', 'museum-railway-timetable'); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            $info = $service_info[$idx];
        ?>
            <div class="mrt-grid-cell mrt-header-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <?php if ($info['train_type']): ?>
                    <span class="mrt-train-type-icon"><?php echo MRT_get_train_type_icon($info['train_type']); ?></span>
                    <span class="mrt-train-type"><?php echo esc_html($info['train_type']->name); ?></span>
                <?php else: ?>
                    <span class="mrt-train-type">—</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="mrt-grid-cell mrt-station-col-header-empty" aria-hidden="true"></div>
        <?php foreach ($services_list as $idx => $service_data):
            $info = $service_info[$idx];
        ?>
            <div class="mrt-grid-cell mrt-header-train-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <span class="mrt-service-number"><?php echo esc_html($info['service_number']); ?></span>
                <?php if ($info['is_special'] && !empty($info['special_name'])): ?>
                    <span class="mrt-special-label"><?php echo esc_html($info['special_name']); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render "Från [station]" row
 */
function MRT_render_grid_from_row($first_station, $services_list, $service_classes, $service_info) {
    $first_station_id = $first_station->ID;
    $station_row_label = sprintf(
        __('Från %s', 'museum-railway-timetable'),
        MRT_get_station_display_name($first_station)
    );
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-from-row">
        <div class="mrt-grid-cell mrt-station-col">
            <?php printf(esc_html__('Från %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($first_station))); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            $stop_time = $service_data['stop_times'][$first_station_id] ?? null;
            $display = MRT_get_from_row_display_stop_time($stop_time);
            $time_display = MRT_format_stop_time_display($display ?? $stop_time);
            $label_parts = MRT_get_service_label_parts($service_info[$idx]);
            $special_name = $service_info[$idx]['special_name'] ?? '';
            $cell_aria = MRT_overview_grid_cell_aria_label($station_row_label, $label_parts, $time_display . ($special_name ? ' ' . $special_name : ''));
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-from-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                 data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>"
                 aria-label="<?php echo esc_attr($cell_aria); ?>">
                <?php echo esc_html($time_display); ?>
                <?php if (!empty($special_name)): ?>
                    <span class="mrt-special-label-inline"><?php echo esc_html($special_name); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render regular station rows (middle stations)
 */
function MRT_render_grid_regular_station_rows($regular_stations, $services_list, $service_classes, $service_info) {
    $html = '';
        foreach ($regular_stations as $station) {
        $station_id = $station->ID;
        $station_row_label = $station->post_title;
        ob_start();
        ?>
        <div class="mrt-grid-row">
            <div class="mrt-grid-cell mrt-station-col">
                <?php echo esc_html($station->post_title); ?>
            </div>
            <?php foreach ($services_list as $idx => $service_data):
                $stop_time = $service_data['stop_times'][$station_id] ?? null;
                $time_display = MRT_format_stop_time_display($stop_time);
                $label_parts = MRT_get_service_label_parts($service_info[$idx]);
                $cell_aria = MRT_overview_grid_cell_aria_label($station_row_label, $label_parts, $time_display);
            ?>
                <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                     data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                     data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>"
                     aria-label="<?php echo esc_attr($cell_aria); ?>">
                    <?php echo esc_html($time_display); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        $html .= ob_get_clean();
    }
    return $html;
}

/**
 * Render "Till [station]" row
 */
function MRT_render_grid_to_row($last_station, $services_list, $service_classes, $service_info) {
    $last_station_id = $last_station->ID;
    $station_row_label = sprintf(
        __('Till %s', 'museum-railway-timetable'),
        MRT_get_station_display_name($last_station)
    );
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-to-row">
        <div class="mrt-grid-cell mrt-station-col">
            <?php printf(esc_html__('Till %s', 'museum-railway-timetable'), esc_html(MRT_get_station_display_name($last_station))); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            $stop_time = $service_data['stop_times'][$last_station_id] ?? null;
            $display = MRT_get_to_row_display_stop_time($stop_time);
            $time_display = MRT_format_stop_time_display($display ?? $stop_time);
            $label_parts = MRT_get_service_label_parts($service_info[$idx]);
            $cell_aria = MRT_overview_grid_cell_aria_label($station_row_label, $label_parts, $time_display);
        ?>
            <div class="mrt-grid-cell mrt-time-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 data-service-number="<?php echo esc_attr($service_info[$idx]['service_number']); ?>"
                 data-service-label="<?php echo esc_attr(implode(' ', $label_parts)); ?>"
                 aria-label="<?php echo esc_attr($cell_aria); ?>">
                <?php echo esc_html($time_display); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render "Tågbyte:" transfer rows (2 rows: train types + train numbers)
 */
function MRT_render_grid_transfer_rows($services_list, $service_classes, $all_connections, $service_info) {
    $transfer_prefix = __('Tågbyte:', 'museum-railway-timetable');
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-transfer-row">
        <div class="mrt-grid-cell mrt-station-col mrt-transfer-station-col">
            <?php esc_html_e('Tågbyte:', 'museum-railway-timetable'); ?>
        </div>
        <?php foreach ($services_list as $idx => $service_data):
            if (isset($all_connections[$idx]) && !empty($all_connections[$idx])):
                $first_conn = $all_connections[$idx][0];
                $train_type_name = !empty($first_conn['train_type']) ? $first_conn['train_type'] : '';
                $label_parts = MRT_get_service_label_parts($service_info[$idx]);
                $cell_aria = MRT_overview_grid_cell_aria_label($transfer_prefix, array_merge($label_parts, [$train_type_name]), $train_type_name);
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 aria-label="<?php echo esc_attr($cell_aria); ?>">
                <?php echo esc_html($train_type_name); ?>
            </div>
        <?php else: ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-train-type <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"></div>
        <?php endif;
        endforeach; ?>
    </div>
    <div class="mrt-grid-row mrt-transfer-row">
        <div class="mrt-grid-cell mrt-station-col-empty" aria-hidden="true"></div>
        <?php foreach ($services_list as $idx => $service_data):
            if (isset($all_connections[$idx]) && !empty($all_connections[$idx])):
                [$conn_text, $conn_plain] = MRT_render_grid_transfer_conn_chunks($all_connections[$idx]);
                $label_parts = MRT_get_service_label_parts($service_info[$idx]);
                $joined_plain = implode(', ', $conn_plain);
                $cell_aria = MRT_overview_grid_cell_aria_label($transfer_prefix, $label_parts, $joined_plain);
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-service-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"
                 aria-label="<?php echo esc_attr($cell_aria); ?>">
                <?php echo implode(', ', $conn_text); ?>
            </div>
        <?php else: ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-transfer-service-number <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>"></div>
        <?php endif;
        endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
