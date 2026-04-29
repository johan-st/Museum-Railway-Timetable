<?php
/**
 * Custom post types and taxonomies – loader
 *
 * @package Museum_Railway_Timetable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once MRT_PATH . 'inc/cpt/cpt-register.php';
require_once MRT_PATH . 'inc/cpt/cpt-admin.php';
