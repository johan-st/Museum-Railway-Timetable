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

    function mrtWizardPriceZonesForStationPair(fromId, toId, cfg) {
        var map = cfg.priceStationZones || {};
        var fromZones = map[String(fromId)] || map[fromId] || [];
        var toZones = map[String(toId)] || map[toId] || [];
        var best = 4;
        if (!fromZones.length || !toZones.length) {
            return best;
        }
        fromZones.forEach(function(fz) {
            toZones.forEach(function(tz) {
                var span = Math.abs(parseInt(tz, 10) - parseInt(fz, 10)) + 1;
                if (!isNaN(span)) {
                    best = Math.min(best, span);
                }
            });
        });
        return Math.max(1, Math.min(4, best));
    }

    function mrtWizardPriceMatrixForZone(cfg, zones) {
        var byZone = cfg.priceMatrixByZone || null;
        var zoneKey = String(Math.max(1, Math.min(4, parseInt(zones, 10) || 4)));
        var out = {};
        if (!byZone) {
            return cfg.priceMatrix || {};
        }
        PRICE_TYPE_KEYS.forEach(function(tk) {
            out[tk] = {};
            PRICE_CAT_KEYS.forEach(function(ck) {
                out[tk][ck] = byZone[tk] && byZone[tk][ck] ? byZone[tk][ck][zoneKey] : null;
            });
        });
        return out;
    }

    function mrtWizardBuildPriceSection(tripType, cfg, zones) {
        var matrix = mrtWizardPriceMatrixForZone(cfg, zones);
        if (!matrix || !mrtWizardMatrixHasAnyPrice(matrix)) {
            return '';
        }
        var tickets = cfg.priceTickets || {};
        var cats = cfg.priceCategories || {};
        var activeType = tripType === 'return' ? 'return' : 'single';
        var zoneText = (cfg.priceZoneLabel || '%d zones').replace('%d', String(zones || 4));
        var html = '<div class="mrt-journey-wizard__prices mrt-mt-lg">';
        html += '<h4 class="mrt-heading mrt-heading--md">' + SU.escapeHtml(cfg.priceTitle || '') + ' <span>(' +
            SU.escapeHtml(zoneText) + ')</span></h4>';
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

    function mrtWizardFormatDuration(minutes, cfg) {
        var m = parseInt(minutes, 10);
        if (isNaN(m) || m < 0) {
            return '';
        }
        if (m >= 60) {
            var h = Math.floor(m / 60);
            var rest = m % 60;
            return rest ? (h + ' tim ' + rest + ' min') : (h + ' tim');
        }
        return (cfg.durationMinutes || '%d min').replace('%d', String(m));
    }

    function mrtWizardTripTypeText(state, cfg) {
        return state.tripType === 'return' ? (cfg.tripTypeReturn || 'Tur- och retur') : (cfg.tripTypeSingle || 'Enkel');
    }

    function mrtWizardContextLine(state, cfg, includeDate) {
        var routeTpl = includeDate ? (cfg.routeDateContext || '%1$s → %2$s | %3$s\n%4$s') : (cfg.routeContext || '%1$s → %2$s | %3$s');
        var out = routeTpl
            .replace('%1$s', state.fromTitle || '')
            .replace('%2$s', state.toTitle || '')
            .replace('%3$s', mrtWizardTripTypeText(state, cfg));
        if (includeDate) {
            out = out.replace('%4$s', window.MRTDateUtils.formatYmdForDisplay(state.date, cfg));
        }
        return out;
    }

    function mrtWizardUpdateContext($root, state, cfg) {
        $root.find('[data-wizard-context]').each(function() {
            $(this).text(mrtWizardContextLine(state, cfg, Boolean(state.date)));
        });
    }

    function mrtWizardVehicleClass(label) {
        var s = String(label || '').toLowerCase();
        if (s.indexOf('räls') !== -1 || s.indexOf('rail') !== -1 || s.indexOf('buss') !== -1) {
            return 'railbus';
        }
        if (s.indexOf('diesel') !== -1) {
            return 'diesel';
        }
        if (s.indexOf('ång') !== -1 || s.indexOf('steam') !== -1) {
            return 'steam';
        }
        return 'train';
    }

    function mrtWizardVehicleBadge(label, serviceName) {
        var text = label || serviceName || '';
        var kind = mrtWizardVehicleClass(text);
        return '<span class="mrt-journey-wizard__vehicle mrt-journey-wizard__vehicle--' + kind + '">' +
            '<span class="mrt-journey-wizard__vehicle-mark" aria-hidden="true"></span>' +
            '<span>' + SU.escapeHtml(text || 'Tåg') + '</span>' +
            '</span>';
    }

    function mrtWizardLegVehicleBadge(leg) {
        var service = leg.service_name || (leg.service_id ? String(leg.service_id) : '');
        var train = leg.train_type || '';
        var label = train && service ? (train + ' ' + service) : (train || service);
        return mrtWizardVehicleBadge(label, service);
    }

    function mrtWizardConnectionLegs(conn) {
        if (conn.legs && conn.legs.length) {
            return conn.legs;
        }
        return [{
            service_id: conn.service_id,
            service_name: conn.service_name,
            train_type: conn.train_type,
            from_station_id: conn.from_station_id,
            to_station_id: conn.to_station_id,
            from_departure: departureFromOrigin(conn),
            to_arrival: arrivalAtDestination(conn),
            destination: conn.destination,
            direction: conn.direction
        }];
    }

    function mrtWizardConnectionMeta(conn, cfg) {
        if (conn.connection_type === 'transfer' || (conn.legs && conn.legs.length > 1)) {
            return cfg.transferTrip || 'Byte';
        }
        return cfg.directTrip || 'Direktresa';
    }

    function mrtWizardCardRouteText(state, ctx) {
        if (ctx === 'return') {
            return (state.toTitle || '') + ' → ' + (state.fromTitle || '');
        }
        return (state.fromTitle || '') + ' → ' + (state.toTitle || '');
    }

    function mrtWizardCardHtml(conn, idx, ctx, legFrom, legTo, cfg, state) {
        var dep = departureFromOrigin(conn) || '—';
        var arr = arrivalAtDestination(conn) || '—';
        var duration = mrtWizardFormatDuration(conn.duration_minutes, cfg);
        var detailId = 'mrt-jw-detail-' + ctx + '-' + idx;
        var legs = mrtWizardConnectionLegs(conn);
        var badges = '';
        legs.forEach(function(leg) {
            badges += mrtWizardLegVehicleBadge(leg);
        });
        if (!badges) {
            badges = mrtWizardVehicleBadge(conn.train_type, conn.service_name);
        }
        var notice = conn.notice || '';
        var html = '<article class="mrt-journey-wizard__trip-card" data-wizard-card="' + SU.escapeHtml(ctx) + '-' + idx + '">';
        html += '<div class="mrt-journey-wizard__trip-main">';
        html += '<div class="mrt-journey-wizard__trip-copy">';
        html += '<p class="mrt-journey-wizard__trip-time">' + SU.escapeHtml(dep) + '→' + SU.escapeHtml(arr);
        if (notice) {
            html += '<span class="mrt-journey-wizard__notice-dot" aria-label="' + SU.escapeHtml(cfg.noticeLabel || '') + '">!</span>';
        }
        html += '</p>';
        if (notice) {
            html += '<p class="mrt-journey-wizard__notice">' + SU.escapeHtml(notice) + '</p>';
        }
        html += '<p class="mrt-journey-wizard__trip-route">' + SU.escapeHtml(mrtWizardCardRouteText(state, ctx)) + '</p>';
        html += '<div class="mrt-journey-wizard__vehicle-row">' + badges + '</div>';
        html += '</div>';
        html += '<div class="mrt-journey-wizard__trip-side">';
        if (duration) {
            html += '<span class="mrt-journey-wizard__duration">' + SU.escapeHtml(duration) + '</span>';
        }
        html += '<button type="button" class="mrt-journey-wizard__btn-select" aria-label="' +
            SU.escapeHtml(mrtWizardAriaChooseTrip(conn, dep, arr, cfg)) + '" data-ctx="' + SU.escapeHtml(ctx) +
            '" data-idx="' + String(idx) + '">' + SU.escapeHtml(cfg.selectTrip || 'Välj →') + '</button>';
        html += '</div>';
        html += '</div>';
        html += '<button type="button" class="mrt-journey-wizard__btn-detail" aria-label="' +
            SU.escapeHtml((cfg.btnShowStopsAria || '').replace('%s', conn.service_name || mrtWizardConnectionMeta(conn, cfg))) +
            '" aria-expanded="false" aria-controls="' + detailId + '" data-ctx="' + SU.escapeHtml(ctx) +
            '" data-idx="' + String(idx) + '" data-leg-from="' + String(legFrom) + '" data-leg-to="' + String(legTo) + '">';
        html += '<span>' + SU.escapeHtml(mrtWizardConnectionMeta(conn, cfg)) + '</span><span class="mrt-journey-wizard__chevron" aria-hidden="true"></span>';
        html += '</button>';
        html += '<div class="mrt-journey-wizard__detail" id="' + detailId + '" hidden></div>';
        html += '</article>';
        return html;
    }

    function mrtWizardSelectedTripHtml(conn, cfg, state) {
        var dep = departureFromOrigin(conn) || '—';
        var arr = arrivalAtDestination(conn) || '—';
        var duration = mrtWizardFormatDuration(conn.duration_minutes, cfg);
        var html = '<div class="mrt-journey-wizard__selected-label">' + SU.escapeHtml(cfg.selectedOutbound || 'Vald utresa') + '</div>';
        html += '<div class="mrt-journey-wizard__selected-card">';
        html += '<div><strong>' + SU.escapeHtml(dep) + '→' + SU.escapeHtml(arr) + '</strong>';
        html += '<span> • ' + SU.escapeHtml(state.fromTitle || '') + ' → ' + SU.escapeHtml(state.toTitle || '') + '</span></div>';
        if (duration) {
            html += '<strong>' + SU.escapeHtml(duration) + '</strong>';
        }
        html += '<div class="mrt-journey-wizard__vehicle-row">' + mrtWizardVehicleBadge(conn.train_type, conn.service_name) + '</div>';
        html += '</div>';
        return html;
    }

    function mrtWizardRenderConnectionCards($target, list, ctx, legFrom, legTo, cfg, state) {
        var html = '<div class="mrt-journey-wizard__trip-list" data-wizard-conn-context="' + SU.escapeHtml(ctx) + '">';
        list.forEach(function(conn, idx) {
            html += mrtWizardCardHtml(conn, idx, ctx, legFrom, legTo, cfg, state);
        });
        html += '</div>';
        $target.empty().append(html);
    }

    function mrtWizardStationTime(s) {
        return s.departure_time || s.arrival_time || '';
    }

    function mrtWizardTimelineStopsHtml(stops, cfg, expanded) {
        var html = '';
        var visibleStops = expanded ? stops : stops.filter(function(_, i) {
            return i === 0 || i === stops.length - 1;
        });
        visibleStops.forEach(function(stop, i) {
            var isTerminal = i === 0 || i === visibleStops.length - 1;
            html += '<div class="mrt-journey-wizard__timeline-row' + (isTerminal ? ' is-terminal' : '') + '">';
            html += '<time class="mrt-journey-wizard__timeline-time">' + SU.escapeHtml(mrtWizardStationTime(stop)) + '</time>';
            html += '<span class="mrt-journey-wizard__timeline-node" aria-hidden="true"></span>';
            html += '<span class="mrt-journey-wizard__timeline-station">' + SU.escapeHtml(stop.station_title || '') + '</span>';
            html += '</div>';
        });
        if (!expanded && stops.length > 2) {
            html += '<button type="button" class="mrt-journey-wizard__passed-toggle" data-wizard-passed-toggle>' +
                '⌄ ' + SU.escapeHtml(cfg.showStops || 'Visa passerade stationer') +
                '</button>';
        } else if (expanded && stops.length > 2) {
            html += '<button type="button" class="mrt-journey-wizard__passed-toggle" data-wizard-passed-toggle>' +
                '⌃ ' + SU.escapeHtml(cfg.hideStops || 'Dölj passerade stationer') +
                '</button>';
        }
        return html;
    }

    function mrtWizardBuildStopsDetailHtml(detail, notice, cfg, leg, expanded) {
        var html = '';
        if (notice) {
            html += '<p class="mrt-journey-wizard__notice"><strong>' + SU.escapeHtml(cfg.noticeLabel) + ':</strong> ' + SU.escapeHtml(notice) + '</p>';
        }
        if (leg) {
            html += '<div class="mrt-journey-wizard__timeline-leg">';
            html += mrtWizardLegVehicleBadge(leg);
            if (leg.destination || leg.direction) {
                html += '<span class="mrt-journey-wizard__towards">' +
                    SU.escapeHtml((cfg.towards || 'mot %s').replace('%s', leg.destination || leg.direction)) +
                    '</span>';
            }
            html += '</div>';
        }
        html += '<div class="mrt-journey-wizard__timeline">';
        html += mrtWizardTimelineStopsHtml(detail.stops || [], cfg, Boolean(expanded));
        html += '</div>';
        return html;
    }

    function mrtWizardLoadMultiLegDetailRows(conn, $cell, $btn, cfg, ajaxPost, expanded) {
        var legTpl = cfg.legSegmentLabel || 'Train %d';
        var multiHtml = '<div class="mrt-journey-wizard__detail mrt-journey-wizard__detail--multi">';
        var legIndex = 0;
        function loadNextLeg() {
            if (legIndex >= conn.legs.length) {
                multiHtml += '</div>';
                $cell.html(multiHtml);
                $btn.attr('aria-expanded', 'true');
                $cell.removeAttr('hidden');
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
                multiHtml += '<h4 class="mrt-journey-wizard__detail-title">' + SU.escapeHtml(title) + '</h4>';
                multiHtml += mrtWizardBuildStopsDetailHtml(detail, notice, cfg, leg, expanded);
                multiHtml += '</div>';
                if (legIndex < conn.legs.length - 1) {
                    multiHtml += '<div class="mrt-journey-wizard__transfer-block">' + SU.escapeHtml(cfg.transferTrip || 'Byte') + '</div>';
                }
                legIndex += 1;
                loadNextLeg();
            }).fail(function() {
                $cell.html('<div class="mrt-alert mrt-alert-error"></div>');
                $cell.find('.mrt-alert').text(cfg.errorGeneric);
                $btn.attr('aria-expanded', 'true');
                $cell.removeAttr('hidden');
            });
        }
        loadNextLeg();
    }

    /**
     * Expand/collapse connection detail row (single-leg AJAX or multi-leg chain).
     *
     * @param {object} wctx Wizard runtime from mrtWizardCreateRuntime
     * @param {jQuery} $btn Detail button
     */
    function mrtWizardLoadDetailIntoCard(wctx, $btn, expanded) {
        var cfg = wctx.cfg;
        var legCtx = $btn.attr('data-ctx');
        var idx = parseInt($btn.attr('data-idx'), 10);
        var legFrom = parseInt($btn.attr('data-leg-from'), 10);
        var legTo = parseInt($btn.attr('data-leg-to'), 10);
        var list = legCtx === 'return' ? wctx.lastReturnList : wctx.lastOutboundList;
        var conn = list[idx];
        if (!conn) {
            return;
        }
        var $card = $btn.closest('.mrt-journey-wizard__trip-card');
        var $detail = $card.find('.mrt-journey-wizard__detail').first();
        $detail.removeAttr('hidden');
        $detail.toggleClass('is-passed-expanded', Boolean(expanded));
        $detail.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
        $card.addClass('is-expanded');

        if (conn.legs && conn.legs.length > 1) {
            mrtWizardLoadMultiLegDetailRows(conn, $detail, $btn, cfg, wctx.ajaxPost, expanded);
            return;
        }

        wctx.ajaxPost('mrt_journey_connection_detail', {
            from_station: legFrom,
            to_station: legTo,
            service_id: conn.service_id
        }).done(function(res) {
            if (!res || !res.success || !res.data) {
                $detail.html('<div class="mrt-alert mrt-alert-error"></div>');
                $detail.find('.mrt-alert').text(cfg.errorGeneric);
                return;
            }
            var detail = res.data.detail || {};
            var notice = res.data.notice || '';
            var html = mrtWizardBuildStopsDetailHtml(detail, notice, cfg, mrtWizardConnectionLegs(conn)[0], expanded);
            $detail.html(html);
            $btn.attr('aria-expanded', 'true');
        }).fail(function() {
            $detail.html('<div class="mrt-alert mrt-alert-error"></div>');
            $detail.find('.mrt-alert').text(cfg.errorGeneric);
            $btn.attr('aria-expanded', 'true');
        });
    }

    /**
     * Expand/collapse connection detail card.
     *
     * @param {object} wctx Wizard runtime from mrtWizardCreateRuntime
     * @param {jQuery} $btn Detail button
     */
    function mrtWizardToggleDetailRow(wctx, $btn) {
        var $card = $btn.closest('.mrt-journey-wizard__trip-card');
        var $detail = $card.find('.mrt-journey-wizard__detail').first();
        if ($detail.html()) {
            var nextExpanded = $detail.is('[hidden]');
            $detail.prop('hidden', !nextExpanded);
            $btn.attr('aria-expanded', nextExpanded ? 'true' : 'false');
            $card.toggleClass('is-expanded', nextExpanded);
            return;
        }
        mrtWizardLoadDetailIntoCard(wctx, $btn, false);
    }

    function mrtWizardBindRouteNext(wctx) {
        var $root = wctx.$root;
        var cfg = wctx.cfg;
        var state = wctx.state;
        $root.on('click', '[data-wizard-next="route"]', function() {
            wctx.clearError();
            var from = parseInt($root.find('#mrt_wizard_from').val(), 10);
            var to = parseInt($root.find('#mrt_wizard_to').val(), 10);
            var trip = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
            if (!from || !to) {
                wctx.showError(cfg.pleaseStations);
                return;
            }
            if (from === to) {
                wctx.showError(FA.msg('errorSameStations', cfg.errorGeneric));
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
            mrtWizardUpdateContext($root, state, cfg);
            wctx.buildStepNav();
            wctx.showPanel('date');
            var cm0 = window.MRTDateUtils.currentCalendarYearMonth();
            wctx.loadCalendar(cm0.year, cm0.month);
        });
    }

    function mrtWizardBindCalendarNav(wctx) {
        var $root = wctx.$root;
        var state = wctx.state;
        $root.on('click', '.mrt-journey-wizard__cal-prev', function() {
            var cm = window.MRTDateUtils.addCalendarMonths(state.calYear, state.calMonth, -1);
            wctx.loadCalendar(cm.year, cm.month);
        });
        $root.on('click', '.mrt-journey-wizard__cal-next', function() {
            var cm = window.MRTDateUtils.addCalendarMonths(state.calYear, state.calMonth, 1);
            wctx.loadCalendar(cm.year, cm.month);
        });
        $root.on('click', '[data-wizard-current-month]', function() {
            var cm0 = window.MRTDateUtils.currentCalendarYearMonth();
            wctx.loadCalendar(cm0.year, cm0.month);
        });
    }

    function mrtWizardBindDetailAndSelect(wctx) {
        var $root = wctx.$root;
        var state = wctx.state;
        $root.on('click', '.mrt-journey-wizard__btn-detail', function() {
            mrtWizardToggleDetailRow(wctx, $(this));
        });
        $root.on('click', '[data-wizard-passed-toggle]', function() {
            var $detail = $(this).closest('.mrt-journey-wizard__detail');
            var $card = $(this).closest('.mrt-journey-wizard__trip-card');
            var $btn = $card.find('.mrt-journey-wizard__btn-detail').first();
            var expandPassed = !$detail.hasClass('is-passed-expanded');
            mrtWizardLoadDetailIntoCard(wctx, $btn, expandPassed);
        });
        $root.on('click', '.mrt-journey-wizard__btn-select', function() {
            var legCtx = $(this).attr('data-ctx');
            var idx = parseInt($(this).attr('data-idx'), 10);
            var list = legCtx === 'return' ? wctx.lastReturnList : wctx.lastOutboundList;
            var conn = list[idx];
            if (!conn) {
                return;
            }
            if (legCtx === 'outbound') {
                state.outbound = conn;
                state.inbound = null;
                if (state.tripType === 'return') {
                    wctx.showPanel('return');
                    wctx.loadReturnConnections();
                } else {
                    wctx.showPanel('summary');
                    wctx.renderSummary();
                }
            } else {
                state.inbound = conn;
                wctx.showPanel('summary');
                wctx.renderSummary();
            }
        });
    }

    function mrtWizardBindBack(wctx) {
        var $root = wctx.$root;
        var state = wctx.state;
        $root.on('click', '[data-wizard-back]', function() {
            var step = $(this).attr('data-wizard-back');
            wctx.clearError();
            if (step === 'date') {
                state.date = '';
                wctx.showPanel('route');
            } else if (step === 'outbound') {
                state.outbound = null;
                state.inbound = null;
                wctx.showPanel('date');
                wctx.loadCalendar(state.calYear, state.calMonth);
            } else if (step === 'return') {
                state.inbound = null;
                wctx.showPanel('outbound');
                wctx.loadOutboundConnections();
            } else if (step === 'summary') {
                if (state.tripType === 'return') {
                    wctx.showPanel('return');
                    wctx.loadReturnConnections();
                } else {
                    wctx.showPanel('outbound');
                    wctx.loadOutboundConnections();
                }
            }
        });
    }

    function mrtWizardBindTripType(wctx) {
        var $root = wctx.$root;
        var state = wctx.state;
        $root.on('change', 'input[name="mrt_wizard_trip_type"]', function() {
            state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
        });
    }

    function mrtWizardBindAllEvents(wctx) {
        mrtWizardBindRouteNext(wctx);
        mrtWizardBindCalendarNav(wctx);
        mrtWizardBindDetailAndSelect(wctx);
        mrtWizardBindBack(wctx);
        mrtWizardBindTripType(wctx);
    }

    function mrtWizardAttachRuntimeErrorsAjax(wctx, $err, cfg) {
        wctx.showError = function(msg) {
            $err.html('<div class="mrt-alert mrt-alert-error"></div>');
            $err.find('.mrt-alert').text(msg);
        };
        wctx.clearError = function() {
            $err.empty();
        };
        wctx.ajaxPost = function(action, data) {
            return FA.post(action, data, {
                ajaxurl: cfg.ajaxurl,
                nonce: cfg.nonce
            });
        };
    }

    function mrtWizardAttachRuntimeStepNav(wctx, $root, cfg, state, $stepsOl) {
        wctx.getStepSequence = function() {
            var seq = ['route', 'date', 'outbound'];
            if (state.tripType === 'return') {
                seq.push('return');
            }
            seq.push('summary');
            return seq;
        };
        wctx.buildStepNav = function() {
            var seq = wctx.getStepSequence();
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
        };
        wctx.updateStepNav = function(name) {
            var seq = wctx.getStepSequence();
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
        };
    }

    function mrtWizardAttachRuntimeShowPanel(wctx, $root) {
        wctx.showPanel = function(name) {
            var $visible = null;
            mrtWizardUpdateContext($root, wctx.state, wctx.cfg);
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
            wctx.updateStepNav(name);
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
        };
    }

    function mrtWizardAttachRuntimeCalendar(wctx, $root, cfg, startOfWeek, state) {
        wctx.renderCalendarGrid = function(year, month, daysMap) {
            mrtWizardRenderCalendarGrid($root, year, month, daysMap, cfg, startOfWeek, state.date, function(ymd) {
                state.date = ymd;
                wctx.clearError();
                wctx.showPanel('outbound');
                wctx.loadOutboundConnections();
            });
        };
        wctx.loadCalendar = function(year, month) {
            state.calYear = year;
            state.calMonth = month;
            var $calHost = $root.find('[data-wizard-calendar]');
            $calHost.attr('aria-busy', 'true');
            $calHost.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
            wctx.ajaxPost('mrt_journey_calendar_month', {
                from_station: state.from,
                to_station: state.to,
                year: year,
                month: month
            }).done(function(res) {
                if (!res || !res.success || !res.data) {
                    $calHost.attr('aria-busy', 'false');
                    wctx.showError(cfg.errorGeneric);
                    return;
                }
                wctx.renderCalendarGrid(res.data.year, res.data.month, res.data.days || {});
                $calHost.attr('aria-busy', 'false');
            }).fail(function() {
                $calHost.attr('aria-busy', 'false');
                wctx.showError(FA.msg('networkError', cfg.errorGeneric));
            });
        };
    }

    function mrtWizardAttachRuntimeConnTable(wctx, cfg, state) {
        wctx.renderConnectionTable = function($target, list, legCtx, legFrom, legTo) {
            if (legCtx === 'outbound') {
                wctx.lastOutboundList = list;
            } else {
                wctx.lastReturnList = list;
            }
            mrtWizardRenderConnectionCards(
                $target,
                list,
                legCtx,
                legFrom,
                legTo,
                cfg,
                state
            );
        };
    }

    function mrtWizardAttachRuntimeLoadOutbound(wctx, $root, cfg, state) {
        wctx.loadOutboundConnections = function() {
            var $box = $root.find('[data-wizard-outbound]');
            $box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
            wctx.ajaxPost('mrt_search_journey', {
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
                wctx.renderConnectionTable($box, conns, 'outbound', state.from, state.to);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    FA.msg('networkError', cfg.errorGeneric)
                );
            });
        };
    }

    function mrtWizardAttachRuntimeLoadReturn(wctx, $root, cfg, state) {
        wctx.loadReturnConnections = function() {
            var arr = arrivalAtDestination(state.outbound);
            if (!arr) {
                wctx.showError(cfg.errorGeneric);
                return;
            }
            var $sum = $root.find('[data-wizard-return-summary]');
            var ob = state.outbound;
            $sum.html(mrtWizardSelectedTripHtml(ob, cfg, state));

            var $box = $root.find('[data-wizard-return]');
            $box.html('<p class="mrt-empty">' + SU.escapeHtml(cfg.loading) + '</p>');
            wctx.ajaxPost('mrt_search_journey', {
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
                wctx.renderConnectionTable($box, conns, 'return', state.to, state.from);
            }).fail(function() {
                $box.html('<div class="mrt-alert mrt-alert-error"></div>');
                $box.find('.mrt-alert').text(
                    FA.msg('networkError', cfg.errorGeneric)
                );
            });
        };
    }

    function mrtWizardAttachRuntimeSummary(wctx, $root, cfg, state) {
        wctx.renderSummary = function() {
            var $box = $root.find('[data-wizard-summary]');
            var parts = [];
            var ob = state.outbound;
            if (ob) {
                parts.push('<div class="mrt-journey-wizard__summary-card"><strong>' + SU.escapeHtml(cfg.outboundHeading) + '</strong><br>' +
                    SU.escapeHtml(state.fromTitle) + ' → ' + SU.escapeHtml(state.toTitle) + '<br>' +
                    SU.escapeHtml(window.MRTDateUtils.formatYmdForDisplay(state.date, cfg)) + '<br><span class="mrt-journey-wizard__trip-time">' +
                    SU.escapeHtml(departureFromOrigin(ob)) + '→' +
                    SU.escapeHtml(arrivalAtDestination(ob)) + '</span><div class="mrt-journey-wizard__vehicle-row">' +
                    mrtWizardVehicleBadge(ob.train_type, ob.service_name) + '</div>' +
                    '</div>');
            }
            if (state.tripType === 'return' && state.inbound) {
                var ib = state.inbound;
                parts.push('<div class="mrt-journey-wizard__summary-card"><strong>' + SU.escapeHtml(cfg.returnHeading) + '</strong><br>' +
                    SU.escapeHtml(state.toTitle) + ' → ' + SU.escapeHtml(state.fromTitle) + '<br>' +
                    SU.escapeHtml(window.MRTDateUtils.formatYmdForDisplay(state.date, cfg)) + '<br><span class="mrt-journey-wizard__trip-time">' +
                    SU.escapeHtml(departureFromOrigin(ib)) + '→' +
                    SU.escapeHtml(arrivalAtDestination(ib)) + '</span><div class="mrt-journey-wizard__vehicle-row">' +
                    mrtWizardVehicleBadge(ib.train_type, ib.service_name) + '</div>' +
                    '</div>');
            }
            $box.html(parts.join('') + mrtWizardBuildPriceSection(
                state.tripType,
                cfg,
                mrtWizardPriceZonesForStationPair(state.from, state.to, cfg)
            ));

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
        };
    }

    /**
     * Wizard state + UI actions for one root element.
     *
     * @param {jQuery} $root Wizard wrapper
     * @param {object} cfg Localized config (mrtJourneyWizard)
     * @param {number} startOfWeek 0–6
     * @return {object} Runtime with methods; events via mrtWizardBindAllEvents
     */
    function mrtWizardCreateRuntime($root, cfg, startOfWeek) {
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

        var wctx = {
            $root: $root,
            cfg: cfg,
            startOfWeek: startOfWeek,
            state: state,
            lastOutboundList: lastOutboundList,
            lastReturnList: lastReturnList,
            $err: $err,
            $stepsOl: $stepsOl
        };

        mrtWizardAttachRuntimeErrorsAjax(wctx, $err, cfg);
        mrtWizardAttachRuntimeStepNav(wctx, $root, cfg, state, $stepsOl);
        mrtWizardAttachRuntimeShowPanel(wctx, $root);
        mrtWizardAttachRuntimeCalendar(wctx, $root, cfg, startOfWeek, state);
        mrtWizardAttachRuntimeConnTable(wctx, cfg, state);
        mrtWizardAttachRuntimeLoadOutbound(wctx, $root, cfg, state);
        mrtWizardAttachRuntimeLoadReturn(wctx, $root, cfg, state);
        mrtWizardAttachRuntimeSummary(wctx, $root, cfg, state);

        state.tripType = $root.find('input[name="mrt_wizard_trip_type"]:checked').val() || 'single';
        return wctx;
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
        var wctx = mrtWizardCreateRuntime($root, cfg, startOfWeek);
        mrtWizardBindAllEvents(wctx);
        wctx.buildStepNav();
        wctx.updateStepNav('route');
    }

    $(function() {
        $('.mrt-journey-wizard').each(function() {
            initOne($(this));
        });
    });
}(jQuery));
