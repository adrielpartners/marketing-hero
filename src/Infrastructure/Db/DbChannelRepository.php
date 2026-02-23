<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\ChannelRepositoryInterface;

final class DbChannelRepository extends AbstractDbRepository implements ChannelRepositoryInterface
{
    public function listAll(): array
    {
        $rows = $this->wpdb->get_results('SELECT * FROM ' . $this->table('mh_channel') . " ORDER BY category ASC, sort_order ASC, name ASC", ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    public function listGrouped(): array
    {
        $grouped = ['organic' => [], 'paid' => []];
        foreach ($this->listAll() as $row) {
            $category = ($row['category'] ?? 'organic') === 'paid' ? 'paid' : 'organic';
            $grouped[$category][] = $row;
        }

        return $grouped;
    }

    public function create(string $name, string $category): int
    {
        $ok = $this->wpdb->insert($this->table('mh_channel'), [
            'created_at' => gmdate('Y-m-d H:i:s'),
            'name' => $name,
            'category' => $category === 'paid' ? 'paid' : 'organic',
            'is_active' => 1,
            'sort_order' => 9999,
        ], ['%s', '%s', '%s', '%d', '%d']);

        return $ok === false ? 0 : (int) $this->wpdb->insert_id;
    }

    public function update(int $id, string $name, string $category): bool
    {
        $updated = $this->wpdb->update($this->table('mh_channel'), ['name' => $name, 'category' => $category === 'paid' ? 'paid' : 'organic'], ['id' => $id], ['%s', '%s'], ['%d']);
        return $updated !== false;
    }

    public function toggle(int $id, bool $isActive): bool
    {
        $updated = $this->wpdb->update($this->table('mh_channel'), ['is_active' => $isActive ? 1 : 0], ['id' => $id], ['%d'], ['%d']);
        return $updated !== false;
    }

    public function deleteAndReassignToUncategorized(int $id): bool
    {
        $uncategorizedId = $this->getUncategorizedId();
        if ($id === $uncategorizedId || $id <= 0) {
            return false;
        }

        $this->wpdb->update($this->table('mh_activity'), ['channel_id' => $uncategorizedId], ['channel_id' => $id], ['%d'], ['%d']);
        $this->wpdb->update($this->table('mh_result'), ['channel_id' => $uncategorizedId], ['channel_id' => $id], ['%d'], ['%d']);

        $deleted = $this->wpdb->delete($this->table('mh_channel'), ['id' => $id], ['%d']);
        return $deleted !== false;
    }

    public function getUncategorizedId(): int
    {
        return (int) $this->wpdb->get_var("SELECT id FROM {$this->table('mh_channel')} WHERE system_code = 99 ORDER BY id ASC LIMIT 1");
    }
}
