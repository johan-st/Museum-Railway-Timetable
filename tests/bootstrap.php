<?php
/**
 * Minimal environment for unit tests (no full WordPress load).
 *
 * Loads production code from inc/; business rules stay in those files, not here.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('MRT_TEXT_DOMAIN', 'museum-railway-timetable');

require_once __DIR__ . '/wp-stubs.php';

if (!function_exists('__')) {
    /**
     * @param string $text
     * @param string $domain
     */
    function __($text, $domain = 'default') {
        return $text;
    }
}

require_once ABSPATH . 'inc/functions/helpers-datetime.php';
require_once ABSPATH . 'inc/functions/helpers-services.php';
require_once ABSPATH . 'inc/functions/journey-notice.php';
require_once ABSPATH . 'inc/functions/journey-normalize.php';
require_once ABSPATH . 'inc/functions/journey-prices.php';
require_once ABSPATH . 'inc/admin-ajax/journey-parse.php';
