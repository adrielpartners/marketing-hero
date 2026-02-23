<?php

declare(strict_types=1);

namespace MarketingHero\Support;

final class Installer
{
    public const DB_VERSION = '2026.02.23.1';

    public static function activate(): void
    {
        self::runMigrations();

        $role = get_role('administrator');
        if ($role instanceof \WP_Role) {
            $role->add_cap('manage_marketing_hero');
        }
    }

    public static function maybeMigrate(): void
    {
        $current = (string) get_option('mh_db_version', '0');
        if (version_compare($current, self::DB_VERSION, '>=')) {
            return;
        }

        self::runMigrations();
    }

    private static function runMigrations(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $activity = $wpdb->prefix . 'mh_activity';
        $result = $wpdb->prefix . 'mh_result';
        $campaign = $wpdb->prefix . 'mh_campaign';
        $channel = $wpdb->prefix . 'mh_channel';
        $resultCategory = $wpdb->prefix . 'mh_result_category';
        $settings = $wpdb->prefix . 'mh_settings';

        dbDelta("CREATE TABLE {$campaign} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            name VARCHAR(120) NOT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY name (name),
            KEY start_end (start_date, end_date)
        ) {$charsetCollate};");

        dbDelta("CREATE TABLE {$channel} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            name VARCHAR(120) NOT NULL,
            category VARCHAR(20) NOT NULL,
            system_code SMALLINT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY category_active_sort (category, is_active, sort_order),
            KEY name (name)
        ) {$charsetCollate};");

        dbDelta("CREATE TABLE {$resultCategory} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            name VARCHAR(120) NOT NULL,
            system_code SMALLINT NOT NULL DEFAULT 0,
            is_system_default TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY active_sort (is_active, sort_order),
            KEY name (name)
        ) {$charsetCollate};");

        dbDelta("CREATE TABLE {$settings} (
            setting_key VARCHAR(80) NOT NULL,
            setting_value LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (setting_key)
        ) {$charsetCollate};");

        dbDelta("CREATE TABLE {$activity} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            occurred_at DATETIME NOT NULL,
            type VARCHAR(40) NULL,
            channel_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            cash_investment_cents INT UNSIGNED NULL,
            team_time_minutes INT UNSIGNED NOT NULL DEFAULT 0,
            owner_time_minutes INT UNSIGNED NOT NULL DEFAULT 0,
            campaign_id BIGINT UNSIGNED NULL,
            source VARCHAR(80) NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY campaign_occurred_at (campaign_id, occurred_at),
            KEY channel_occurred_at (channel_id, occurred_at),
            KEY occurred_at (occurred_at)
        ) {$charsetCollate};");

        dbDelta("CREATE TABLE {$result} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            occurred_at DATETIME NOT NULL,
            type VARCHAR(40) NULL,
            result_category_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            channel_id BIGINT UNSIGNED NULL,
            value_cents INT UNSIGNED NULL,
            campaign_id BIGINT UNSIGNED NULL,
            source VARCHAR(80) NULL,
            notes TEXT NULL,
            meta_json LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY campaign_occurred_at (campaign_id, occurred_at),
            KEY result_category_occurred_at (result_category_id, occurred_at),
            KEY channel_occurred_at (channel_id, occurred_at),
            KEY occurred_at (occurred_at)
        ) {$charsetCollate};");

        self::migrateActivityColumns($wpdb, $activity);
        self::seedDefaultChannels($wpdb, $channel);
        self::seedDefaultResultCategories($wpdb, $resultCategory);
        self::migrateActivityTypesToChannels($wpdb, $activity, $channel);
        self::migrateResultTypesToCategories($wpdb, $result, $resultCategory);
        self::ensureCoreSettings($wpdb, $settings, $resultCategory);

        update_option('mh_db_version', self::DB_VERSION, false);
        add_option('mh_delete_data_on_uninstall', '0');
    }

    private static function migrateActivityColumns(\wpdb $wpdb, string $activityTable): void
    {
        $hasCost = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$activityTable} LIKE %s", 'cost_cents'));
        $hasCash = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$activityTable} LIKE %s", 'cash_investment_cents'));

        if ($hasCost && !$hasCash) {
            $wpdb->query("ALTER TABLE {$activityTable} ADD COLUMN cash_investment_cents INT UNSIGNED NULL");
        }

        if ($hasCost) {
            $wpdb->query("UPDATE {$activityTable} SET cash_investment_cents = COALESCE(cash_investment_cents, cost_cents)");
        }
    }

    private static function seedDefaultChannels(\wpdb $wpdb, string $channelTable): void
    {
        $organic = [
            'Cold Call','Cold Email','Cold SMS','Door Gift','Door Hanger','Door Knocking',
            'Networking Contact','Social Outreach (DMs)','Social Post','Warm Call','Warm Email','Warm SMS',
        ];
        $paid = [
            'Print Ad','Search Ad (Google, Bing, etc.)','Social Media Ad (Facebook, X, etc.)','TV Ad (Broadcast)','TV Ad (Digital)','YouTube Ad',
        ];

        $sort = 0;
        self::upsertChannel($wpdb, $channelTable, 'Uncategorized', 'organic', 99, $sort++);
        sort($organic);
        sort($paid);

        foreach ($organic as $name) {
            self::upsertChannel($wpdb, $channelTable, $name, 'organic', 0, $sort++);
        }
        foreach ($paid as $name) {
            self::upsertChannel($wpdb, $channelTable, $name, 'paid', 0, $sort++);
        }
    }

    private static function upsertChannel(\wpdb $wpdb, string $table, string $name, string $category, int $systemCode, int $sort): void
    {
        $id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE name = %s LIMIT 1", $name));
        if ($id > 0) {
            $wpdb->update($table, ['category' => $category, 'system_code' => $systemCode], ['id' => $id], ['%s', '%d'], ['%d']);
            return;
        }

        $wpdb->insert($table, [
            'created_at' => gmdate('Y-m-d H:i:s'),
            'name' => $name,
            'category' => $category,
            'system_code' => $systemCode,
            'is_active' => 1,
            'sort_order' => $sort,
        ], ['%s', '%s', '%s', '%d', '%d', '%d']);
    }

    private static function seedDefaultResultCategories(\wpdb $wpdb, string $table): void
    {
        $defaults = [
            ['Leads', 1],
            ['Appointments', 2],
            ['Sales', 3],
            ['Reviews', 4],
            ['Referrals', 5],
            ['Subscriptions', 6],
            ['Uncategorized', 99],
        ];

        $sort = 0;
        foreach ($defaults as [$name, $systemCode]) {
            $id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE name = %s LIMIT 1", $name));
            if ($id > 0) {
                $wpdb->update($table, ['system_code' => $systemCode, 'is_system_default' => 1], ['id' => $id], ['%d', '%d'], ['%d']);
                continue;
            }

            $wpdb->insert($table, [
                'created_at' => gmdate('Y-m-d H:i:s'),
                'name' => $name,
                'system_code' => $systemCode,
                'is_system_default' => 1,
                'is_active' => 1,
                'sort_order' => $sort++,
            ], ['%s', '%s', '%d', '%d', '%d', '%d']);
        }
    }

    private static function migrateActivityTypesToChannels(\wpdb $wpdb, string $activityTable, string $channelTable): void
    {
        $uncategorizedId = self::lookupIdByName($wpdb, $channelTable, 'Uncategorized');
        $rows = $wpdb->get_results("SELECT id, type, channel_id FROM {$activityTable}", ARRAY_A) ?: [];
        $map = [];

        foreach ($rows as $row) {
            $type = trim((string) ($row['type'] ?? ''));
            if ($type === '') {
                continue;
            }
            if (!isset($map[$type])) {
                $category = self::classifyChannel($type);
                self::upsertChannel($wpdb, $channelTable, $type, $category, 0, 9999);
                $map[$type] = self::lookupIdByName($wpdb, $channelTable, $type);
            }

            if ((int) ($row['channel_id'] ?? 0) <= 0 && isset($map[$type])) {
                $wpdb->update($activityTable, ['channel_id' => $map[$type]], ['id' => (int) $row['id']], ['%d'], ['%d']);
            }
        }

        $wpdb->query($wpdb->prepare("UPDATE {$activityTable} SET channel_id = %d WHERE channel_id <= 0", $uncategorizedId));
    }

    private static function migrateResultTypesToCategories(\wpdb $wpdb, string $resultTable, string $categoryTable): void
    {
        $uncategorizedId = self::lookupIdByName($wpdb, $categoryTable, 'Uncategorized');
        $rows = $wpdb->get_results("SELECT id, type, result_category_id FROM {$resultTable}", ARRAY_A) ?: [];
        $map = ['lead' => 'Leads', 'booked' => 'Appointments', 'sale' => 'Sales'];

        foreach ($rows as $row) {
            $typeRaw = strtolower(trim((string) ($row['type'] ?? '')));
            $name = $map[$typeRaw] ?? ucfirst($typeRaw);
            if ($name === '') {
                $name = 'Uncategorized';
            }

            $categoryId = self::lookupIdByName($wpdb, $categoryTable, $name);
            if ($categoryId <= 0) {
                $wpdb->insert($categoryTable, [
                    'created_at' => gmdate('Y-m-d H:i:s'),
                    'name' => $name,
                    'is_active' => 1,
                    'sort_order' => 9999,
                ], ['%s', '%s', '%d', '%d']);
                $categoryId = (int) $wpdb->insert_id;
            }

            if ((int) ($row['result_category_id'] ?? 0) <= 0) {
                $wpdb->update($resultTable, ['result_category_id' => $categoryId], ['id' => (int) $row['id']], ['%d'], ['%d']);
            }
        }

        $wpdb->query($wpdb->prepare("UPDATE {$resultTable} SET result_category_id = %d WHERE result_category_id <= 0", $uncategorizedId));

        $uncategorizedChannelId = self::lookupIdByName($wpdb, $wpdb->prefix . 'mh_channel', 'Uncategorized');
        $wpdb->query($wpdb->prepare("UPDATE {$resultTable} SET channel_id = %d WHERE channel_id IS NULL", $uncategorizedChannelId));
    }

    private static function ensureCoreSettings(\wpdb $wpdb, string $settingsTable, string $categoryTable): void
    {
        self::setSettingIfMissing($wpdb, $settingsTable, 'team_time_cost_per_hour_cents', '0');
        self::setSettingIfMissing($wpdb, $settingsTable, 'owner_time_cost_per_hour_cents', '0');

        $kpi = $wpdb->get_var($wpdb->prepare("SELECT setting_key FROM {$settingsTable} WHERE setting_key = %s", 'kpi_tiles'));
        if ($kpi) {
            return;
        }

        $labels = ['Leads', 'Appointments', 'Sales', 'Reviews'];
        $tiles = [];
        foreach ($labels as $label) {
            $tiles[] = [
                'label' => $label,
                'result_category_id' => self::lookupIdByName($wpdb, $categoryTable, $label),
            ];
        }

        self::setSettingIfMissing($wpdb, $settingsTable, 'kpi_tiles', wp_json_encode($tiles));
    }

    private static function setSettingIfMissing(\wpdb $wpdb, string $table, string $key, string $value): void
    {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT setting_key FROM {$table} WHERE setting_key = %s", $key));
        if ($exists) {
            return;
        }

        $now = gmdate('Y-m-d H:i:s');
        $wpdb->insert($table, [
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => $now,
            'created_at' => $now,
        ], ['%s', '%s', '%s', '%s']);
    }

    private static function lookupIdByName(\wpdb $wpdb, string $table, string $name): int
    {
        return (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE name = %s LIMIT 1", $name));
    }

    private static function classifyChannel(string $type): string
    {
        $paidKeywords = ['ad', 'ppc', 'google', 'facebook', 'youtube', 'tv'];
        $normalized = strtolower($type);

        foreach ($paidKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return 'paid';
            }
        }

        return 'organic';
    }
}
