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
    $direction = MRT_timetable_grid_direction($regular_stations);
    foreach ($regular_stations as $station) {
        $station_id = $station->ID;
        $station_row_label = $station->post_title;
        if (MRT_station_row_has_arrival_departure_split($station_id, $services_list)) {
            $html .= MRT_render_grid_station_time_row(
                $station,
                $services_list,
                $service_classes,
                $service_info,
                'arrival',
                sprintf(__('Till %s', 'museum-railway-timetable'), MRT_get_station_display_name($station)),
                'mrt-to-row mrt-print-split-arrival-row'
            );
            $html .= MRT_render_print_mid_transfer_row($station, $services_list, $service_classes, $service_info);
            $html .= MRT_render_grid_station_time_row(
                $station,
                $services_list,
                $service_classes,
                $service_info,
                'departure',
                sprintf(__('Från %s', 'museum-railway-timetable'), MRT_get_station_display_name($station)),
                'mrt-from-row mrt-print-split-departure-row'
            );
            continue;
        }
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
        if (MRT_should_render_print_bus_notice_after_station($station, $direction)) {
            $html .= MRT_render_print_bus_notice_row($direction);
        }
    }
    return $html;
}

/**
 * Direction marker for source-like printed inserts.
 *
 * @param array<int, WP_Post> $regular_stations
 */
function MRT_timetable_grid_direction(array $regular_stations): string {
    if ($regular_stations === []) {
        return '';
    }
    $first = $regular_stations[0]->post_title ?? '';
    return $first === 'Moga' ? 'inbound' : 'outbound';
}

/**
 * Whether a station has distinct arrival/departure times in any service.
 *
 * @param int $station_id
 * @param array<int, array<string, mixed>> $services_list
 */
function MRT_station_row_has_arrival_departure_split(int $station_id, array $services_list): bool {
    foreach ($services_list as $service_data) {
        $stop_time = $service_data['stop_times'][$station_id] ?? null;
        if (!$stop_time) {
            continue;
        }
        $arrival = $stop_time['arrival_time'] ?? '';
        $departure = $stop_time['departure_time'] ?? '';
        if ($arrival !== '' && $departure !== '' && $arrival !== $departure) {
            return true;
        }
    }
    return false;
}

/**
 * Render one synthetic arrival/departure row for a split station such as Marielund.
 *
 * @param WP_Post $station
 * @param array<int, array<string, mixed>> $services_list
 * @param array<int, array<int, string>> $service_classes
 * @param array<int, array<string, mixed>> $service_info
 */
function MRT_render_grid_station_time_row($station, array $services_list, array $service_classes, array $service_info, string $time_key, string $label, string $row_class): string {
    $station_id = $station->ID;
    ob_start();
    ?>
    <div class="mrt-grid-row <?php echo esc_attr($row_class); ?>">
        <div class="mrt-grid-cell mrt-station-col"><?php echo esc_html($label); ?></div>
        <?php foreach ($services_list as $idx => $service_data):
            $stop_time = $service_data['stop_times'][$station_id] ?? null;
            $time_display = '—';
            if ($stop_time && !empty($stop_time[$time_key . '_time'])) {
                $time_display = MRT_format_time_display((string) $stop_time[$time_key . '_time']);
            }
            $label_parts = MRT_get_service_label_parts($service_info[$idx]);
            $cell_aria = MRT_overview_grid_cell_aria_label($label, $label_parts, $time_display);
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
 * Source PDF train-change row at Marielund.
 *
 * @param WP_Post $station
 * @param array<int, array<string, mixed>> $services_list
 * @param array<int, array<int, string>> $service_classes
 * @param array<int, array<string, mixed>> $service_info
 */
function MRT_render_print_mid_transfer_row($station, array $services_list, array $service_classes, array $service_info): string {
    if (($station->post_title ?? '') !== 'Marielund') {
        return '';
    }
    $map = [
        '71' => ['type' => 'Dieseltåg', 'service' => '61'],
        '63' => ['type' => 'Rälsbuss', 'service' => '97'],
        '60' => ['type' => 'Ångtåg', 'service' => '74'],
        '96' => ['type' => 'Dieseltåg', 'service' => '64'],
    ];
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-print-transfer-inline-row">
        <div class="mrt-grid-cell mrt-station-col mrt-transfer-station-col"><?php esc_html_e('Tågbyte:', 'museum-railway-timetable'); ?></div>
        <?php foreach ($services_list as $idx => $service_data):
            $service_number = (string) ($service_info[$idx]['service_number'] ?? '');
            $transfer = $map[$service_number] ?? null;
        ?>
            <div class="mrt-grid-cell mrt-time-cell mrt-print-transfer-inline-cell <?php echo esc_attr(implode(' ', $service_classes[$idx])); ?>">
                <?php if ($transfer): ?>
                    <span class="mrt-print-transfer-inline-type"><?php echo esc_html($transfer['type']); ?></span>
                    <span class="mrt-print-transfer-inline-number"><?php echo esc_html($transfer['service']); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

function MRT_should_render_print_bus_notice_after_station($station, string $direction): bool {
    $name = $station->post_title ?? '';
    return ($direction === 'outbound' && $name === 'Selknä') || ($direction === 'inbound' && $name === 'Löt');
}

function MRT_render_print_bus_notice_row(string $direction): string {
    $label = $direction === 'inbound'
        ? __('Från Fjällnora* Till Selknä*', 'museum-railway-timetable')
        : __('Från Selknä* Till Fjällnora*', 'museum-railway-timetable');
    ob_start();
    ?>
    <div class="mrt-grid-row mrt-print-bus-notice-row">
        <div class="mrt-grid-cell mrt-station-col mrt-print-bus-notice-label"><?php echo esc_html($label); ?></div>
        <div class="mrt-grid-cell mrt-print-bus-notice-message"><?php esc_html_e('INGA BUSSANSLUTNINGAR TILL/FRÅN FJÄLLNORA', 'museum-railway-timetable'); ?></div>
    </div>
    <?php
    return ob_get_clean();
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
