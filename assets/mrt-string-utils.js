/**
 * Shared string/DOM-safe helpers for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */
(function(global) {
    'use strict';

    global.MRTStringUtils = {
        escapeHtml: function(str) {
            if (str == null) {
                return '';
            }
            var s = String(str);
            return s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    };
}(typeof window !== 'undefined' ? window : this));
