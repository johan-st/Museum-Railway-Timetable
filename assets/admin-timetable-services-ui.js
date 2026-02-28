/**
 * Timetable Services UI: add/remove trips
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    function getAjaxUrl() {
        return (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
    }

    function getEmptyDestinationOptions() {
        var defOpt = document.createElement('option');
        defOpt.value = '';
        defOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectDestination) ? mrtAdmin.selectDestination : '— Select Destination —';
        var disOpt = document.createElement('option');
        disOpt.value = '';
        disOpt.disabled = true;
        disOpt.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.selectRouteFirst) ? mrtAdmin.selectRouteFirst : 'Select a route first';
        return [defOpt, disOpt];
    }

    function setDestinationSelectEmpty($select) {
        $select.empty().append(getEmptyDestinationOptions());
    }

    function showTemporaryNotice(message, type) {
        type = type || 'success';
        var cssClass = type === 'success' ? 'mrt-success-message notice notice-success' : 'mrt-error-message notice notice-error';
        var $msg = $('<div class="' + cssClass + ' is-dismissible mrt-my-1"><p></p></div>');
        $msg.find('p').text(message);
        $('#mrt-timetable-services-box').before($msg);
        setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
    }

    function buildNewServiceRow(response, timetableId) {
        var editUrlWithTimetable = response.data.edit_url + (response.data.edit_url.indexOf('?') > -1 ? '&' : '?') + 'timetable_id=' + timetableId;
        var $row = $('<tr></tr>').attr('data-service-id', response.data.service_id);
        var td1 = document.createElement('td');
        td1.textContent = response.data.route_name || '';
        var td2 = document.createElement('td');
        td2.textContent = response.data.train_type_name || '';
        var td3 = document.createElement('td');
        td3.textContent = response.data.destination || response.data.direction || '—';
        var td4 = document.createElement('td');
        var editLink = document.createElement('a');
        editLink.href = editUrlWithTimetable;
        editLink.className = 'button button-small';
        editLink.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.edit) ? mrtAdmin.edit : 'Edit';
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'button button-small mrt-delete-service-from-timetable';
        removeBtn.setAttribute('data-service-id', response.data.service_id);
        removeBtn.textContent = (typeof mrtAdmin !== 'undefined' && mrtAdmin.remove) ? mrtAdmin.remove : 'Remove';
        td4.appendChild(editLink);
        td4.appendChild(document.createTextNode(' '));
        td4.appendChild(removeBtn);
        $row.append(td1).append(td2).append(td3).append(td4);
        return $row;
    }

    function resetNewServiceForm() {
        $('#mrt-new-service-route').val('');
        $('#mrt-new-service-train-type').val('');
        setDestinationSelectEmpty($('#mrt-new-service-end-station'));
    }

    function bindRouteChange(nonce, utils) {
        $('#mrt-new-service-route').on('change', function() {
            var routeId = $(this).val();
            var $destinationSelect = $('#mrt-new-service-end-station');

            if (!routeId) {
                setDestinationSelectEmpty($destinationSelect);
                return;
            }

            utils.setSelectState($destinationSelect, 'loading');

            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'mrt_get_route_destinations',
                    nonce: nonce,
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
        });
    }

    function bindAddService(nonce) {
        $('#mrt-add-service-to-timetable').on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var timetableId = $btn.data('timetable-id');
            var routeId = $('#mrt-new-service-route').val();
            var trainTypeId = $('#mrt-new-service-train-type').val();
            var endStationId = $('#mrt-new-service-end-station').val();

            if (!routeId) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseSelectRoute : 'Please select a route.');
                return;
            }

            if (!nonce) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.securityTokenMissing : 'Security token missing. Please refresh the page.');
                return;
            }

            if (!$btn.data('original-text')) {
                $btn.data('original-text', $btn.text());
            }
            var addingText = (typeof mrtAdmin !== 'undefined' && mrtAdmin.adding) ? mrtAdmin.adding : 'Adding...';
            $btn.prop('disabled', true).text(addingText);

            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'mrt_add_service_to_timetable',
                    nonce: nonce,
                    timetable_id: timetableId,
                    route_id: routeId,
                    train_type_id: trainTypeId,
                    end_station_id: endStationId
                },
                success: function(response) {
                    if (response.success) {
                        var $tbody = $('#mrt-timetable-services-tbody');
                        var $newRow = $tbody.find('.mrt-new-service-row');
                        var $row = buildNewServiceRow(response, timetableId);
                        $newRow.before($row);

                        var tripAddedMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.tripAdded) ? mrtAdmin.tripAdded : 'Trip added successfully.';
                        showTemporaryNotice(tripAddedMsg, 'success');
                        resetNewServiceForm();
                    } else {
                        var errMsg = (response.data && response.data.message) ? String(response.data.message) : 'Error adding trip.';
                        showTemporaryNotice(errMsg, 'error');
                    }
                },
                error: function() {
                    var networkErrorMsg = typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.';
                    showTemporaryNotice(networkErrorMsg, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text($btn.data('original-text') || 'Add Trip');
                }
            });
        });
    }

    function bindRemoveService(nonce) {
        $('#mrt-timetable-services-container').on('click', '.mrt-delete-service-from-timetable', function() {
            if (!confirm(typeof mrtAdmin !== 'undefined' ? mrtAdmin.confirmRemoveTrip : 'Are you sure you want to remove this trip from the timetable?')) {
                return;
            }

            var $btn = $(this);
            var serviceId = $btn.data('service-id');
            var $row = $btn.closest('tr');

            $btn.prop('disabled', true);

            $.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                data: {
                    action: 'mrt_remove_service_from_timetable',
                    nonce: nonce,
                    service_id: serviceId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                        });
                        var tripRemovedMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.tripRemoved) ? mrtAdmin.tripRemoved : 'Trip removed successfully.';
                        showTemporaryNotice(tripRemovedMsg, 'success');
                    } else {
                        var errMsg = (response.data && response.data.message) ? String(response.data.message) : 'Error removing trip.';
                        showTemporaryNotice(errMsg, 'error');
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorRemovingTrip : 'Error removing trip.');
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    function initTimetableServicesUI() {
        var $container = $('#mrt-timetable-services-container');
        if (!$container.length) {
            if (window.mrtDebug) {
                console.log('MRT: Timetable services container not found');
            }
            return;
        }

        if (window.mrtDebug) {
            console.log('MRT: Initializing timetable services UI');
        }

        var nonce = $('#mrt_timetable_services_nonce').val();
        var utils = window.MRTAdminUtils;

        bindRouteChange(nonce, utils);
        bindAddService(nonce);
        bindRemoveService(nonce);
    }

    $(function() {
        initTimetableServicesUI();
    });

})(jQuery);
