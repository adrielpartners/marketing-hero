<?php

declare(strict_types=1);

namespace MarketingHero\Infrastructure\Db;

use MarketingHero\Contracts\ActivityRepositoryInterface;
use MarketingHero\Services\DateRange;

final class DbActivityRepository extends AbstractDbRepository implements ActivityRepositoryInterface
{
    public function create(array $data): int
    {
        $ok = $this->wpdb->insert($this->table('mh_activity'), [
            'created_at' => gmdate('Y-m-d H:i:s'),
            'occurred_at' => (string) $data['occurred_at'],
            'channel_id' => (int) $data['channel_id'],
            'quantity' => (int) $data['quantity'],
            'cash_investment_cents' => isset($data['cash_investment_cents']) ? (int) $data['cash_investment_cents'] : null,
            'team_time_minutes' => (int) ($data['team_time_minutes'] ?? 0),
            'owner_time_minutes' => (int) ($data['owner_time_minutes'] ?? 0),
            'campaign_id' => isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            'source' => $data['source'] ?? null,
            'notes' => $data['notes'] ?? null,
            'meta_json' => $data['meta_json'] ?? null,
        ], ['%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s']);

        return $ok === false ? 0 : (int) $this->wpdb->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        return true;
    }

    public function delete(int $id): bool
    {
        return $this->wpdb->delete($this->table('mh_activity'), ['id' => $id], ['%d']) !== false;
    }

    public function find(int $id): ?array
    {
        $sql = $this->wpdb->prepare('SELECT a.*, c.name AS campaign_name, ch.name AS channel_name, ch.category AS channel_category
            FROM ' . $this->table('mh_activity') . ' a
            LEFT JOIN ' . $this->table('mh_campaign') . ' c ON c.id = a.campaign_id
            LEFT JOIN ' . $this->table('mh_channel') . ' ch ON ch.id = a.channel_id
            WHERE a.id = %d', $id);
        $row = $this->wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }

    public function list(DateRange $range, array $filters = []): array
    {
        $limit = isset($filters['limit']) ? max(1, min(500, (int) $filters['limit'])) : 50;
        $conditions = ['a.occurred_at >= %s', 'a.occurred_at <= %s'];
        $args = [$range->startUtc($this->utc), $range->endUtc($this->utc)];

        if (!empty($filters['campaign_id'])) {
            $conditions[] = 'a.campaign_id = %d';
            $args[] = (int) $filters['campaign_id'];
        }

        if (!empty($filters['channel_id'])) {
            $conditions[] = 'a.channel_id = %d';
            $args[] = (int) $filters['channel_id'];
        }

        $args[] = $limit;

        $sql = 'SELECT a.*, c.name AS campaign_name, ch.name AS channel_name, ch.category AS channel_category
            FROM ' . $this->table('mh_activity') . ' a
            LEFT JOIN ' . $this->table('mh_campaign') . ' c ON c.id = a.campaign_id
            LEFT JOIN ' . $this->table('mh_channel') . ' ch ON ch.id = a.channel_id
            WHERE ' . implode(' AND ', $conditions) . ' ORDER BY a.occurred_at DESC LIMIT %d';
        $rows = $this->wpdb->get_results($this->wpdb->prepare($sql, ...$args), ARRAY_A);

        return is_array($rows) ? $rows : [];
    }
}
