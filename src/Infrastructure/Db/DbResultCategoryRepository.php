<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\ResultCategoryRepositoryInterface;

final class DbResultCategoryRepository extends AbstractDbRepository implements ResultCategoryRepositoryInterface
{
    public function listAll(): array
    {
        $rows = $this->wpdb->get_results('SELECT * FROM ' . $this->table('mh_result_category') . ' ORDER BY sort_order ASC, name ASC', ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    public function create(string $name): int
    {
        $ok = $this->wpdb->insert($this->table('mh_result_category'), [
            'created_at' => gmdate('Y-m-d H:i:s'),
            'name' => $name,
            'is_active' => 1,
            'sort_order' => 9999,
        ], ['%s', '%s', '%d', '%d']);

        return $ok === false ? 0 : (int) $this->wpdb->insert_id;
    }

    public function update(int $id, string $name): bool
    {
        $updated = $this->wpdb->update($this->table('mh_result_category'), ['name' => $name], ['id' => $id], ['%s'], ['%d']);
        return $updated !== false;
    }

    public function toggle(int $id, bool $isActive): bool
    {
        $updated = $this->wpdb->update($this->table('mh_result_category'), ['is_active' => $isActive ? 1 : 0], ['id' => $id], ['%d'], ['%d']);
        return $updated !== false;
    }

    public function deleteAndReassignToUncategorized(int $id): bool
    {
        $uncategorizedId = $this->getUncategorizedId();
        if ($id === $uncategorizedId || $id <= 0) {
            return false;
        }

        $this->wpdb->update($this->table('mh_result'), ['result_category_id' => $uncategorizedId], ['result_category_id' => $id], ['%d'], ['%d']);
        $deleted = $this->wpdb->delete($this->table('mh_result_category'), ['id' => $id], ['%d']);

        if ($deleted !== false) {
            $settings = new DbSettingsRepository($this->wpdb);
            $tiles = $settings->get('kpi_tiles', []);
            if (is_array($tiles)) {
                foreach ($tiles as &$tile) {
                    if ((int) ($tile['result_category_id'] ?? 0) === $id) {
                        $tile['result_category_id'] = $uncategorizedId;
                        $tile['label'] = 'Uncategorized';
                    }
                }
                $settings->set('kpi_tiles', $tiles);
            }
        }

        return $deleted !== false;
    }

    public function getUncategorizedId(): int
    {
        return (int) $this->wpdb->get_var("SELECT id FROM {$this->table('mh_result_category')} WHERE system_code = 99 ORDER BY id ASC LIMIT 1");
    }
}
