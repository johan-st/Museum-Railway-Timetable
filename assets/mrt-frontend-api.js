/**
 * Frontend AJAX + localized strings (mrtFrontend from wp_localize_script)
 *
 * @package Museum_Railway_Timetable
 */
(function(global, $) {
    'use strict';

    function fe() {
        return typeof mrtFrontend !== 'undefined' ? mrtFrontend : {};
    }

    global.MRTFrontendApi = {
        getAjaxUrl: function() {
            return fe().ajaxurl || '/wp-admin/admin-ajax.php';
        },

        getNonce: function() {
            return fe().nonce || '';
        },

        /**
         * Localized string from mrtFrontend, or fallback (e.g. when key missing).
         *
         * @param {string} key
         * @param {string} [fallback]
         * @returns {string}
         */
        msg: function(key, fallback) {
            var o = fe();
            if (o[key] !== undefined && o[key] !== '') {
                return o[key];
            }
            return fallback !== undefined ? fallback : '';
        },

        /**
         * POST to admin-ajax.php as JSON. Optional extra.ajaxurl / extra.nonce override (e.g. wizard cfg).
         *
         * @param {string} action
         * @param {Object} [data]
         * @param {Object} [extra]
         * @returns {jqXHR}
         */
        post: function(action, data, extra) {
            extra = extra || {};
            var url = extra.ajaxurl !== undefined ? extra.ajaxurl : this.getAjaxUrl();
            var nonce = extra.nonce !== undefined ? extra.nonce : this.getNonce();
            return $.post(url, $.extend({
                action: action,
                nonce: nonce
            }, data || {}), null, 'json');
        }
    };
}(typeof window !== 'undefined' ? window : this, jQuery));
