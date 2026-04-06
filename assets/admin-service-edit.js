/**
 * Service edit: route change, destination, title preview, stoptimes form
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    window.MRTAdminServiceEdit = {
        init: function() {
            this.bindRouteChange();
            this.bindDestinationChange();
            this.bindTitleUserEdit();
            this.bindStopsHere();
            this.bindTimeValidation();
            this.bindSaveAllStoptimes();
            this.initStopsHereState();
        },

        bindRouteChange: function() {
            var self = this;
            var $stoptimesContainer = $('#mrt-stoptimes-container');
            var $destinationSelect = $('#mrt_service_end_station_id');
            var utils = window.MRTAdminUtils;

            $('#mrt_service_route_id').on('change', function() {
                var routeId = $(this).val();
                var serviceId = $('#post_ID').val() || 0;
                var nonce = $('#mrt_stoptimes_nonce').val();

                if (!routeId) {
                    self.handleNoRoute($stoptimesContainer, $destinationSelect);
                    return;
                }

                if ($stoptimesContainer.length) {
                    self.showLoadingStations($stoptimesContainer);
                }
                if ($destinationSelect.length) {
                    self.loadDestinations($destinationSelect, routeId, nonce, utils);
                }
                self.updateServiceTitlePreview(routeId, null);
                if ($stoptimesContainer.length && nonce) {
                    self.loadRouteStations($stoptimesContainer, routeId, serviceId, nonce);
                }
            });
        },

        handleNoRoute: function($stoptimesContainer, $destinationSelect) {
            var u = window.MRTAdminUtils;
            if ($stoptimesContainer.length) {
                $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-empty">' +
                    u.msg('noRouteSelected', 'No route selected. Select a route to configure stop times.') +
                    '</td></tr>');
                $('#mrt-save-all-stoptimes').closest('p').hide();
            }
            if ($destinationSelect.length) {
                var defOpt = document.createElement('option');
                defOpt.value = '';
                defOpt.textContent = u.msg('selectDestination', '— Select Destination —');
                $destinationSelect.empty().append(defOpt);
            }
        },

        showLoadingStations: function($stoptimesContainer) {
            $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-block mrt-text-center mrt-p-xl"><span class="spinner is-active mrt-spinner-inline"></span> ' +
                window.MRTAdminUtils.msg('loadingStations', 'Loading stations...') + '</td></tr>');
        },

        loadDestinations: function($destinationSelect, routeId, nonce, utils) {
            utils.setSelectState($destinationSelect, 'loading');
            $.ajax({
                url: utils.getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'mrt_get_route_destinations',
                    nonce: $('#mrt_service_meta_nonce').val() || $('#mrt_timetable_services_nonce').val() || nonce,
                    route_id: routeId
                },
                success: function(response) {
                    if (response.success && response.data.destinations) {
                        utils.populateDestinationsSelect($destinationSelect, response.data.destinations);
                    } else {
                        utils.setSelectState($destinationSelect, 'error');
                    }
                },
                error: function() {
                    utils.setSelectState($destinationSelect, 'error');
                }
            });
        },

        loadRouteStations: function($stoptimesContainer, routeId, serviceId, nonce) {
            var self = this;
            $.ajax({
                url: window.MRTAdminUtils.getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'mrt_get_route_stations_for_stoptimes',
                    nonce: nonce,
                    route_id: routeId,
                    service_id: serviceId
                },
                success: function(response) {
                    if (response.success && response.data.has_stations) {
                        self.renderStoptimesRows(response.data.stations, serviceId);
                        $('#mrt-save-all-stoptimes').closest('p').show();
                    } else {
                        $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-empty">' +
                            window.MRTAdminUtils.msg('noStationsOnRoute', 'No stations found on this route.') +
                            '</td></tr>');
                        $('#mrt-save-all-stoptimes').closest('p').hide();
                    }
                },
                error: function() {
                    $('#mrt-stoptimes-tbody').html('<tr><td colspan="7" class="mrt-alert mrt-alert-error">' +
                        window.MRTAdminUtils.msg('errorLoadingStations', 'Error loading stations. Please refresh the page.') +
                        '</td></tr>');
                }
            });
        },

        renderStoptimesRows: function(stations, serviceId) {
            var $tbody = $('#mrt-stoptimes-tbody');
            var utils = window.MRTAdminUtils;
            var timeHint = utils.msg('timeHint', 'Leave empty if train stops but time is not fixed');
            var pickupLabel = utils.msg('pickup', 'Pickup');
            var dropoffLabel = utils.msg('dropoff', 'Dropoff');
            $tbody.empty();
            stations.forEach(function(station, index) {
                var stopsHere = station.stops_here;
                var disabledAttr = stopsHere ? '' : 'disabled';
                var opacityClass = stopsHere ? '' : 'mrt-opacity-50';
                var safeName = utils.escapeHtml(station.name || '');
                var safeArrival = utils.escapeHtml(station.arrival_time || '');
                var safeDeparture = utils.escapeHtml(station.departure_time || '');
                var row = '<tr class="mrt-route-station-row" data-station-id="' + station.id + '" data-service-id="' + serviceId + '" data-sequence="' + station.sequence + '">' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td><strong>' + safeName + '</strong></td>' +
                    '<td><input type="checkbox" class="mrt-stops-here mrt-cursor-pointer" ' + (stopsHere ? 'checked' : '') + ' data-station-id="' + station.id + '" /></td>' +
                    '<td class="mrt-time-field mrt-relative ' + opacityClass + '">' +
                    '<input type="text" class="mrt-arrival-time mrt-input mrt-input--sm mrt-font-mono" value="' + safeArrival + '" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" ' + disabledAttr + ' />' +
                    '<p class="description mrt-text-xs mrt-text-tertiary">' + utils.escapeHtml(timeHint) + '</p></td>' +
                    '<td class="mrt-time-field mrt-relative ' + opacityClass + '">' +
                    '<input type="text" class="mrt-departure-time mrt-input mrt-input--sm mrt-font-mono" value="' + safeDeparture + '" placeholder="HH:MM" pattern="[0-2][0-9]:[0-5][0-9]" ' + disabledAttr + ' />' +
                    '<p class="description mrt-text-xs mrt-text-tertiary">' + utils.escapeHtml(timeHint) + '</p></td>' +
                    '<td class="mrt-option-field mrt-text-center ' + opacityClass + '">' +
                    '<label><input type="checkbox" class="mrt-pickup mrt-cursor-pointer" ' + (station.pickup_allowed ? 'checked' : '') + ' ' + disabledAttr + ' /> ' + utils.escapeHtml(pickupLabel) + '</label></td>' +
                    '<td class="mrt-option-field mrt-text-center ' + opacityClass + '">' +
                    '<label><input type="checkbox" class="mrt-dropoff mrt-cursor-pointer" ' + (station.dropoff_allowed ? 'checked' : '') + ' ' + disabledAttr + ' /> ' + utils.escapeHtml(dropoffLabel) + '</label></td>' +
                    '</tr>';
                $tbody.append(row);
            });
        },

        bindDestinationChange: function() {
            var self = this;
            $('#mrt_service_end_station_id').on('change', function() {
                var routeId = $('#mrt_service_route_id').val();
                var endStationId = $(this).val();
                self.updateServiceTitlePreview(routeId, endStationId);
            });
        },

        updateServiceTitlePreview: function(routeId, endStationId) {
            var $titleField = $('#title');
            if (!$titleField.length || !routeId) return;

            var routeName = $('#mrt_service_route_id option:selected').text();
            if (!routeName || routeName.indexOf('—') === 0) return;

            var newTitle = routeName;
            if (endStationId) {
                var destinationName = $('#mrt_service_end_station_id option:selected').text();
                if (destinationName && destinationName.indexOf('—') !== 0) {
                    destinationName = destinationName.replace(/\s*\(Start\)\s*$/i, '').replace(/\s*\(End\)\s*$/i, '');
                    newTitle = routeName + ' → ' + destinationName;
                }
            }
            if ($titleField.is(':visible') && !$titleField.data('user-edited')) {
                $titleField.val(newTitle);
            }
        },

        bindTitleUserEdit: function() {
            $('#title').on('input', function() {
                $(this).data('user-edited', true);
            });
        },

        bindStopsHere: function() {
            $(document).on('change', '.mrt-stops-here', function() {
                var $row = $(this).closest('tr');
                var stopsHere = $(this).is(':checked');
                $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
                $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
                if (!stopsHere) {
                    $row.find('.mrt-arrival-time, .mrt-departure-time').val('');
                }
            });
        },

        bindTimeValidation: function() {
            var utils = window.MRTAdminUtils;
            $(document).on('input blur', '.mrt-arrival-time, .mrt-departure-time', function() {
                var $input = $(this);
                var timeValue = $input.val();
                var $field = $input.closest('td');

                $input.removeClass('mrt-time-error');
                $field.find('.mrt-time-error-message').remove();

                if (timeValue && timeValue.trim() !== '' && !utils.validateTimeFormat(timeValue)) {
                    $input.addClass('mrt-time-error');
                    var errorText = utils.msg('invalidTimeFormat', 'Invalid format. Use HH:MM (e.g., 09:15)');
                    var $err = document.createElement('span');
                    $err.className = 'mrt-time-error-message mrt-block mrt-text-error mrt-text-small mrt-mt-xs';
                    $err.textContent = errorText;
                    $field[0].appendChild($err);
                }
            });
        },

        bindSaveAllStoptimes: function() {
            var self = this;
            $(document).on('click', '#mrt-save-all-stoptimes', function(e) {
                if (self.validateStoptimeFormats()) {
                    e.preventDefault();
                    return false;
                }
                self.sendSaveAllStoptimes($(this));
            });
        },

        validateStoptimeFormats: function() {
            var utils = window.MRTAdminUtils;
            var hasErrors = false;
            $('.mrt-arrival-time, .mrt-departure-time').each(function() {
                var $input = $(this);
                var timeValue = $input.val();
                if (timeValue && timeValue.trim() !== '' && !utils.validateTimeFormat(timeValue)) {
                    hasErrors = true;
                    $input.trigger('blur');
                }
            });
            if (hasErrors) {
                var errorMsg = utils.msg('fixTimeFormats', 'Please fix invalid time formats before saving. Use HH:MM format (e.g., 09:15).');
                alert(errorMsg);
                return true;
            }
            return false;
        },

        sendSaveAllStoptimes: function($btn) {
            if (!$btn.data('original-text')) $btn.data('original-text', $btn.text());
            var serviceId = $btn.data('service-id');
            var nonce = $('#mrt_stoptimes_nonce').val();
            var stops = [];
            $('#mrt-stoptimes-tbody .mrt-route-station-row').each(function() {
                var $row = $(this);
                stops.push({
                    station_id: $row.data('station-id'),
                    stops_here: $row.find('.mrt-stops-here').is(':checked') ? '1' : '0',
                    arrival: $row.find('.mrt-arrival-time').val(),
                    departure: $row.find('.mrt-departure-time').val(),
                    pickup: $row.find('.mrt-pickup').is(':checked') ? '1' : '0',
                    dropoff: $row.find('.mrt-dropoff').is(':checked') ? '1' : '0'
                });
            });
            var originalText = $btn.data('original-text') || $btn.text();
            var savingText = window.MRTAdminUtils.msg('saving', 'Saving...');
            $btn.prop('disabled', true).text(savingText).addClass('mrt-opacity-70 mrt-cursor-wait');

            $.post(window.MRTAdminUtils.getAjaxUrl(), {
                action: 'mrt_save_all_stoptimes',
                nonce: nonce,
                service_id: serviceId,
                stops: stops
            }, function(response) {
                var u = window.MRTAdminUtils;
                var msg = response.success
                    ? (response.data.message || u.msg('stopTimeSavedSuccessfully', 'Stop times saved successfully.'))
                    : (response.data.message || u.msg('errorSavingStopTime', 'Error saving stop times.'));
                var cssClass = response.success ? 'mrt-success-message notice notice-success' : 'mrt-error-message notice notice-error';
                var $msg = $('<div class="' + cssClass + ' is-dismissible mrt-my-1"><p></p></div>');
                $msg.find('p').text(msg);
                $btn.closest('.mrt-stoptimes-box').before($msg);
                setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
                $btn.prop('disabled', false).text(originalText).removeClass('mrt-opacity-70 mrt-cursor-wait');
            }).fail(function() {
                var networkErrorMsg = window.MRTAdminUtils.msg('networkError', 'Network error. Please try again.');
                var $errP = document.createElement('p');
                $errP.textContent = networkErrorMsg;
                var $msg = $('<div class="mrt-error-message notice notice-error is-dismissible"></div>').append($errP);
                $btn.closest('.mrt-stoptimes-box').before($msg);
                $btn.prop('disabled', false).text(originalText).removeClass('mrt-opacity-70 mrt-cursor-wait');
            });
        },

        initStopsHereState: function() {
            $('.mrt-stops-here').each(function() {
                var $row = $(this).closest('tr');
                var stopsHere = $(this).is(':checked');
                $row.find('.mrt-time-field input, .mrt-option-field input').prop('disabled', !stopsHere);
                $row.find('.mrt-time-field, .mrt-option-field').css('opacity', stopsHere ? '1' : '0.5');
            });
        }
    };

})(jQuery);
