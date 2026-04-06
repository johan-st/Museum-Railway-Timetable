/**
 * Admin utilities for Museum Railway Timetable
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    window.MRTAdminUtils = {
        /**
         * Localized string from mrtAdmin (wp_localize_script), or fallback.
         *
         * @param {string} key
         * @param {string} [fallback]
         * @returns {string}
         */
        msg: function(key, fallback) {
            var o = typeof mrtAdmin !== 'undefined' ? mrtAdmin : {};
            if (o[key] !== undefined && o[key] !== '') {
                return o[key];
            }
            return fallback !== undefined ? fallback : '';
        },

        /**
         * Get AJAX URL for admin requests
         * @returns {string}
         */
        getAjaxUrl: function() {
            return (typeof mrtAdmin !== 'undefined' && mrtAdmin.ajaxurl) ? mrtAdmin.ajaxurl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
        },

        /**
         * Escape HTML for safe insertion into HTML strings
         * @param {string} str - String to escape
         * @returns {string}
         */
        escapeHtml: function(str) {
            return window.MRTStringUtils.escapeHtml(str);
        },

        /**
         * Populate a select element with destination options (XSS-safe via textContent)
         * @param {jQuery} $select - The select element
         * @param {Array} destinations - Array of {id, name}
         * @param {string} defaultLabel - Label for the empty option
         */
        populateDestinationsSelect: function($select, destinations, defaultLabel) {
            var label = defaultLabel || window.MRTAdminUtils.msg('selectDestination', '— Select Destination —');
            var selectEl = $select[0];
            $select.empty();
            var defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = label;
            selectEl.appendChild(defaultOpt);
            if (destinations && destinations.length) {
                destinations.forEach(function(dest) {
                    var opt = document.createElement('option');
                    opt.value = dest.id;
                    opt.textContent = (dest.name != null ? String(dest.name) : '');
                    selectEl.appendChild(opt);
                });
            }
            $select.prop('disabled', false);
        },

        /**
         * Set select to loading or error state (XSS-safe)
         */
        setSelectState: function($select, state, label) {
            var text = label || (state === 'loading'
                ? window.MRTAdminUtils.msg('loading', 'Loading...')
                : window.MRTAdminUtils.msg('errorLoadingDestinations', 'Error loading destinations'));
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = text;
            $select.empty().append(opt).prop('disabled', state === 'loading');
        },

        /**
         * Validate time format (HH:MM) — delegates to MRTDateUtils.validateHhMm
         */
        validateTimeFormat: function(timeString) {
            return window.MRTDateUtils.validateHhMm(timeString);
        }
    };

})(jQuery);
