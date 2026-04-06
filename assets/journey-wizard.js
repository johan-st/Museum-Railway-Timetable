/**
 * Multi-step journey wizard for [museum_journey_wizard]
 *
 * @package Museum_Railway_Timetable
 */

(function($) {
    'use strict';

    var PRICE_TYPE_KEYS = ['single', 'return', 'day'];
    var PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'];

    function escapeHtml(s) {
        return $('<div>').text(s).html();
    }

    function formatDateYmd(ymd, cfg) {
        var p = ymd.split('-');
        if (p.length !== 3) {
            return ymd;
        }
        var y = p[0];
        var mo = parseInt(p[1], 10);
        var day = parseInt(p[2], 10);
        if (cfg.monthNames && cfg.monthNames[mo - 1]) {
            return cfg.monthNames[mo - 1] + ' ' + day + ', ' + y;
        }
        return ymd;
    }

    function arrivalAtDestination(conn) {
        return conn.to_arrival || conn.to_departure || '';
    }

    function initOne($root) {
        var cfg = typeof mrtJourneyWizard !== 'undefined' ? mrtJourneyWizard : null;
        if (!cfg) {
            return;
        }

        var startOfWeek = parseInt($root.data('startOfWeek'), 10);
        if (isNaN(startOfWeek) || startOfWeek < 0 || startOfWeek > 6) {
            startOfWeek = 1;
        }

        var state = {
            tripType: 'single',
            from: 0,
            to: 0,
            fromTitle: '',
            toTitle: '',
            date: '',
            calYear: 0,
            calMonth: 0,
            outbound: null,
            inbound: null
        };

        var lastOutboundList = [];
        var lastReturnList = [];

        var $err = $root.find('.mrt-journey-wizard__errors');
        var $stepsOl = $root.find('[data-wizard-steps]');

        function showError(msg) {
            $err.html('<div class="mrt-alert mrt-alert-error"></div>');
            $err.find('.mrt-alert').text(msg);
        }

        function clearError() {
            $err.empty();
        }

        function ajaxPost(action, data) {
            return $.post(cfg.ajaxurl, $.extend({
                action: action,
                nonce: cfg.nonce
            }, data), null, 'json');
        }

        function getStepSequence() {
            var seq = ['route', 'date', 'outbound'];
            if (state.tripType === 'return') {
                seq.push('return');
            }
            seq.push('summary');
            return seq;
        }

        function buildStepNav() {
            var seq = getStepSequence();
            var labels = {
                route: cfg.stepRoute,
                date: cfg.stepDate,
                outbound: cfg.stepOutbound,
                return: cfg.stepReturn,
                summary: cfg.stepSummary
            };
            $stepsOl.empty();
            seq.forEach(function(key, i) {
                var $li = $('<li></li>').text((i + 1) + '. ' + (labels[key] || key));
                $li.attr('data-wizard-indicator', key);
                $stepsOl.append($li);
            });
        }

        function updateStepNav(name) {
            var seq = getStepSequence();
            var idx = seq.indexOf(name);
            $stepsOl.find('li').each(function(i) {
                var $li = $(this);
                $li.toggleClass('is-active', i === idx);
                $li.toggleClass('is-done', i < idx);
                if (i === idx) {
                    $li.attr('aria-current', 'step');
                } else {
                    $li.removeAttr('aria-current');
                }
            });
        }

        function showPanel(name) {
            var $visible = null;
            $root.find('.mrt-journey-wizard__panel').each(function() {
                var $p = $(this);
                var step = $p.attr('data-wizard-step');
                if (step === name) {
                    $p.removeAttr('hidden').addClass('mrt-journey-wizard__panel--active');
                    $visible = $p;
                } else {
                    $p.attr('hidden', 'hidden').removeClass('mrt-journey-wizard__panel--active');
                }
            });
            updateStepNav(name);
            if ($visible && $visible.length) {
                var $h = $visible.find('h2, h3').first();
                if ($h.length) {
                    $h.attr('tabindex', '-1');
                    $h.trigger('focus');
                }
            }
        }

        function orderedWeekdayHeaders() {
            var abb = cfg.weekdayAbbrev.slice();
            var out = [];
            var i;
            for (i = 0; i < 7; i++) {
                out.push(abb[(startOfWeek + i) % 7]);
            }
            return out;
        }

        function renderCalendarGrid(year, month, daysMap) {
            var $cal = $root.find('[data-wizard-calendar]');
            $cal.empty();
            var title = (cfg.monthNames[month - 1] || month) + ' ' + year;
            $root.find('.mrt-journey-wizard__cal-title').text(title);

            var first = new Date(year, month - 1, 1);
            var lastDay = new Date(year, month, 0).getDate();
            var startCol = (first.getDay() - startOfWeek + 7) % 7;

            var $table = $('<table></table>')
                .attr('role', 'grid')
                .attr('aria-label', cfg.calendarGridLabel || '');
            var $thead = $('<tr></tr>');
            orderedWeekdayHeaders().forEach(function(ab) {
                $thead.append($('<th scope="col"></th>').text(ab));
            });
            $table.append($('<thead></thead>').append($thead));

            var $tb = $('<tbody></tbody>');
            var col = 0;
            var $row = $('<tr></tr>');
            var d;
            var pad;
            for (pad = 0; pad < startCol; pad++) {
                $row.append($('<td></td>'));
                col++;
            }
            for (d = 1; d <= lastDay; d++) {
                if (col >= 7) {
                    $tb.append($row);
                    $row = $('<tr></tr>');
                    col = 0;
                }
                var ymd = year + '-' + (month < 10 ? '0' : '') + month + '-' + (d < 10 ? '0' : '') + d;
                var st = daysMap[ymd] || 'none';
                var $td = $('<td></td>');
                var $btn = $('<button type="button" class="mrt-journey-wizard__day"></button>');
                $btn.text(String(d));
                if (st === 'ok') {
                    $btn.addClass('mrt-journey-wizard__day--ok');
                    $btn.attr('aria-pressed', state.date === ymd ? 'true' : 'false');
                    $btn.on('click', function() {
                        $cal.find('.mrt-journey-wizard__day--ok').each(function() {
                            $(this).attr('aria-pressed', 'false').removeClass('is-selected');
                        });
                        $btn.attr('aria-pressed', 'true').addClass('is-selected');
                        state.date = ymd;
                        clearError();
                        showPanel('outbound');
                        loadOutboundConnections();
                    });
                } else if (st === 'traffic_no_match') {
                    $btn.addClass('mrt-journey-wizard__day--traffic');
                    $btn.attr('disabled', 'disabled');
                } else {
                    $btn.addClass('mrt-journey-wizard__day--none');
                    $btn.attr('disabled', 'disabled');
                }
                if (state.date === ymd && st === 'ok') {
                    $btn.addClass('is-selected');
                }
                $td.append($btn);
                $row.append($td);
                col++;
            }
            while (col > 0 && col < 7) {
                $row.append($('<td></td>'));
                col++;
            }
            $tb.append($row);
            $table.append($tb);
            $cal.append($table);
        }

        function loadCalendar(year, month) {
            state.calYear = year;
            state.calMonth = month;
            $root.find('[data-wizard-calendar]').html('<p class="mrt-empty">' + escapeHtml(cfg.loading) + '</p>');
            ajaxPost('mrt_journey_calendar_month', {
                from_station: state.from,
                to_station: state.to,
                year: year,
                month: month
            }).done(function(res) {
                if (!res || !res.success || !res.data) {
                    showError(cfg.errorGeneric);
                    return;
                }
                renderCalendarGrid(res.data.year, res.data.month, res.data.days || {});
            }).fail(function() {
                showError(typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : cfg.errorGeneric);
            });
        }

        function renderConnectionTable($target, list, ctx, legFrom, legTo) {
            if (ctx === 'outbound') {
                lastOutboundList = list;
            } else {
                lastReturnList = list;
            }
            var $wrap = $('<div data-wizard-conn-context="' + escapeHtml(ctx) + '"></div>');
            var $table = $('<table class="mrt-table mrt-journey-table"></table>');
            var $thead = $('<thead><tr>' +
                '<th scope="col">' + escapeHtml(cfg.selectTrip) + '</th>' +
                '<th scope="col">' + escapeHtml(cfg.colService) + '</th>' +
                '<th scope="col">' + escapeHtml(cfg.colTrainType) + '</th>' +
                '<th scope="col">' + escapeHtml(cfg.colDeparture) + '</th>' +
                '<th scope="col">' + escapeHtml(cfg.colArrival) + '</th>' +
                '<th scope="col"></th>' +
                '</tr></thead>');
            $table.append($thead);
            var $tb = $('<tbody></tbody>');
            list.forEach(function(conn, idx) {
                var dep = conn.from_departure || conn.from_arrival || '—';
                var arr = conn.to_arrival || conn.to_departure || '—';
                var $tr = $('<tr></tr>');
                $tr.append($('<td></td>').append(
                    $('<button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__btn-select"></button>')
                        .text(cfg.selectTrip)
                        .attr('data-ctx', ctx)
                        .attr('data-idx', String(idx))
                ));
                $tr.append($('<td></td>').text(conn.service_name || ''));
                $tr.append($('<td></td>').text(conn.train_type || ''));
                $tr.append($('<td></td>').text(dep));
                $tr.append($('<td></td>').text(arr));
                var $act = $('<td class="mrt-journey-wizard__conn-actions"></td>');
                $act.append(
                    $('<button type="button" class="mrt-btn mrt-btn--secondary mrt-journey-wizard__btn-detail"></button>')
                        .text(cfg.showStops)
                        .attr('aria-expanded', 'false')
                        .attr('data-ctx', ctx)
                        .attr('data-idx', String(idx))
                        .attr('data-leg-from', String(legFrom))
                        .attr('data-leg-to', String(legTo))
                );
                $tr.append($act);
                $tb.append($tr);
            });
            $table.append($tb);
            $wrap.append($table);
            $target.empty().append($wrap);
        }

        function toggleDetailRow($btn) {
            var ctx = $btn.attr('data-ctx');
            var idx = parseInt($btn.attr('data-idx'), 10);
            var legFrom = parseInt($btn.attr('data-leg-from'), 10);
            var legTo = parseInt($btn.attr('data-leg-to'), 10);
            var list = ctx === 'return' ? lastReturnList : lastOutboundList;
            var conn = list[idx];
            if (!conn) {
                return;
            }
            var $tr = $btn.closest('tr');
            var $next = $tr.next('.mrt-journey-wizard__detail-row');
            if ($next.length) {
                $next.toggle();
                $btn.attr('aria-expanded', $next.is(':visible') ? 'true' : 'false');
                return;
            }
            var $detailTr = $('<tr class="mrt-journey-wizard__detail-row"></tr>');
            var $cell = $('<td colspan="6"></td>');
            $detailTr.append($cell);
            $cell.html('<p class="mrt-empty">' + escapeHtml(cfg.loading) + '</p>');
            $tr.after($detailTr);
            ajaxPost('mrt_journey_connection_detail', {
                from_station: legFrom,
                to_station: legTo,
                service_id: conn.service_id
            }).done(function(res) {
                if (!res || !res.success || !res.data) {
                    $cell.html('<div class="mrt-alert mrt-alert-error"></div>');
                    $cell.find('.mrt-alert').text(cfg.errorGeneric);
                    return;
                }
                var detail = res.data.detail || {};
                var notice = res.data.notice || '';
                var html = '<div class="mrt-journey-wizard__detail">';
                if (notice) {
                    html += '<p><strong>' + escapeHtml(cfg.noticeLabel) + ':</strong> ' + escapeHtml(notice) + '</p>';
                }
                if (detail.duration_minutes !== null && detail.duration_minutes !== undefined) {
                    html += '<p>' + escapeHtml(cfg.durationMinutes.replace('%d', String(detail.duration_minutes))) + '</p>';
                }
                html += '<table class="mrt-table"><thead><tr><th>' + escapeHtml(cfg.colStation) +
                    '</th><th>' + escapeHtml(cfg.colArrival) +
                    '</th><th>' + escapeHtml(cfg.colDeparture) + '</th></tr></thead><tbody>';
                (detail.stops || []).forEach(function(s) {
                    html += '<tr><td>' + escapeHtml(s.station_title || '') + '</td><td>' +
                        escapeHtml(s.arrival_time || '—') + '</td><td>' +
                        escapeHtml(s.departure_time || '—') + '</td></tr>';
                });
                html += '</tbody></table></div>';
                $cell.html(html);
                $btn.attr('aria-expanded', 'true');
            }).fail(function() {
                $cell.html('<div class="mrt-alert mrt-alert-error"></div>');
                $cell.find('.mrt-alert').text(cfg.errorGeneric);
                $btn.attr('aria-expanded', 'true');
            });
        }

        function loadOutboundConnections() {
            var $box = $root.find('[data-wizard-outbound]');
            $box.html('<p class="mrt-empty">' + escapeHtml(cfg.loading) + '</p>');
            ajaxPost('mrt_search_journey', {
                from_station: state.from,
                to_station: state.to,
                date: state.date,
                trip_type: 'single'
            }).done(function(res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : cfg.errorGeneric;
                    $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                    $box.find('.mrt-alert').text(msg);
                    return;
                }
                var conns = res.data.connections || [];
                if (!conns.length) {
                    $box.html('<div class="mrt-alert mrt-alert-info"><p>' + escapeHtml(cfg.noConnections) + '</p></div>');
                    return;
                }
                renderConnectionTable($box, conns, 'outbound', state.from, state.to);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : cfg.errorGeneric
                );
            });
        }

        function matrixHasAnyPrice(matrix) {
            if (!matrix) {
                return false;
            }
            var ti;
            var ci;
            for (ti = 0; ti < PRICE_TYPE_KEYS.length; ti++) {
                var row = matrix[PRICE_TYPE_KEYS[ti]];
                if (!row) {
                    continue;
                }
                for (ci = 0; ci < PRICE_CAT_KEYS.length; ci++) {
                    var v = row[PRICE_CAT_KEYS[ci]];
                    if (v !== null && v !== undefined && v !== '') {
                        return true;
                    }
                }
            }
            return false;
        }

        function formatPriceCell(v) {
            if (v === null || v === undefined || v === '') {
                return cfg.priceDash || '—';
            }
            return String(v);
        }

        function buildPriceSection(tripType) {
            if (!cfg.priceMatrix || !matrixHasAnyPrice(cfg.priceMatrix)) {
                return '';
            }
            var matrix = cfg.priceMatrix;
            var tickets = cfg.priceTickets || {};
            var cats = cfg.priceCategories || {};
            var activeType = tripType === 'return' ? 'return' : 'single';
            var html = '<div class="mrt-journey-wizard__prices mrt-mt-lg">';
            html += '<h4 class="mrt-heading mrt-heading--md">' + escapeHtml(cfg.priceTitle || '') + '</h4>';
            html += '<div class="mrt-journey-wizard__prices-scroll mrt-overflow-x-auto">';
            html += '<table class="mrt-table mrt-journey-wizard__price-table"><thead><tr><th scope="col"></th>';
            PRICE_CAT_KEYS.forEach(function(ck) {
                html += '<th scope="col">' + escapeHtml(cats[ck] || ck) + '</th>';
            });
            html += '</tr></thead><tbody>';
            PRICE_TYPE_KEYS.forEach(function(tk) {
                var row = matrix[tk] || {};
                var rowClass = tk === activeType ? ' class="mrt-journey-wizard__price-row--active"' : '';
                html += '<tr' + rowClass + '><th scope="row">' + escapeHtml(tickets[tk] || tk) + '</th>';
                PRICE_CAT_KEYS.forEach(function(ck) {
                    html += '<td>' + escapeHtml(formatPriceCell(row[ck])) + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            if (cfg.priceNote) {
                html += '<p class="mrt-text-secondary mrt-journey-wizard__price-note mrt-mt-sm">' +
                    escapeHtml(cfg.priceNote) + '</p>';
            }
            html += '</div>';
            return html;
        }

        function loadReturnConnections() {
            var arr = arrivalAtDestination(state.outbound);
            if (!arr) {
                showError(cfg.errorGeneric);
                return;
            }
            var $sum = $root.find('[data-wizard-return-summary]');
            var ob = state.outbound;
            var line = (ob.service_name || '') + ' · ' +
                (ob.from_departure || ob.from_arrival || '') + '–' +
                (ob.to_arrival || ob.to_departure || '') + ' · ' +
                formatDateYmd(state.date, cfg);
            $sum.text(line);

            var $box = $root.find('[data-wizard-return]');
            $box.html('<p class="mrt-empty">' + escapeHtml(cfg.loading) + '</p>');
            ajaxPost('mrt_search_journey', {
                from_station: state.from,
                to_station: state.to,
                date: state.date,
                trip_type: 'return',
                outbound_arrival: arr
            }).done(function(res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : cfg.errorGeneric;
                    $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                    $box.find('.mrt-alert').text(msg);
                    return;
                }
                var conns = res.data.connections || [];
                if (!conns.length) {
                    $box.html('<div class="mrt-alert mrt-alert-info"><p>' + escapeHtml(cfg.noConnections) + '</p></div>');
                    return;
                }
                renderConnectionTable($box, conns, 'return', state.to, state.from);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    typeof mrtFrontend !== 'undefined' ? mrtFrontend.networkError : cfg.errorGeneric
                );
            });
        }

        function renderSummary() {
            var $box = $root.find('[data-wizard-summary]');
            var parts = [];
            var ob = state.outbound;
            if (ob) {
                parts.push('<div class="mrt-box mrt-mb-sm"><strong>' + escapeHtml(cfg.outboundHeading) + '</strong><br>' +
                    escapeHtml(state.fromTitle) + ' → ' + escapeHtml(state.toTitle) + '<br>' +
                    escapeHtml(formatDateYmd(state.date, cfg)) + '<br>' +
                    escapeHtml(ob.service_name || '') + ' · ' +
                    escapeHtml(ob.from_departure || ob.from_arrival || '') + '–' +
                    escapeHtml(ob.to_arrival || ob.to_departure || '') +
                    '</div>');
            }
            if (state.tripType === 'return' && state.inbound) {
                var ib = state.inbound;
                parts.push('<div class="mrt-box"><strong>' + escapeHtml(cfg.returnHeading) + '</strong><br>' +
                    escapeHtml(state.toTitle) + ' → ' + escapeHtml(state.fromTitle) + '<br>' +
                    escapeHtml(formatDateYmd(state.date, cfg)) + '<br>' +
                    escapeHtml(ib.service_name || '') + ' · ' +
                    escapeHtml(ib.from_departure || ib.from_arrival || '') + '–' +
                    escapeHtml(ib.to_arrival || ib.to_departure || '') +
                    '</div>');
            }
            $box.html(parts.join('') + buildPriceSection(state.tripType));

            var url = $root.attr('data-ticket-url') || '';
            var $tw = $root.find('[data-wizard-ticket-wrap]');
            var $ta = $root.find('[data-wizard-ticket]');
            if (url) {
                $tw.removeAttr('hidden');
                $ta.attr('href', url);
            } else {
                $tw.attr('hidden', 'hidden');
                $ta.attr('href', '#');
            }
        }

        $root.on('click', '[data-wizard-next="route"]', function() {
            clearError();
            var from = parseInt($root.find('#mrt_wizard_from').val(), 10);
            var to = parseInt($root.find('#mrt_wizard_to').val(), 10);
            var trip = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
            if (!from || !to) {
                showError(cfg.pleaseStations);
                return;
            }
            if (from === to) {
                showError(typeof mrtFrontend !== 'undefined' ? mrtFrontend.errorSameStations : cfg.errorGeneric);
                return;
            }
            state.from = from;
            state.to = to;
            state.tripType = trip === 'return' ? 'return' : 'single';
            state.outbound = null;
            state.inbound = null;
            state.date = '';
            state.fromTitle = $root.find('#mrt_wizard_from option:selected').text();
            state.toTitle = $root.find('#mrt_wizard_to option:selected').text();
            buildStepNav();
            showPanel('date');
            var now = new Date();
            loadCalendar(now.getFullYear(), now.getMonth() + 1);
        });

        $root.on('click', '.mrt-journey-wizard__cal-prev', function() {
            var y = state.calYear;
            var m = state.calMonth - 1;
            if (m < 1) {
                m = 12;
                y -= 1;
            }
            loadCalendar(y, m);
        });

        $root.on('click', '.mrt-journey-wizard__cal-next', function() {
            var y = state.calYear;
            var m = state.calMonth + 1;
            if (m > 12) {
                m = 1;
                y += 1;
            }
            loadCalendar(y, m);
        });

        $root.on('click', '.mrt-journey-wizard__btn-detail', function() {
            toggleDetailRow($(this));
        });

        $root.on('click', '.mrt-journey-wizard__btn-select', function() {
            var ctx = $(this).attr('data-ctx');
            var idx = parseInt($(this).attr('data-idx'), 10);
            var list = ctx === 'return' ? lastReturnList : lastOutboundList;
            var conn = list[idx];
            if (!conn) {
                return;
            }
            if (ctx === 'outbound') {
                state.outbound = conn;
                state.inbound = null;
                if (state.tripType === 'return') {
                    showPanel('return');
                    loadReturnConnections();
                } else {
                    showPanel('summary');
                    renderSummary();
                }
            } else {
                state.inbound = conn;
                showPanel('summary');
                renderSummary();
            }
        });

        $root.on('click', '[data-wizard-back]', function() {
            var step = $(this).attr('data-wizard-back');
            clearError();
            if (step === 'date') {
                state.date = '';
                showPanel('route');
            } else if (step === 'outbound') {
                state.outbound = null;
                state.inbound = null;
                showPanel('date');
                loadCalendar(state.calYear, state.calMonth);
            } else if (step === 'return') {
                state.inbound = null;
                showPanel('outbound');
                loadOutboundConnections();
            } else if (step === 'summary') {
                if (state.tripType === 'return') {
                    showPanel('return');
                    loadReturnConnections();
                } else {
                    showPanel('outbound');
                    loadOutboundConnections();
                }
            }
        });

        state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
        $root.on('change', 'input[name="mrt_wizard_trip_type"]', function() {
            state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
        });

        buildStepNav();
        updateStepNav('route');
    }

    $(function() {
        $('.mrt-journey-wizard').each(function() {
            initOne($(this));
        });
    });
}(jQuery));
