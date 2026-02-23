<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\SettingsRepositoryInterface;

final class DbSettingsRepository extends AbstractDbRepository implements SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->wpdb->get_var($this->wpdb->prepare('SELECT setting_value FROM ' . $this->table('mh_settings') . ' WHERE setting_key = %s', $key));
        if ($value === null) {
            return $default;
        }

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public function set(string $key, mixed $value): void
    {
        $encoded = is_array($value) || is_object($value) ? wp_json_encode($value) : (string) $value;
        $now = gmdate('Y-m-d H:i:s');

        $exists = $this->wpdb->get_var($this->wpdb->prepare('SELECT setting_key FROM ' . $this->table('mh_settings') . ' WHERE setting_key = %s', $key));
        if ($exists) {
            $this->wpdb->update($this->table('mh_settings'), ['setting_value' => $encoded, 'updated_at' => $now], ['setting_key' => $key], ['%s', '%s'], ['%s']);
            return;
        }

        $this->wpdb->insert($this->table('mh_settings'), ['setting_key' => $key, 'setting_value' => $encoded, 'updated_at' => $now, 'created_at' => $now], ['%s', '%s', '%s', '%s']);
    }
}
