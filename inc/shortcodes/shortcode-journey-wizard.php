<?php
/**
 * Shortcode: multi-step journey wizard [museum_journey_wizard]
 *
 * Attributes: ticket_url, hero_image (cover URL for step 1), hero_subtitle (optional line under title).
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Optional hero background: CSS custom property for cover image (see journey-wizard.css).
 *
 * @param string $hero_image Attachment URL or empty
 * @return string Safe HTML attributes for the opening tag (leading space when non-empty; escaped).
 */
function MRT_journey_wizard_hero_bg_attr($hero_image) {
    $hero_image = is_string($hero_image) ? trim($hero_image) : '';
    if ($hero_image === '') {
        return '';
    }
    $u = esc_url($hero_image);
    return ' data-has-hero-bg="1" style="' . esc_attr('--mrt-hero-image: url("' . $u . '")') . '"';
}

/**
 * Output one station select field
 *
 * @param string                $id Input id/name suffix
 * @param string                $label Accessible label
 * @param array<int>            $stations Station post IDs
 * @param int                   $selected Selected ID
 * @param string                $placeholder Option text
 * @return void
 */
function MRT_render_journey_wizard_station_select($id, $label, $stations, $selected, $placeholder) {
    $field_id = 'mrt_wizard_' . $id;
    ?>
    <div class="mrt-form-field">
        <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($label); ?></label>
        <select name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" required>
            <option value=""><?php echo esc_html($placeholder); ?></option>
            <?php foreach ($stations as $station_id) : ?>
                <option value="<?php echo esc_attr((string) $station_id); ?>" <?php selected($selected, $station_id); ?>>
                    <?php echo esc_html(get_the_title($station_id)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * Route step: station fields, trip type, primary action
 *
 * @param array<int> $stations Station IDs
 * @return void
 */
function MRT_render_journey_wizard_route_form_fields(array $stations) {
    MRT_render_journey_wizard_station_select(
        'from',
        __('Från', 'museum-railway-timetable'),
        $stations,
        0,
        __('Var börjar du din resa?', 'museum-railway-timetable')
    );
    MRT_render_journey_wizard_station_select(
        'to',
        __('Till', 'museum-railway-timetable'),
        $stations,
        0,
        __('Vart vill du resa?', 'museum-railway-timetable')
    );
    ?>
    <fieldset class="mrt-form-field mrt-journey-wizard__trip-type">
        <legend class="mrt-sr-only"><?php esc_html_e('Restyp', 'museum-railway-timetable'); ?></legend>
        <div class="mrt-journey-wizard__trip-type-toggle">
            <label class="mrt-journey-wizard__radio-label">
                <input type="radio" name="mrt_wizard_trip_type" value="single" checked>
                <span class="mrt-journey-wizard__radio-text" aria-hidden="true">→</span>
                <span class="mrt-journey-wizard__radio-text"><?php esc_html_e('Enkel', 'museum-railway-timetable'); ?></span>
            </label>
            <label class="mrt-journey-wizard__radio-label">
                <input type="radio" name="mrt_wizard_trip_type" value="return">
                <span class="mrt-journey-wizard__radio-text" aria-hidden="true">↔</span>
                <span class="mrt-journey-wizard__radio-text"><?php esc_html_e('Tur och retur', 'museum-railway-timetable'); ?></span>
            </label>
        </div>
    </fieldset>
    <div class="mrt-form-field mrt-journey-wizard__actions">
        <button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-next="route">
            <?php esc_html_e('Sök resa', 'museum-railway-timetable'); ?>
        </button>
    </div>
    <?php
}

/**
 * Step 1: route and trip type
 *
 * @param array<int>        $stations Station IDs
 * @param string            $title_id Heading id (aria-labelledby target)
 * @param string            $panel_id Panel wrapper id
 * @param array<string,mixed> $hero Optional keys: image (url), subtitle (string)
 * @return void
 */
function MRT_render_journey_wizard_step_route(array $stations, $title_id, $panel_id, array $hero = []) {
    $hero_subtitle = isset($hero['subtitle']) && is_string($hero['subtitle']) ? trim($hero['subtitle']) : '';
    ?>
    <div
        class="mrt-journey-wizard__panel mrt-journey-wizard__panel--active mrt-journey-wizard__search-panel"
        id="<?php echo esc_attr($panel_id); ?>"
        data-wizard-step="route"
        role="region"
        aria-labelledby="<?php echo esc_attr($title_id); ?>"
    >
        <header class="mrt-journey-wizard__hero-head">
            <h2 class="mrt-journey-wizard__hero-title" id="<?php echo esc_attr($title_id); ?>">
                <?php esc_html_e('Sök din resa med Lennakatten', 'museum-railway-timetable'); ?>
            </h2>
            <?php if ($hero_subtitle !== '') : ?>
                <p class="mrt-journey-wizard__hero-lede"><?php echo esc_html($hero_subtitle); ?></p>
            <?php endif; ?>
        </header>
        <div class="mrt-form-fields mrt-journey-wizard__route">
            <?php MRT_render_journey_wizard_route_form_fields($stations); ?>
        </div>
    </div>
    <?php
}

/**
 * Shared mock-style step header.
 *
 * @param string $back_step Step key for back button
 * @return void
 */
function MRT_render_journey_wizard_step_context_header(string $back_step): void {
    ?>
    <header class="mrt-journey-wizard__step-head">
        <button type="button" class="mrt-journey-wizard__back" data-wizard-back="<?php echo esc_attr($back_step); ?>">
            <?php esc_html_e('← Tillbaka', 'museum-railway-timetable'); ?>
        </button>
        <p class="mrt-journey-wizard__context" data-wizard-context></p>
    </header>
    <?php
}

/**
 * Step 2: calendar placeholder + legend
 *
 * @param string $title_id Heading id
 * @param string $panel_id Panel id
 * @return void
 */
function MRT_render_journey_wizard_step_date($title_id, $panel_id) {
    ?>
    <div
        class="mrt-journey-wizard__panel"
        id="<?php echo esc_attr($panel_id); ?>"
        data-wizard-step="date"
        role="region"
        aria-labelledby="<?php echo esc_attr($title_id); ?>"
        hidden
    >
        <?php MRT_render_journey_wizard_step_context_header('date'); ?>
        <h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr($title_id); ?>">
            <?php esc_html_e('Välj datum', 'museum-railway-timetable'); ?>
        </h3>
        <div class="mrt-journey-wizard__calendar-nav mrt-mb-sm" aria-label="<?php esc_attr_e('Calendar month navigation', 'museum-railway-timetable'); ?>">
            <button type="button" class="mrt-journey-wizard__cal-prev" aria-label="<?php esc_attr_e('Previous month', 'museum-railway-timetable'); ?>">‹</button>
            <span class="mrt-journey-wizard__cal-title" aria-live="polite"></span>
            <button type="button" class="mrt-journey-wizard__cal-next" aria-label="<?php esc_attr_e('Next month', 'museum-railway-timetable'); ?>">›</button>
            <button type="button" class="mrt-journey-wizard__cal-today" data-wizard-current-month>
                <?php esc_html_e('Denna månad', 'museum-railway-timetable'); ?>
            </button>
        </div>
        <div
            class="mrt-journey-wizard__calendar mrt-mb-sm"
            data-wizard-calendar
            role="region"
            aria-label="<?php esc_attr_e('Travel dates calendar', 'museum-railway-timetable'); ?>"
        ></div>
        <ul class="mrt-journey-wizard__legend mrt-text-secondary mrt-mb-sm" aria-label="<?php esc_attr_e('Calendar legend', 'museum-railway-timetable'); ?>">
            <li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--ok" aria-hidden="true"></span> <?php esc_html_e('Lennakatten trafikerar den valda resan', 'museum-railway-timetable'); ?></li>
            <li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--traffic" aria-hidden="true"></span> <?php esc_html_e('Lennakatten trafikerar, men ej den valda resan', 'museum-railway-timetable'); ?></li>
            <li><span class="mrt-journey-wizard__swatch mrt-journey-wizard__swatch--none" aria-hidden="true"></span> <?php esc_html_e('Ingen trafik', 'museum-railway-timetable'); ?></li>
        </ul>
    </div>
    <?php
}

/**
 * Steps 3–5: outbound, return, summary (filled by JS)
 *
 * @param string $title_out  Outbound heading id
 * @param string $panel_out  Outbound panel id
 * @param string $title_ret  Return heading id
 * @param string $panel_ret  Return panel id
 * @param string $title_sum  Summary heading id
 * @param string $panel_sum  Summary panel id
 * @return void
 */
function MRT_render_journey_wizard_step_placeholders($title_out, $panel_out, $title_ret, $panel_ret, $title_sum, $panel_sum) {
    ?>
    <div
        class="mrt-journey-wizard__panel"
        id="<?php echo esc_attr($panel_out); ?>"
        data-wizard-step="outbound"
        role="region"
        aria-labelledby="<?php echo esc_attr($title_out); ?>"
        hidden
    >
        <?php MRT_render_journey_wizard_step_context_header('outbound'); ?>
        <h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr($title_out); ?>">
            <?php esc_html_e('Välj utresa', 'museum-railway-timetable'); ?>
        </h3>
        <div data-wizard-outbound></div>
    </div>
    <div
        class="mrt-journey-wizard__panel"
        id="<?php echo esc_attr($panel_ret); ?>"
        data-wizard-step="return"
        role="region"
        aria-labelledby="<?php echo esc_attr($title_ret); ?>"
        hidden
    >
        <?php MRT_render_journey_wizard_step_context_header('return'); ?>
        <div data-wizard-return-summary class="mrt-journey-wizard__selected-trip"></div>
        <h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr($title_ret); ?>">
            <?php esc_html_e('Välj återresa', 'museum-railway-timetable'); ?>
        </h3>
        <div data-wizard-return></div>
    </div>
    <div
        class="mrt-journey-wizard__panel"
        id="<?php echo esc_attr($panel_sum); ?>"
        data-wizard-step="summary"
        role="region"
        aria-labelledby="<?php echo esc_attr($title_sum); ?>"
        hidden
    >
        <?php MRT_render_journey_wizard_step_context_header('summary'); ?>
        <h3 class="mrt-journey-wizard__step-title" id="<?php echo esc_attr($title_sum); ?>">
            <?php esc_html_e('Din resa', 'museum-railway-timetable'); ?>
        </h3>
        <div data-wizard-summary></div>
        <p class="mrt-mt-sm" data-wizard-ticket-wrap hidden>
            <a href="#" class="mrt-btn mrt-btn--primary mrt-journey-wizard__cta" data-wizard-ticket><?php esc_html_e('Fortsätt till biljetter', 'museum-railway-timetable'); ?></a>
        </p>
    </div>
    <?php
}

/**
 * @return array{ticket_url: string, hero: array{image: string, subtitle: string}}
 */
function MRT_journey_wizard_parse_shortcode_atts($atts): array {
    $atts = shortcode_atts(
        [
            'ticket_url' => '',
            'hero_image' => '',
            'hero_subtitle' => '',
        ],
        (array) $atts,
        'museum_journey_wizard'
    );

    return [
        'ticket_url' => esc_url($atts['ticket_url']),
        'hero' => [
            'image' => is_string($atts['hero_image']) ? $atts['hero_image'] : '',
            'subtitle' => is_string($atts['hero_subtitle']) ? $atts['hero_subtitle'] : '',
        ],
    ];
}

/**
 * @return array<string, string>
 */
function MRT_journey_wizard_step_element_ids(string $u): array {
    return [
        'route_title' => $u . '-route-t',
        'route_panel' => $u . '-route-p',
        'date_title' => $u . '-date-t',
        'date_panel' => $u . '-date-p',
        'out_title' => $u . '-out-t',
        'out_panel' => $u . '-out-p',
        'ret_title' => $u . '-ret-t',
        'ret_panel' => $u . '-ret-p',
        'sum_title' => $u . '-sum-t',
        'sum_panel' => $u . '-sum-p',
    ];
}

/**
 * Render [museum_journey_wizard]
 *
 * @param array|string $atts Shortcode attributes
 * @return string HTML
 */
function MRT_render_shortcode_journey_wizard($atts) {
    $parsed = MRT_journey_wizard_parse_shortcode_atts($atts);
    $ticket_url = $parsed['ticket_url'];
    $hero = $parsed['hero'];
    $hero_image = isset($hero['image']) && is_string($hero['image']) ? trim($hero['image']) : '';
    $hero_attr = MRT_journey_wizard_hero_bg_attr($hero_image);

    $stations = MRT_get_all_stations();
    if (empty($stations)) {
        return '<p class="mrt-alert mrt-alert-info">' . esc_html__('No stations are available.', 'museum-railway-timetable') . '</p>';
    }
    $u = wp_unique_id('mrtjw');
    $ids = MRT_journey_wizard_step_element_ids($u);
    ob_start();
    ?>
    <div
        class="mrt-journey-wizard mrt-my-lg"
        data-ticket-url="<?php echo $ticket_url ? esc_attr($ticket_url) : ''; ?>"
        data-start-of-week="<?php echo esc_attr((string) (int) get_option('start_of_week', '1')); ?>"
    >
        <section class="mrt-journey-wizard__hero"<?php echo $hero_attr; ?>>
            <div class="mrt-journey-wizard__hero-inner">
                <noscript>
                    <p class="mrt-alert mrt-alert-info"><?php esc_html_e('This planner needs JavaScript enabled.', 'museum-railway-timetable'); ?></p>
                </noscript>
                <div id="<?php echo esc_attr($u); ?>-errors" class="mrt-journey-wizard__errors" role="alert" aria-live="assertive" aria-relevant="additions text"></div>
                <nav class="mrt-journey-wizard__nav" aria-label="<?php esc_attr_e('Trip planner steps', 'museum-railway-timetable'); ?>">
                    <ol class="mrt-journey-wizard__steps" data-wizard-steps></ol>
                </nav>
                <div class="mrt-journey-wizard__panels">
                    <?php
                    MRT_render_journey_wizard_step_route($stations, $ids['route_title'], $ids['route_panel'], $hero);
                    MRT_render_journey_wizard_step_date($ids['date_title'], $ids['date_panel']);
                    MRT_render_journey_wizard_step_placeholders(
                        $ids['out_title'],
                        $ids['out_panel'],
                        $ids['ret_title'],
                        $ids['ret_panel'],
                        $ids['sum_title'],
                        $ids['sum_panel']
                    );
                    ?>
                </div>
            </div>
        </section>
    </div>
    <?php
    return (string) ob_get_clean();
}
