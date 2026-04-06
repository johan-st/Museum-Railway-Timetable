/**
 * Multi-step journey wizard for [museum_journey_wizard]
 *
 * @package Museum_Railway_Timetable
 */

(function($) {
    'use strict';

    var PRICE_TYPE_KEYS = ['single', 'return', 'day'];
    var PRICE_CAT_KEYS = ['adult', 'child_4_15', 'child_0_3', 'student_senior'];
    var SU = window.MRTStringUtils;
    var FA = window.MRTFrontendApi;

    function arrivalAtDestination(conn) {
        return conn.to_arrival || conn.to_departure || conn.arrival || '';
    }

    function departureFromOrigin(conn) {
        return conn.from_departure || conn.from_arrival || conn.departure || '';
    }

    function mrtWizardMatrixHasAnyPrice(matrix) {
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

    function mrtWizardFormatPriceCell(v, cfg) {
        if (v === null || v === undefined || v === '') {
            return cfg.priceDash || '—';
        }
        return String(v);
    }

    function mrtWizardBuildPriceSection(tripType, cfg) {
        if (!cfg.priceMatrix || !mrtWizardMatrixHasAnyPrice(cfg.priceMatrix)) {
            return '';
        }
        var matrix = cfg.priceMatrix;
        var tickets = cfg.priceTickets || {};
        var cats = cfg.priceCategories || {};
        var activeType = tripType === 'return' ? 'return' : 'single';
        var html = '<div class="mrt-journey-wizard__prices mrt-mt-lg">';
        html += '<h4 class="mrt-heading mrt-heading--md">' + SU.escapeHtml(cfg.priceTitle || '') + '</h4>';
        html += '<div class="mrt-journey-wizard__prices-scroll mrt-overflow-x-auto">';
        html += '<table class="mrt-table mrt-journey-wizard__price-table"><thead><tr><th scope="col"><span class="mrt-sr-only">' +
            SU.escapeHtml(cfg.priceTableTypeColumn || '') + '</span></th>';
        PRICE_CAT_KEYS.forEach(function(ck) {
            html += '<th scope="col">' + SU.escapeHtml(cats[ck] || ck) + '</th>';
        });
        html += '</tr></thead><tbody>';
        PRICE_TYPE_KEYS.forEach(function(tk) {
            var row = matrix[tk] || {};
            var rowClass = tk === activeType ? ' class="mrt-journey-wizard__price-row--active"' : '';
            html += '<tr' + rowClass + '><th scope="row">' + SU.escapeHtml(tickets[tk] || tk) + '</th>';
            PRICE_CAT_KEYS.forEach(function(ck) {
                html += '<td>' + SU.escapeHtml(mrtWizardFormatPriceCell(row[ck], cfg)) + '</td>';
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        if (cfg.priceNote) {
            html += '<p class="mrt-text-secondary mrt-journey-wizard__price-note mrt-mt-sm">' +
                SU.escapeHtml(cfg.priceNote) + '</p>';
        }
        html += '</div>';
        return html;
    }

    function mrtWizardDayButtonAriaLabel(ymd, st, cfg) {
        var human = window.MRTDateUtils.formatYmdForDisplay(ymd, cfg);
        if (st === 'ok') {
            return (cfg.dayDateOk || human).replace('%s', human);
        }
        if (st === 'traffic_no_match') {
            return (cfg.dayDateTraffic || human).replace('%s', human);
        }
        return (cfg.dayDateNone || human).replace('%s', human);
    }

    function mrtWizardOrderedWeekdayHeaders(cfg, startOfWeek) {
        var abb = cfg.weekdayAbbrev.slice();
        var out = [];
        var i;
        for (i = 0; i < 7; i++) {
            out.push(abb[(startOfWeek + i) % 7]);
        }
        return out;
    }

    function mrtWizardCalendarDayButton(ymd, dayNum, st, cfg, selectedDateYmd, $cal, onSelectOkDay) {
        var $btn = $('<button type="button" class="mrt-journey-wizard__day"></button>');
        $btn.text(String(dayNum));
        $btn.attr('aria-label', mrtWizardDayButtonAriaLabel(ymd, st, cfg));
        if (st === 'ok') {
            $btn.addClass('mrt-journey-wizard__day--ok');
            $btn.attr('aria-pressed', selectedDateYmd === ymd ? 'true' : 'false');
            $btn.on('click', function() {
                $cal.find('.mrt-journey-wizard__day--ok').each(function() {
                    $(this).attr('aria-pressed', 'false').removeClass('is-selected');
                });
                $btn.attr('aria-pressed', 'true').addClass('is-selected');
                onSelectOkDay(ymd);
            });
        } else if (st === 'traffic_no_match') {
            $btn.addClass('mrt-journey-wizard__day--traffic');
            $btn.attr('disabled', 'disabled');
        } else {
            $btn.addClass('mrt-journey-wizard__day--none');
            $btn.attr('disabled', 'disabled');
        }
        if (selectedDateYmd === ymd && st === 'ok') {
            $btn.addClass('is-selected');
        }
        return $btn;
    }

    function mrtWizardRenderCalendarGrid($root, year, month, daysMap, cfg, startOfWeek, selectedDateYmd, onSelectOkDay) {
        var DU = window.MRTDateUtils;
        var $cal = $root.find('[data-wizard-calendar]');
        $cal.empty();
        $root.find('.mrt-journey-wizard__cal-title').text(DU.calendarMonthTitle(year, month, cfg.monthNames));

        var lastDay = DU.daysInMonth(year, month);
        var startCol = DU.monthStartColumn(year, month, startOfWeek);

        var $table = $('<table></table>')
            .attr('role', 'grid')
            .attr('aria-label', cfg.calendarGridLabel || '');
        var $thead = $('<tr></tr>');
        mrtWizardOrderedWeekdayHeaders(cfg, startOfWeek).forEach(function(ab) {
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
            var ymd = DU.ymdFromParts(year, month, d);
            var st = daysMap[ymd] || 'none';
            var $td = $('<td></td>');
            $td.append(mrtWizardCalendarDayButton(ymd, d, st, cfg, selectedDateYmd, $cal, onSelectOkDay));
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

    function mrtWizardAriaChooseTrip(conn, dep, arr, cfg) {
        var s = cfg.btnChooseTripAria || '';
        return s
            .replace('%1$s', conn.service_name || '')
            .replace('%2$s', dep)
            .replace('%3$s', arr);
    }

    function mrtWizardConnectionTableRow(conn, idx, ctx, legFrom, legTo, cfg) {
        var dep = departureFromOrigin(conn) || '—';
        var arr = arrivalAtDestination(conn) || '—';
        var $tr = $('<tr></tr>');
        $tr.append($('<td></td>').append(
            $('<button type="button" class="mrt-btn mrt-btn--primary mrt-journey-wizard__btn-select"></button>')
                .text(cfg.selectTrip)
                .attr('aria-label', mrtWizardAriaChooseTrip(conn, dep, arr, cfg))
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
                .attr('aria-label', (cfg.btnShowStopsAria || '').replace('%s', conn.service_name || ''))
                .attr('aria-expanded', 'false')
                .attr('data-ctx', ctx)
                .attr('data-idx', String(idx))
                .attr('data-leg-from', String(legFrom))
                .attr('data-leg-to', String(legTo))
        );
        $tr.append($act);
        return $tr;
    }

    function mrtWizardRenderConnectionTable($target, list, ctx, legFrom, legTo, cfg, outboundDateCaption) {
        var $wrap = $('<div data-wizard-conn-context="' + SU.escapeHtml(ctx) + '"></div>');
        var captionText = ctx === 'return'
            ? (cfg.tripsCaptionReturn || '')
            : (cfg.tripsCaptionOutbound || '').replace('%s', outboundDateCaption);
        var $table = $('<table class="mrt-table mrt-journey-table"></table>');
        if (captionText) {
            $table.append($('<caption class="mrt-journey-wizard__table-caption"></caption>').text(captionText));
        }
        var actionsTh = '<th scope="col"><span class="mrt-sr-only">' + SU.escapeHtml(cfg.colActions || '') + '</span></th>';
        var $thead = $('<thead><tr>' +
            '<th scope="col">' + SU.escapeHtml(cfg.selectTrip) + '</th>' +
            '<th scope="col">' + SU.escapeHtml(cfg.colService) + '</th>' +
            '<th scope="col">' + SU.escapeHtml(cfg.colTrainType) + '</th>' +
            '<th scope="col">' + SU.escapeHtml(cfg.colDeparture) + '</th>' +
            '<th scope="col">' + SU.escapeHtml(cfg.colArrival) + '</th>' +
            actionsTh +
            '</tr></thead>');
        $table.append($thead);
        var $tb = $('<tbody></tbody>');
        list.forEach(function(conn, idx) {
            $tb.append(mrtWizardConnectionTableRow(conn, idx, ctx, legFrom, legTo, cfg));
        });
        $table.append($tb);
        $wrap.append($table);
        $target.empty().append($wrap);
    }

    function mrtWizardBuildStopsDetailHtml(detail, notice, cfg) {
        var html = '';
        if (notice) {
            html += '<p><strong>' + SU.escapeHtml(cfg.noticeLabel) + ':</strong> ' + SU.escapeHtml(notice) + '</p>';
        }
        if (detail.duration_minutes !== null && detail.duration_minutes !== undefined) {
            html += '<p>' + SU.escapeHtml(cfg.durationMinutes.replace('%d', String(detail.duration_minutes))) + '</p>';
        }
        html += '<table class="mrt-table"><thead><tr><th scope="col">' + SU.escapeHtml(cfg.colStation) +
            '</th><th scope="col">' + SU.escapeHtml(cfg.colArrival) +
            '</th><th scope="col">' + SU.escapeHtml(cfg.colDeparture) + '</th></tr></thead><tbody>';
        (detail.stops || []).forEach(function(s) {
            html += '<tr><td>' + SU.escapeHtml(s.station_title || '') + '</td><td>' +
                SU.escapeHtml(s.arrival_time || '—') + '</td><td>' +
                SU.escapeHtml(s.departure_time || '—') + '</td></tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    function mrtWizardLoadMultiLegDetailRows(conn, $cell, $btn, cfg, ajaxPost) {
        var legTpl = cfg.legSegmentLabel || 'Train %d';
        var multiHtml = '<div class="mrt-journey-wizard__detail mrt-journey-wizard__detail--multi">';
        var legIndex = 0;
        function loadNextLeg() {
            if (legIndex >= conn.legs.length) {
                multiHtml += '</div>';
                $cell.html(multiHtml);
                $btn.attr('aria-expanded', 'true');
                return;
            }
            var leg = conn.legs[legIndex];
            var title = legTpl.replace('%d', String(legIndex + 1));
            ajaxPost('mrt_journey_connection_detail', {
                from_station: leg.from_station_id,
                to_station: leg.to_station_id,
                service_id: leg.service_id
            }).done(function(res) {
                if (!res || !res.success || !res.data) {
                    $cell.html('<div class="mrt-alert mrt-alert-error"></div>');
                    $cell.find('.mrt-alert').text(cfg.errorGeneric);
                    $btn.attr('aria-expanded', 'true');
                    return;
                }
                var detail = res.data.detail || {};
                var notice = res.data.notice || '';
                multiHtml += '<div class="mrt-journey-wizard__detail-segment mrt-mb-sm">';
                multiHtml += '<h4 class="mrt-heading mrt-heading--sm">' + SU.escapeHtml(title) + '</h4>';
                multiHtml += mrtWizardBuildStopsDetailHtml(detail, notice, cfg);
                multiHtml += '</div>';
                legIndex += 1;
                loadNextLeg();
            }).fail(function() {
                $cell.html('<div class="mrt-alert mrt-alert-error"></div>');
                $cell.find('.mrt-alert').text(cfg.errorGeneric);
                $btn.attr('aria-expanded', 'true');
            });
        }
        loadNextLeg();
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
            return FA.post(action, data, {
                ajaxurl: cfg.ajaxurl,
                nonce: cfg.nonce
            });
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
                    $h.one('blur', function() {
                        $h.removeAttr('tabindex');
                    });
                }
            }
        }

        function renderCalendarGrid(year, month, daysMap) {
            mrtWizardRenderCalendarGrid($root, year, month, daysMap, cfg, startOfWeek, state.date, function(ymd) {
                state.date = ymd;
                clearError();
                showPanel('outbound');
                loadOutboundConnections();
            });
        }

        function loadCalendar(year, month) {
            state.calYear = year;
            state.calMonth = month;
            var $calHost = $root.find('[data-wizard-calendar]');
            $calHost.attr('aria-busy', 'true');
            $calHost.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
            ajaxPost('mrt_journey_calendar_month', {
                from_station: state.from,
                to_station: state.to,
                year: year,
                month: month
            }).done(function(res) {
                if (!res || !res.success || !res.data) {
                    $calHost.attr('aria-busy', 'false');
                    showError(cfg.errorGeneric);
                    return;
                }
                renderCalendarGrid(res.data.year, res.data.month, res.data.days || {});
                $calHost.attr('aria-busy', 'false');
            }).fail(function() {
                $calHost.attr('aria-busy', 'false');
                showError(FA.msg('networkError', cfg.errorGeneric));
            });
        }

        function renderConnectionTable($target, list, ctx, legFrom, legTo) {
            if (ctx === 'outbound') {
                lastOutboundList = list;
            } else {
                lastReturnList = list;
            }
            mrtWizardRenderConnectionTable(
                $target,
                list,
                ctx,
                legFrom,
                legTo,
                cfg,
                window.MRTDateUtils.formatYmdForDisplay(state.date, cfg)
            );
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
            $cell.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
            $tr.after($detailTr);

            if (conn.legs && conn.legs.length > 1) {
                mrtWizardLoadMultiLegDetailRows(conn, $cell, $btn, cfg, ajaxPost);
                return;
            }

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
                var html = '<div class="mrt-journey-wizard__detail">' + mrtWizardBuildStopsDetailHtml(detail, notice, cfg) + '</div>';
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
            $box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
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
                    $box.html('<div class="mrt-alert mrt-alert-info"><p>' + SU.escapeHtml(cfg.noConnections) + '</p></div>');
                    return;
                }
                renderConnectionTable($box, conns, 'outbound', state.from, state.to);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    FA.msg('networkError', cfg.errorGeneric)
                );
            });
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
                departureFromOrigin(ob) + '–' +
                arrivalAtDestination(ob) + ' · ' +
                window.MRTDateUtils.formatYmdForDisplay(state.date, cfg);
            $sum.text(line);

            var $box = $root.find('[data-wizard-return]');
            $box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
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
                    $box.html('<div class="mrt-alert mrt-alert-info"><p>' + SU.escapeHtml(cfg.noConnections) + '</p></div>');
                    return;
                }
                renderConnectionTable($box, conns, 'return', state.to, state.from);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    FA.msg('networkError', cfg.errorGeneric)
                );
            });
        }

        function renderSummary() {
            var $box = $root.find('[data-wizard-summary]');
            var parts = [];
            var ob = state.outbound;
            if (ob) {
                parts.push('<div class="mrt-box mrt-mb-sm"><strong>' + SU.escapeHtml(cfg.outboundHeading) + '</strong><br>' +
                    SU.escapeHtml(state.fromTitle) + ' → ' + SU.escapeHtml(state.toTitle) + '<br>' +
                    SU.escapeHtml(window.MRTDateUtils.formatYmdForDisplay(state.date, cfg)) + '<br>' +
                    SU.escapeHtml(ob.service_name || '') + ' · ' +
                    SU.escapeHtml(departureFromOrigin(ob)) + '–' +
                    SU.escapeHtml(arrivalAtDestination(ob)) +
                    '</div>');
            }
            if (state.tripType === 'return' && state.inbound) {
                var ib = state.inbound;
                parts.push('<div class="mrt-box"><strong>' + SU.escapeHtml(cfg.returnHeading) + '</strong><br>' +
                    SU.escapeHtml(state.toTitle) + ' → ' + SU.escapeHtml(state.fromTitle) + '<br>' +
                    SU.escapeHtml(window.MRTDateUtils.formatYmdForDisplay(state.date, cfg)) + '<br>' +
                    SU.escapeHtml(ib.service_name || '') + ' · ' +
                    SU.escapeHtml(departureFromOrigin(ib)) + '–' +
                    SU.escapeHtml(arrivalAtDestination(ib)) +
                    '</div>');
            }
            $box.html(parts.join('') + mrtWizardBuildPriceSection(state.tripType, cfg));

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
                showError(FA.msg('errorSameStations', cfg.errorGeneric));
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
            var cm0 = window.MRTDateUtils.currentCalendarYearMonth();
            loadCalendar(cm0.year, cm0.month);
        });

        $root.on('click', '.mrt-journey-wizard__cal-prev', function() {
            var cm = window.MRTDateUtils.addCalendarMonths(state.calYear, state.calMonth, -1);
            loadCalendar(cm.year, cm.month);
        });

        $root.on('click', '.mrt-journey-wizard__cal-next', function() {
            var cm = window.MRTDateUtils.addCalendarMonths(state.calYear, state.calMonth, 1);
            loadCalendar(cm.year, cm.month);
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
