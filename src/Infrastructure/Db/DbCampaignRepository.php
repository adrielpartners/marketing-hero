<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\CampaignRepositoryInterface;

final class DbCampaignRepository extends AbstractDbRepository implements CampaignRepositoryInterface
{
    public function create(array $data): int
    {
        $ok = $this->wpdb->insert(
            $this->table('mh_campaign'),
            [
                'created_at' => gmdate('Y-m-d H:i:s'),
                'name' => (string) $data['name'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'meta_json' => $data['meta_json'] ?? null,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($ok === false) {
            return 0;
        }

        return (int) $this->wpdb->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $updated = $this->wpdb->update(
            $this->table('mh_campaign'),
            [
                'name' => (string) $data['name'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'meta_json' => $data['meta_json'] ?? null,
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public function delete(int $id): bool
    {
        $deleted = $this->wpdb->delete($this->table('mh_campaign'), ['id' => $id], ['%d']);

        return $deleted !== false;
    }

    public function find(int $id): ?array
    {
        $sql = $this->wpdb->prepare('SELECT * FROM ' . $this->table('mh_campaign') . ' WHERE id = %d', $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function listAll(): array
    {
        $sql = 'SELECT * FROM ' . $this->table('mh_campaign') . ' ORDER BY created_at DESC LIMIT 1000';
        $rows = $this->wpdb->get_results($sql, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }
}
