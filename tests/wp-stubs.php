<?php
/**
 * Minimal WordPress API stubs for PHPUnit (no core load).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if (!class_exists('WP_Error')) {
    class WP_Error {
        /** @var array<string, array<int, string>> */
        public $errors = [];

        /**
         * @param string|int $code
         * @param string     $message
         * @param mixed      $data
         */
        public function __construct($code = '', $message = '', $data = '') {
            unset($data);
            if ((string) $code !== '') {
                $this->errors[(string) $code] = [(string) $message];
            }
        }

        public function get_error_code(): string {
            $keys = array_keys($this->errors);

            return (string) ($keys[0] ?? '');
        }
    }
}

if (!function_exists('is_wp_error')) {
    /**
     * @param mixed $thing
     */
    function is_wp_error($thing): bool {
        return $thing instanceof WP_Error;
    }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * @param string $str
     */
    function sanitize_text_field($str): string {
        return is_string($str) ? trim(wp_strip_all_tags($str)) : '';
    }
}

if (!function_exists('wp_strip_all_tags')) {
    /**
     * @param string $str
     */
    function wp_strip_all_tags($str): string {
        return strip_tags($str);
    }
}

if (!function_exists('wp_unslash')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function wp_unslash($value) {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('sanitize_key')) {
    /**
     * @param string $key
     */
    function sanitize_key($key): string {
        $key = strtolower((string) $key);

        return (string) preg_replace('/[^a-z0-9_\-]/', '', $key);
    }
}

if (!function_exists('get_the_title')) {
    /**
     * @param int|WP_Post $post
     */
    function get_the_title($post = 0): string {
        $id = is_object($post) ? (int) $post->ID : (int) $post;

        return $id > 0 ? 'Post ' . $id : '';
    }
}

if (!function_exists('get_post')) {
    /**
     * @param int $post
     * @return null
     */
    function get_post($post = null, $output = 'OBJECT', $filter = 'raw') {
        unset($output, $filter, $post);

        return null;
    }
}

if (!function_exists('get_option')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_options'][ option_name ]
     *
     * @param string $option
     * @param mixed  $default
     * @return mixed
     */
    function get_option($option, $default = false) {
        if (isset($GLOBALS['mrt_test_options']) && is_array($GLOBALS['mrt_test_options']) && array_key_exists($option, $GLOBALS['mrt_test_options'])) {
            return $GLOBALS['mrt_test_options'][$option];
        }

        return $default;
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_post_meta'][ "{$id}|{$key}" ]
     *
     * @param int    $post_id
     * @param string $key
     * @param bool   $single
     * @return mixed
     */
    function get_post_meta($post_id, $key, $single = false) {
        $k = (int) $post_id . '|' . $key;
        if (isset($GLOBALS['mrt_test_post_meta'][$k])) {
            return $GLOBALS['mrt_test_post_meta'][$k];
        }

        return $single ? '' : [];
    }
}
