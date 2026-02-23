<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\ResultRepositoryInterface;
use MarketingHero\Services\DateRange;

final class DbResultRepository extends AbstractDbRepository implements ResultRepositoryInterface
{
    public function create(array $data): int
    {
        $ok = $this->wpdb->insert($this->table('mh_result'), [
            'created_at' => gmdate('Y-m-d H:i:s'),
            'occurred_at' => (string) $data['occurred_at'],
            'result_category_id' => (int) $data['result_category_id'],
            'channel_id' => isset($data['channel_id']) ? (int) $data['channel_id'] : null,
            'value_cents' => isset($data['value_cents']) ? (int) $data['value_cents'] : null,
            'campaign_id' => isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            'source' => $data['source'] ?? null,
            'notes' => $data['notes'] ?? null,
            'meta_json' => $data['meta_json'] ?? null,
        ], ['%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s']);

        return $ok === false ? 0 : (int) $this->wpdb->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        return true;
    }

    public function delete(int $id): bool
    {
        return $this->wpdb->delete($this->table('mh_result'), ['id' => $id], ['%d']) !== false;
    }

    public function find(int $id): ?array
    {
        $sql = $this->wpdb->prepare('SELECT r.*, c.name AS campaign_name, ch.name AS channel_name, rc.name AS result_category_name
            FROM ' . $this->table('mh_result') . ' r
            LEFT JOIN ' . $this->table('mh_campaign') . ' c ON c.id = r.campaign_id
            LEFT JOIN ' . $this->table('mh_channel') . ' ch ON ch.id = r.channel_id
            LEFT JOIN ' . $this->table('mh_result_category') . ' rc ON rc.id = r.result_category_id
            WHERE r.id = %d', $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function list(DateRange $range, array $filters = []): array
    {
        $limit = isset($filters['limit']) ? max(1, min(500, (int) $filters['limit'])) : 50;
        $conditions = ['r.occurred_at >= %s', 'r.occurred_at <= %s'];
        $args = [$range->startUtc($this->utc), $range->endUtc($this->utc)];

        if (!empty($filters['campaign_id'])) {
            $conditions[] = 'r.campaign_id = %d';
            $args[] = (int) $filters['campaign_id'];
        }

        if (!empty($filters['result_category_id'])) {
            $conditions[] = 'r.result_category_id = %d';
            $args[] = (int) $filters['result_category_id'];
        }

        $args[] = $limit;

        $sql = 'SELECT r.*, c.name AS campaign_name, ch.name AS channel_name, rc.name AS result_category_name
            FROM ' . $this->table('mh_result') . ' r
            LEFT JOIN ' . $this->table('mh_campaign') . ' c ON c.id = r.campaign_id
            LEFT JOIN ' . $this->table('mh_channel') . ' ch ON ch.id = r.channel_id
            LEFT JOIN ' . $this->table('mh_result_category') . ' rc ON rc.id = r.result_category_id
            WHERE ' . implode(' AND ', $conditions) . ' ORDER BY r.occurred_at DESC LIMIT %d';
        $rows = $this->wpdb->get_results($this->wpdb->prepare($sql, ...$args), ARRAY_A);

        return is_array($rows) ? $rows : [];
    }
}
