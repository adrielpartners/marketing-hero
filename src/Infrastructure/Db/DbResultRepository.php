<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\ResultRepositoryInterface;
use MarketingHero\Services\DateRange;

final class DbResultRepository extends AbstractDbRepository implements ResultRepositoryInterface
{
    public function create(array $data): int
    {
        $ok = $this->wpdb->insert(
            $this->table('mh_result'),
            [
                'created_at' => gmdate('Y-m-d H:i:s'),
                'occurred_at' => (string) $data['occurred_at'],
                'type' => (string) $data['type'],
                'value_cents' => isset($data['value_cents']) ? (int) $data['value_cents'] : null,
                'campaign_id' => isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
                'source' => $data['source'] ?? null,
                'notes' => $data['notes'] ?? null,
                'meta_json' => $data['meta_json'] ?? null,
            ],
            ['%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        if ($ok === false) {
            return 0;
        }

        return (int) $this->wpdb->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $updated = $this->wpdb->update(
            $this->table('mh_result'),
            [
                'occurred_at' => (string) $data['occurred_at'],
                'type' => (string) $data['type'],
                'value_cents' => isset($data['value_cents']) ? (int) $data['value_cents'] : null,
                'campaign_id' => isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
                'source' => $data['source'] ?? null,
                'notes' => $data['notes'] ?? null,
                'meta_json' => $data['meta_json'] ?? null,
            ],
            ['id' => $id],
            ['%s', '%s', '%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public function delete(int $id): bool
    {
        $deleted = $this->wpdb->delete($this->table('mh_result'), ['id' => $id], ['%d']);

        return $deleted !== false;
    }

    public function find(int $id): ?array
    {
        $sql = $this->wpdb->prepare('SELECT * FROM ' . $this->table('mh_result') . ' WHERE id = %d', $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function list(DateRange $range, array $filters = []): array
    {
        $limit = isset($filters['limit']) ? max(1, min(500, (int) $filters['limit'])) : 50;
        $conditions = ['occurred_at >= %s', 'occurred_at <= %s'];
        $args = [$range->startUtc($this->utc), $range->endUtc($this->utc)];

        if (!empty($filters['type'])) {
            $conditions[] = 'type = %s';
            $args[] = (string) $filters['type'];
        }

        if (!empty($filters['campaign_id'])) {
            $conditions[] = 'campaign_id = %d';
            $args[] = (int) $filters['campaign_id'];
        }

        if (!empty($filters['source'])) {
            $conditions[] = 'source = %s';
            $args[] = (string) $filters['source'];
        }

        $args[] = $limit;

        $sql = 'SELECT * FROM ' . $this->table('mh_result') . ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY occurred_at DESC LIMIT %d';
        $prepared = $this->wpdb->prepare($sql, ...$args);
        $rows = $this->wpdb->get_results($prepared, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }
}
