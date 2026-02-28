/**
 * Stop Times UI: legacy inline row editing
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    function startEditStopTime($row, state) {
        state.editingRow = $row;
        $row.addClass('mrt-editing');
        $row.find('.mrt-display').hide();
        $row.find('.mrt-input').show();
        $row.find('.mrt-save-stoptime, .mrt-cancel-edit').show();
        $row.find('.mrt-delete-stoptime').hide();
    }

    function exitEditMode($row, state) {
        $row.removeClass('mrt-editing');
        $row.find('.mrt-display').show();
        $row.find('.mrt-input').hide();
        $row.find('.mrt-save-stoptime, .mrt-cancel-edit').hide();
        $row.find('.mrt-delete-stoptime').show();
        state.editingRow = null;
    }

    function applyStoptimeToRow($row, st) {
        $row.find('[data-field="arrival"] input').val(st.arrival_time || '');
        $row.find('[data-field="departure"] input').val(st.departure_time || '');
        $row.find('.mrt-arrival-time').val(st.arrival_time || '');
        $row.find('.mrt-departure-time').val(st.departure_time || '');
        $row.find('[data-field="pickup"] input[type="checkbox"]').prop('checked', st.pickup_allowed == 1);
        $row.find('[data-field="dropoff"] input[type="checkbox"]').prop('checked', st.dropoff_allowed == 1);
        $row.find('.mrt-pickup').prop('checked', st.pickup_allowed == 1);
        $row.find('.mrt-dropoff').prop('checked', st.dropoff_allowed == 1);
    }

    function showStoptimeSuccess($container, message) {
        var $msg = $('<div class="mrt-success-message notice notice-success is-dismissible mrt-my-1"><p></p></div>');
        $msg.find('p').text(message);
        $container.before($msg);
        setTimeout(function() { $msg.fadeOut(300, function() { $(this).remove(); }); }, 3000);
    }

    function bindRowClick($container, state) {
        $container.on('click', '.mrt-stoptime-row:not(.mrt-new-row)', function(e) {
            if ($(e.target).is('button, input, select') || $(e.target).closest('button, input, select').length) {
                return;
            }
            if (state.editingRow && state.editingRow[0] !== this) {
                cancelEditStopTime(state.editingRow, state);
            }
            startEditStopTime($(this), state);
        });
    }

    function bindSave($container, nonce, state) {
        $container.on('click', '.mrt-save-stoptime', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-stoptime-row');
            var id = $row.data('stoptime-id');
            var serviceId = $row.data('service-id');

            var $stationField = $row.find('[data-field="station"]');
            var stationId = $stationField.find('select').length ? $stationField.find('select').val() : $stationField.find('input').val();

            var data = {
                action: id === 'new' ? 'mrt_add_stoptime' : 'mrt_update_stoptime',
                nonce: nonce,
                service_id: serviceId,
                station_id: stationId,
                sequence: $row.find('[data-field="sequence"] input').val(),
                arrival: $row.find('[data-field="arrival"] input').val(),
                departure: $row.find('[data-field="departure"] input').val(),
                pickup: $row.find('[data-field="pickup"] input[type="checkbox"]').is(':checked') ? 1 : 0,
                dropoff: $row.find('[data-field="dropoff"] input[type="checkbox"]').is(':checked') ? 1 : 0
            };

            if (id !== 'new') {
                data.id = id;
            }

            if (!data.station_id || !data.sequence) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseFillStationAndSequence : 'Please fill in Station and Sequence.');
                return;
            }

            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text((typeof mrtAdmin !== 'undefined' && mrtAdmin.saving) ? mrtAdmin.saving : 'Saving...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    if (response.data) {
                        applyStoptimeToRow($row, response.data);
                    }
                    exitEditMode($row, state);
                    var successMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.stopTimeSavedSuccessfully) ? mrtAdmin.stopTimeSavedSuccessfully : 'Stop time saved successfully.';
                    showStoptimeSuccess($container, successMsg);
                } else {
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorSavingStopTime : 'Error saving stop time.'));
                    $btn.prop('disabled', false).text(originalText);
                }
            }).fail(function() {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            });
        });
    }

    function bindAdd($container, nonce, state) {
        $container.on('click', '.mrt-add-stoptime', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('.mrt-stoptime-row');
            var serviceId = $row.data('service-id');

            var data = {
                action: 'mrt_add_stoptime',
                nonce: nonce,
                service_id: serviceId,
                station_id: $row.find('[data-field="station"] select').val(),
                sequence: $row.find('[data-field="sequence"] input').val(),
                arrival: $row.find('[data-field="arrival"] input').val(),
                departure: $row.find('[data-field="departure"] input').val(),
                pickup: $row.find('[data-field="pickup"] input[type="checkbox"]').is(':checked') ? 1 : 0,
                dropoff: $row.find('[data-field="dropoff"] input[type="checkbox"]').is(':checked') ? 1 : 0
            };

            if (!data.station_id || !data.sequence) {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.pleaseFillStationAndSequence : 'Please fill in Station and Sequence.');
                return;
            }

            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text((typeof mrtAdmin !== 'undefined' && mrtAdmin.adding) ? mrtAdmin.adding : 'Adding...');

            $.post(mrtAdmin.ajaxurl, data, function(response) {
                if (response.success) {
                    if (response.data) {
                        var st = response.data;
                        $row.data('stoptime-id', st.id);
                        $row.data('id', st.id);
                        $row.removeClass('mrt-new-row');
                        applyStoptimeToRow($row, st);
                        exitEditMode($row, state);
                        var successMsg = (typeof mrtAdmin !== 'undefined' && mrtAdmin.stopTimeAddedSuccessfully) ? mrtAdmin.stopTimeAddedSuccessfully : 'Stop time added successfully.';
                        showStoptimeSuccess($container, successMsg);
                    }
                } else {
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorAddingStopTime : 'Error adding stop time.'));
                    $btn.prop('disabled', false).text(originalText);
                }
            }).fail(function() {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            });
        });
    }

    function cancelEditStopTime($row, state) {
        if (!$row.length) return;
        var stoptimeId = $row.data('stoptime-id');
        var id = $row.data('id');
        var nonce = $('#mrt_stoptimes_nonce').val();

        if (stoptimeId && stoptimeId !== 'new' && id) {
            $.post(mrtAdmin.ajaxurl, { action: 'mrt_get_stoptime', nonce: nonce, id: id }, function(response) {
                if (response.success && response.data) {
                    applyStoptimeToRow($row, response.data);
                }
                exitEditMode($row, state);
            });
        } else {
            exitEditMode($row, state);
        }
    }

    function bindCancel($container, state) {
        $container.on('click', '.mrt-cancel-edit', function(e) {
            e.stopPropagation();
            cancelEditStopTime($(this).closest('.mrt-stoptime-row'), state);
        });
    }

    function bindDelete($container, nonce) {
        $container.on('click', '.mrt-delete-stoptime', function(e) {
            e.stopPropagation();
            if (!confirm(typeof mrtAdmin !== 'undefined' ? mrtAdmin.confirmDeleteStopTime : 'Are you sure you want to delete this stop time?')) {
                return;
            }
            var id = $(this).data('id');
            $.post(mrtAdmin.ajaxurl, {
                action: 'mrt_delete_stoptime',
                nonce: nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || (typeof mrtAdmin !== 'undefined' ? mrtAdmin.errorDeletingStopTime : 'Error deleting stop time.'));
                }
            }).fail(function() {
                alert(typeof mrtAdmin !== 'undefined' ? mrtAdmin.networkError : 'Network error. Please try again.');
            });
        });
    }

    function initStopTimesUI() {
        var $container = $('#mrt-stoptimes-container');
        if (!$container.length) return;

        var nonce = $('#mrt_stoptimes_nonce').val();
        var state = { editingRow: null };

        bindRowClick($container, state);
        bindSave($container, nonce, state);
        bindAdd($container, nonce, state);
        bindCancel($container, state);
        bindDelete($container, nonce);
    }

    $(function() {
        initStopTimesUI();
    });

})(jQuery);
