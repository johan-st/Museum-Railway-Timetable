<?php
/**
 * Dashboard: Shortcodes documentation
 *
 * @package Museum_Railway_Timetable
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Render shortcodes documentation section
 */
function MRT_render_dashboard_shortcodes() {
    ?>
    <div class="mrt-section mrt-bg-info">
        <h2><?php esc_html_e('Shortcodes', 'museum-railway-timetable'); ?></h2>
        <p><?php esc_html_e('Use these shortcodes to display timetables on your pages and posts.', 'museum-railway-timetable'); ?></p>

        <div class="mrt-mt-1">
            <h3 class="mrt-heading mrt-mt-0">1. <?php esc_html_e('Month View', 'museum-railway-timetable'); ?></h3>
            <p><code>[museum_timetable_month month="2025-06" train_type="" service="" legend="1" show_counts="1"]</code></p>
            <p class="description">
                <?php esc_html_e('Displays a calendar month view showing which days have services running.', 'museum-railway-timetable'); ?><br>
                <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                • <code>month</code> - <?php esc_html_e('Month in YYYY-MM format (default: current month)', 'museum-railway-timetable'); ?><br>
                • <code>train_type</code> - <?php esc_html_e('Filter by train type slug (optional)', 'museum-railway-timetable'); ?><br>
                • <code>service</code> - <?php esc_html_e('Filter by exact service title (optional)', 'museum-railway-timetable'); ?><br>
                • <code>legend</code> - <?php esc_html_e('Show legend (0 or 1, default: 1)', 'museum-railway-timetable'); ?><br>
                • <code>show_counts</code> - <?php esc_html_e('Show service count per day (0 or 1, default: 1)', 'museum-railway-timetable'); ?><br>
                • <code>start_monday</code> - <?php esc_html_e('Start week on Monday (0 or 1, default: 1)', 'museum-railway-timetable'); ?>
            </p>
            <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
            <pre class="mrt-box mrt-code-block">[museum_timetable_month month="2025-06" train_type="steam" show_counts="1"]</pre>
        </div>

        <div class="mrt-mt-1">
            <h3>2. <?php esc_html_e('Journey wizard (multi-step)', 'museum-railway-timetable'); ?></h3>
            <p><code>[museum_journey_wizard]</code></p>
            <p class="description">
                <?php esc_html_e('Full mockup-style flow: route and trip type (one way / return), calendar with traffic-day states, outbound and optional return trips, summary with price matrix. Requires JavaScript.', 'museum-railway-timetable'); ?>
            </p>
            <p class="description mrt-mt-sm">
                <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                • <code>ticket_url</code> – <?php esc_html_e('URL for the “Continue to tickets” button (optional)', 'museum-railway-timetable'); ?><br>
                • <code>hero_image</code> – <?php esc_html_e('Background image URL for step 1 (optional)', 'museum-railway-timetable'); ?><br>
                • <code>hero_subtitle</code> – <?php esc_html_e('Optional subtitle line under the title on step 1', 'museum-railway-timetable'); ?>
            </p>
            <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
            <pre class="mrt-box mrt-code-block">[museum_journey_wizard ticket_url="https://example.com/biljetter" hero_subtitle="Välj rutt och datum"]</pre>
        </div>

        <div class="mrt-mt-1">
            <h3>3. <?php esc_html_e('Timetable Overview', 'museum-railway-timetable'); ?></h3>
            <p><code>[museum_timetable_overview timetable_id="123"]</code></p>
            <p class="description">
                <?php esc_html_e('Displays a complete timetable overview grouped by route and direction, showing all trips with train types and times. Similar to traditional printed timetables.', 'museum-railway-timetable'); ?>
            </p>
            <p class="description mrt-mt-sm">
                <strong><?php esc_html_e('What it shows:', 'museum-railway-timetable'); ?></strong><br>
                • <?php esc_html_e('All trips (services) in the timetable', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Grouped by route and direction (e.g., "Från Uppsala Ö Till Marielund")', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Train types with icons for each trip (🚂 Ångtåg, 🚌 Rälsbuss, 🚃 Dieseltåg)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Train numbers displayed prominently (or service ID as fallback)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Arrival/departure times in HH.MM format for each station', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Symbols: P (pickup only), A (dropoff only), X (no time), | (passes without stopping)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Transfer information showing connecting trains at destination stations', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Direction arrows (↓) for first and last stations', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Special styling for express services (yellow vertical bar)', 'museum-railway-timetable'); ?>
            </p>
            <p class="description mrt-mt-sm">
                <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                • <code>timetable_id</code> - <?php esc_html_e('Timetable post ID (recommended).', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;<?php esc_html_e('How to find it:', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;1. <?php esc_html_e('Go to Railway Timetable → Timetables', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;2. <?php esc_html_e('Look in the "ID" column - the number is displayed there', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;3. <?php esc_html_e('Or click "Edit" and look at the URL - the number after "post=" is the ID', 'museum-railway-timetable'); ?><br>
                • <code>timetable</code> - <?php esc_html_e('Timetable name (alternative to timetable_id). Use the exact title of the timetable.', 'museum-railway-timetable'); ?>
            </p>
            <p><strong><?php esc_html_e('Examples:', 'museum-railway-timetable'); ?></strong></p>
            <pre class="mrt-box mrt-code-block">[museum_timetable_overview timetable_id="123"]</pre>
            <p class="description mrt-mt-sm mrt-text-base">
                <?php esc_html_e('Or use the timetable name:', 'museum-railway-timetable'); ?>
            </p>
            <pre class="mrt-box mrt-code-block">[museum_timetable_overview timetable="Sommar 2025"]</pre>
            <p class="description mrt-mt-sm mrt-alert mrt-alert-warning">
                <strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('You can preview how the timetable will look in the "Timetable Overview" meta box when editing a timetable in the admin.', 'museum-railway-timetable'); ?>
            </p>
        </div>

        <div class="mrt-mt-1">
            <h3>4. <?php esc_html_e('Journey Planner (Reseplanerare)', 'museum-railway-timetable'); ?></h3>
            <p><code>[museum_journey_planner]</code></p>
            <p class="description">
                <?php esc_html_e('Displays a journey planner where users can search for connections between two stations on a specific date.', 'museum-railway-timetable'); ?><br>
                <strong><?php esc_html_e('What it shows:', 'museum-railway-timetable'); ?></strong><br>
                • <?php esc_html_e('Dropdown to select departure station (From)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Dropdown to select arrival station (To)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Date picker (defaults to today\'s date)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Search button to find connections', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Results table showing all available connections with departure/arrival times, train types, and service information', 'museum-railway-timetable'); ?>
            </p>
            <p class="description mrt-mt-sm">
                <strong><?php esc_html_e('Parameters:', 'museum-railway-timetable'); ?></strong><br>
                • <code>default_date</code> - <?php esc_html_e('Default date in YYYY-MM-DD format (optional, defaults to today)', 'museum-railway-timetable'); ?>
            </p>
            <p class="description mrt-mt-sm">
                <strong><?php esc_html_e('How it works:', 'museum-railway-timetable'); ?></strong><br>
                • <?php esc_html_e('Users select a departure station and arrival station', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Users can choose a date (defaults to today)', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('After clicking "Search", the planner finds all services that:', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;1. <?php esc_html_e('Run on the selected date', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;2. <?php esc_html_e('Stop at both the departure and arrival stations', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;3. <?php esc_html_e('Have the departure station before the arrival station in the route sequence', 'museum-railway-timetable'); ?><br>
                &nbsp;&nbsp;&nbsp;&nbsp;4. <?php esc_html_e('Allow pickup at departure station and dropoff at arrival station', 'museum-railway-timetable'); ?><br>
                • <?php esc_html_e('Results are sorted by departure time', 'museum-railway-timetable'); ?>
            </p>
            <p><strong><?php esc_html_e('Example:', 'museum-railway-timetable'); ?></strong></p>
            <pre class="mrt-box mrt-code-block">[museum_journey_planner]</pre>
            <p class="description mrt-mt-sm">
                <?php esc_html_e('Or with a default date:', 'museum-railway-timetable'); ?>
            </p>
            <pre class="mrt-box mrt-code-block">[museum_journey_planner default_date="2025-06-15"]</pre>
            <p class="description mrt-mt-sm mrt-alert mrt-alert-warning">
                <strong><?php esc_html_e('Tip:', 'museum-railway-timetable'); ?></strong> <?php esc_html_e('The journey planner automatically shows today\'s date by default, but users can select any date to check future connections. Make sure you have created timetables with dates and services with stop times for the dates you want to support.', 'museum-railway-timetable'); ?>
            </p>
        </div>
    </div>
    <?php
}
