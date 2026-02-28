/**
 * Route UI: add/remove/reorder route stations
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    function updateRouteStationOrders() {
        var $rows = $('#mrt-route-stations-tbody tr:not(.mrt-new-route-station-row)');
        var totalRows = $rows.length;

        $rows.each(function(index) {
            $(this).find('td:first').text(index + 1);

            var $upBtn = $(this).find('.mrt-move-route-station-up');
            var $downBtn = $(this).find('.mrt-move-route-station-down');

            $upBtn.prop('disabled', index === 0);
            $downBtn.prop('disabled', index === totalRows - 1);
        });

        var stationIds = [];
        $rows.each(function() {
            stationIds.push($(this).data('station-id'));
        });
        $('#mrt_route_stations').val(stationIds.join(','));

        var nextOrder = totalRows + 1;
        $('.mrt-new-route-station-row td:first').text(nextOrder);
    }

    function bindAddStation($container) {
        $('#mrt-add-route-station').on('click', function() {
            var $select = $('#mrt-new-route-station');
            var stationId = $select.val();
            if (!stationId) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseSelectStation : 'Please select a station.');
                return;
            }

            var currentStations = $('#mrt_route_stations').val().split(',').filter(function(id) { return id; });
            if (currentStations.indexOf(stationId) !== -1) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.stationAlreadyOnRoute : 'This station is already on the route.');
                return;
            }

            currentStations.push(stationId);
            $('#mrt_route_stations').val(currentStations.join(','));

            var stationName = $select.find('option:selected').text();
            var $tbody = $('#mrt-route-stations-tbody');
            var $newRow = $tbody.find('.mrt-new-route-station-row');
            var newIndex = currentStations.length;
            var safeName = (window.MRTAdminUtils && window.MRTAdminUtils.escapeHtml) ? window.MRTAdminUtils.escapeHtml(stationName) : stationName;

            var $row = $('<tr class="mrt-row-hover" data-station-id="' + stationId + '">' +
                '<td>' + newIndex + '</td>' +
                '<td>' + safeName + '</td>' +
                '<td>' +
                '<button type="button" class="button button-small mrt-move-route-station-up" data-station-id="' + stationId + '" title="' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.moveUp : 'Move up') + '">↑</button> ' +
                '<button type="button" class="button button-small mrt-move-route-station-down" data-station-id="' + stationId + '" title="' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.moveDown : 'Move down') + '">↓</button> ' +
                '<button type="button" class="button button-small mrt-remove-route-station" data-station-id="' + stationId + '">' + (typeof mrtAdmin !== 'undefined' ? mrtAdmin.remove : 'Remove') + '</button>' +
                '</td>' +
                '</tr>');

            $newRow.before($row);
            $select.val('').focus();
            updateRouteStationOrders();
        });
    }

    function bindMoveAndRemove($container) {
        $container.on('click', '.mrt-move-route-station-up', function() {
            var $row = $(this).closest('tr');
            var $prevRow = $row.prev();
            if ($prevRow.length && !$prevRow.hasClass('mrt-new-route-station-row')) {
                $row.insertBefore($prevRow);
                updateRouteStationOrders();
            }
        });

        $container.on('click', '.mrt-move-route-station-down', function() {
            var $row = $(this).closest('tr');
            var $nextRow = $row.next();
            if ($nextRow.length && !$nextRow.hasClass('mrt-new-route-station-row')) {
                $row.insertAfter($nextRow);
                updateRouteStationOrders();
            }
        });

        $container.on('click', '.mrt-remove-route-station', function() {
            var stationId = $(this).data('station-id');
            var currentStations = $('#mrt_route_stations').val().split(',').filter(function(id) {
                return id && id != stationId;
            });
            $('#mrt_route_stations').val(currentStations.join(','));
            $(this).closest('tr').remove();
            updateRouteStationOrders();
        });
    }

    function bindEndStationsChange() {
        var endStationsSaveTimeout;
        $('#mrt-route-start-station, #mrt-route-end-station').on('change', function() {
            var routeId = $('#post_ID').val();
            if (!routeId) return;

            clearTimeout(endStationsSaveTimeout);
            endStationsSaveTimeout = setTimeout(function() {
                var startStation = $('#mrt-route-start-station').val();
                var endStation = $('#mrt-route-end-station').val();
                var nonce = $('#mrt_route_meta_nonce').val();

                if (!nonce) return;

                $.ajax({
                    url: (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),
                    type: 'POST',
                    data: {
                        action: 'mrt_save_route_end_stations',
                        nonce: nonce,
                        route_id: routeId,
                        start_station: startStation || 0,
                        end_station: endStation || 0
                    },
                    success: function(response) {
                        if (response.success) {
                            var $indicator = $('<span class="mrt-save-indicator mrt-text-success mrt-ml-sm">✓ Saved</span>');
                            $('#mrt-route-end-station').closest('td').find('.mrt-save-indicator').remove();
                            $('#mrt-route-end-station').closest('td').append($indicator);
                            setTimeout(function() {
                                $indicator.fadeOut(300, function() { $(this).remove(); });
                            }, 2000);
                        }
                    },
                    error: function() {
                        var errMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.networkError) ? mrtAdmin.networkError : 'Network error. Please try again.';
                        var safeMsg = (window.MRTAdminUtils && window.MRTAdminUtils.escapeHtml) ? window.MRTAdminUtils.escapeHtml(errMsg) : errMsg;
                        var $err = $('<div class="mrt-alert mrt-alert-error mrt-mt-sm">' + safeMsg + '</div>');
                        $('#mrt-route-end-station').closest('td').find('.mrt-save-indicator').remove();
                        $('#mrt-route-end-station').closest('td').find('.mrt-alert').remove();
                        $('#mrt-route-end-station').closest('td').append($err);
                        setTimeout(function() { $err.fadeOut(300, function() { $(this).remove(); }); }, 3000);
                    }
                });
            }, 1000);
        });
    }

    function initRouteUI() {
        var $container = $('#mrt-route-stations-container');
        if (!$container.length) return;

        bindAddStation($container);
        bindMoveAndRemove($container);
        bindEndStationsChange();
    }

    $(function() {
        initRouteUI();
    });

})(jQuery);
