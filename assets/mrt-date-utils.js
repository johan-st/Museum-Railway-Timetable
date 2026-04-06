/**
 * Shared date/time helpers for Museum Railway Timetable (frontend + admin)
 *
 * @package Museum_Railway_Timetable
 */
(function(global) {
    'use strict';

    var HH_MM_PATTERN = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;

    function pad2(n) {
        var x = parseInt(n, 10);
        if (!isFinite(x) || x < 0) {
            x = 0;
        }
        return (x < 10 ? '0' : '') + x;
    }

    function parseInt10(n) {
        var x = parseInt(n, 10);
        return isFinite(x) ? x : NaN;
    }

    function todayYearMonth() {
        var d = new Date();
        return { year: d.getFullYear(), month: d.getMonth() + 1 };
    }

    global.MRTDateUtils = {
        formatYmdForDisplay: function(ymd, monthNamesOrCfg) {
            if (!ymd || typeof ymd !== 'string') {
                return '';
            }
            var p = ymd.split('-');
            if (p.length !== 3) {
                return ymd;
            }
            var y = p[0];
            var mo = parseInt10(p[1]);
            var day = parseInt10(p[2]);
            if (mo !== mo || day !== day || mo < 1 || mo > 12) {
                return ymd;
            }
            var monthNames = Array.isArray(monthNamesOrCfg)
                ? monthNamesOrCfg
                : (monthNamesOrCfg && monthNamesOrCfg.monthNames);
            if (monthNames && monthNames[mo - 1]) {
                return monthNames[mo - 1] + ' ' + day + ', ' + y;
            }
            return ymd;
        },

        ymdFromParts: function(year, month, day) {
            var y = parseInt10(year);
            var m = parseInt10(month);
            var d = parseInt10(day);
            if (y !== y || m !== m || d !== d) {
                return '';
            }
            return y + '-' + pad2(m) + '-' + pad2(d);
        },

        calendarMonthTitle: function(year, month, monthNames) {
            var label = monthNames && monthNames[month - 1] ? monthNames[month - 1] : String(month);
            return label + ' ' + year;
        },

        daysInMonth: function(year, month) {
            return new Date(year, month, 0).getDate();
        },

        monthStartColumn: function(year, month, startOfWeek) {
            var first = new Date(year, month - 1, 1);
            return (first.getDay() - startOfWeek + 7) % 7;
        },

        currentCalendarYearMonth: function() {
            return todayYearMonth();
        },

        addCalendarMonths: function(year, month, delta) {
            var y = parseInt10(year);
            var m = parseInt10(month);
            var del = parseInt10(delta);
            if (y !== y || m !== m || del !== del) {
                return todayYearMonth();
            }
            var d = new Date(y, m - 1 + del, 1);
            return { year: d.getFullYear(), month: d.getMonth() + 1 };
        },

        validateHhMm: function(timeString) {
            if (!timeString || String(timeString).trim() === '') {
                return true;
            }
            return HH_MM_PATTERN.test(String(timeString).trim());
        }
    };
}(typeof window !== 'undefined' ? window : this));
