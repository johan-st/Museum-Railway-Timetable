<?php
// Remove plugin options on uninstall; keep tables/data by default
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
delete_option('mrt_settings');
delete_option('mrt_price_matrix');
