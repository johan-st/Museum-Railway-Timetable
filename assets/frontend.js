/**
 * Frontend JavaScript for Museum Railway Timetable
 * Handles AJAX interactions for shortcodes
 */

(function($) {
    'use strict';

    function showError($container, message) {
        var $div = $('<div class="mrt-alert mrt-alert-error"></div>');
        $div.text(message);
        $container.html($div);
    }

    /**
     * Initialize Journey Planner AJAX
     */
    function initJourneyPlanner() {
        var $planner = $('.mrt-journey-planner');
        if (!$planner.length) return;

        var $form = $planner.find('.mrt-journey-form');
        var $results = $planner.find('.mrt-journey-results');
        var $searchBtn = $form.find('button[type="submit"]');
        
        // Prevent default form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            searchJourney();
        });

        function searchJourney() {
            var fromStation = $form.find('#mrt_from').val();
            var toStation = $form.find('#mrt_to').val();
            var date = $form.find('#mrt_date').val();

            // Validation
            if (!fromStation || !toStation || !date) {
                return;
            }

            if (fromStation === toStation) {
                showError($results, typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorSameStations : 'Please select different stations for departure and arrival.');
                return;
            }

            // Show loading state
            $searchBtn.prop('disabled', true).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.searching : 'Searching...');
            $results.html('<div class="mrt-empty mrt-empty--loading">' + (typeof mrtFrontend !== 'undefined' ? mrtFrontend.loading : 'Loading...') + '</div>');

            // AJAX request
            $.ajax({
                url: (typeof mrtFrontend !== 'undefined' && mrtFrontend.ajaxurl) ? mrtFrontend.ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'mrt_search_journey',
                    nonce: (typeof mrtFrontend !== 'undefined' && mrtFrontend.nonce) ? mrtFrontend.nonce : '',
                    from_station: fromStation,
                    to_station: toStation,
                    date: date
                },
                success: function(response) {
                    $searchBtn.prop('disabled', false).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.search : 'Search');
                    
                    if (response.success) {
                        $results.html(response.data.html);
                        
                        // Update URL without reload (optional)
                        if (history.pushState) {
                            var url = new URL(window.location);
                            url.searchParams.set('mrt_from', fromStation);
                            url.searchParams.set('mrt_to', toStation);
                            url.searchParams.set('mrt_date', date);
                            history.pushState({}, '', url);
                        }
                    } else {
                        showError($results, response.data.message || (typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorSearching : 'Error searching for connections.'));
                    }
                },
                error: function() {
                    $searchBtn.prop('disabled', false).text(typeof mrtFrontend !== 'undefined' ? mrtFrontend.search : 'Search');
                    showError($results, typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : 'Network error. Please try again.');
                }
            });
        }
    }

    /**
     * Initialize Month Calendar with clickable days
     */
    function initMonthCalendar() {
        var $month = $('.mrt-month');
        if (!$month.length) return;

        var $container = $month.find('.mrt-day-timetable-container');
        if (!$container.length) return;

        // Handle click on day
        $month.on('click', '.mrt-day-clickable', function(e) {
            e.preventDefault();
            var $day = $(this);
            var date = $day.data('date');
            
            if (!date) return;

            // Get train type filter from shortcode attributes (if available)
            var trainType = $month.data('train-type') || '';

            // Remove previous active state
            $month.find('.mrt-day-clickable').removeClass('mrt-day-active');
            $day.addClass('mrt-day-active');

            // Show loading
            $container.html('<div class="mrt-empty mrt-empty--loading">' + (typeof mrtFrontend !== 'undefined' ? mrtFrontend.loading : 'Loading...') + '</div>').slideDown(300);

            // AJAX request
            $.ajax({
                url: (typeof mrtFrontend !== 'undefined' && mrtFrontend.ajaxurl) ? mrtFrontend.ajaxurl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'mrt_get_timetable_for_date',
                    nonce: (typeof mrtFrontend !== 'undefined' && mrtFrontend.nonce) ? mrtFrontend.nonce : '',
                    date: date,
                    train_type: trainType
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data.html);
                    } else {
                        var msg = response.data.message || (typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorLoading : 'Error loading timetable.');
                        showError($container, msg);
                    }
                },
                error: function() {
                    var msg = typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : 'Network error. Please try again.';
                    showError($container, msg);
                }
            });
        });
    }

    /**
     * Initialize all frontend features
     */
    function init() {
        initJourneyPlanner();
        initMonthCalendar();
    }

    // Initialize when DOM is ready
    $(document).ready(init);

    // Also initialize for dynamically loaded content (e.g., AJAX-loaded pages)
    $(document).on('mrt_reinit', init);

})(jQuery);

