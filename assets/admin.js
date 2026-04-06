/**
 * Admin JavaScript for Museum Railway Timetable
 * Main entry point – loads admin modules
 *
 * @package Museum_Railway_Timetable
 *
 * MODULES:
 * - mrt-string-utils.js: MRTStringUtils.escapeHtml
 * - mrt-date-utils.js: MRTDateUtils (datum/tid, validateHhMm)
 * - admin-utils.js: msg (mrtAdmin), getAjaxUrl, escapeHtml (→ MRTStringUtils), populateDestinationsSelect, setSelectState, validateTimeFormat (→ MRTDateUtils)
 * - mrt-frontend-api.js: MRTFrontendApi (endast frontend; ej admin-bundle)
 * - admin-route-ui.js: Route stations add/remove/reorder
 * - admin-stoptimes-ui.js: Stop times legacy inline editing
 * - admin-timetable-services-ui.js: Timetable add/remove trips
 * - admin-service-edit.js: Service edit (route change, title preview, stops-here, save all)
 */
(function($) {
    'use strict';

    $(function() {
        if (window.MRTAdminServiceEdit && typeof window.MRTAdminServiceEdit.init === 'function') {
            window.MRTAdminServiceEdit.init();
        }

        if (typeof console !== 'undefined' && console.log && window.mrtDebug) {
            console.log('Museum Railway Timetable admin loaded.');
        }
    });

})(jQuery);
