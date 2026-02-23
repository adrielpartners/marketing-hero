<?php

declare(strict_types=1);

namespace MarketingHero\Support;

final class Installer
{
    public static function activate(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();

        $activity = $wpdb->prefix . 'mh_activity';
        $result = $wpdb->prefix . 'mh_result';
        $campaign = $wpdb->prefix . 'mh_campaign';

        $sqlActivity = "CREATE TABLE {$activity} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            occurred_at DATETIME NOT NULL,
            type VARCHAR(40) NOT NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            cost_cents INT UNSIGNED NULL,
            campaign_id BIGINT UNSIGNED NULL,
            source VARCHAR(80) NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY occurred_at (occurred_at),
            KEY type_occurred_at (type, occurred_at),
            KEY campaign_occurred_at (campaign_id, occurred_at)
        ) {$charsetCollate};";

        $sqlResult = "CREATE TABLE {$result} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            occurred_at DATETIME NOT NULL,
            type VARCHAR(20) NOT NULL,
            value_cents INT UNSIGNED NULL,
            campaign_id BIGINT UNSIGNED NULL,
            source VARCHAR(80) NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY occurred_at (occurred_at),
            KEY type_occurred_at (type, occurred_at),
            KEY campaign_occurred_at (campaign_id, occurred_at),
            KEY source_occurred_at (source, occurred_at)
        ) {$charsetCollate};";

        $sqlCampaign = "CREATE TABLE {$campaign} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            name VARCHAR(120) NOT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY start_end (start_date, end_date)
        ) {$charsetCollate};";

        dbDelta($sqlActivity);
        dbDelta($sqlResult);
        dbDelta($sqlCampaign);

        add_option('mh_delete_data_on_uninstall', '0');

        $role = get_role('administrator');
        if ($role instanceof \WP_Role) {
            $role->add_cap('manage_marketing_hero');
        }
    }
}
