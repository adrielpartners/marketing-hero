<?php

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$deleteData = get_option('mh_delete_data_on_uninstall', '0');

if ($deleteData !== '1') {
    return;
}

global $wpdb;

$tables = [
    $wpdb->prefix . 'mh_activity',
    $wpdb->prefix . 'mh_result',
    $wpdb->prefix . 'mh_campaign',
    $wpdb->prefix . 'mh_channel',
    $wpdb->prefix . 'mh_result_category',
    $wpdb->prefix . 'mh_settings',
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

delete_option('mh_delete_data_on_uninstall');

delete_option('mh_db_version');
