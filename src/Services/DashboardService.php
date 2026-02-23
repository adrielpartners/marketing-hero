<?php

declare(strict_types=1);

namespace MarketingHero\Services;

use MarketingHero\Contracts\ActivityRepositoryInterface;
use MarketingHero\Contracts\ResultCategoryRepositoryInterface;
use MarketingHero\Contracts\ResultRepositoryInterface;
use MarketingHero\Contracts\SettingsRepositoryInterface;

final class DashboardService
{
    public function __construct(
        private ActivityRepositoryInterface $activityRepository,
        private ResultRepositoryInterface $resultRepository,
        private SettingsRepositoryInterface $settingsRepository,
        private ResultCategoryRepositoryInterface $resultCategoryRepository
    ) {
    }

    public function getKpis(DateRange $range): array
    {
        $results = $this->resultRepository->list($range, ['limit' => 1000]);
        $tiles = $this->settingsRepository->get('kpi_tiles', []);
        $counts = [];
        $revenue = 0;
        $salesCount = 0;

        foreach ($results as $result) {
            $categoryId = (int) ($result['result_category_id'] ?? 0);
            $counts[$categoryId] = ($counts[$categoryId] ?? 0) + 1;

            if (strtolower((string) ($result['result_category_name'] ?? '')) === 'sales') {
                $salesCount++;
                $revenue += (int) ($result['value_cents'] ?? 0);
            }
        }

        $kpiTiles = [];
        foreach (is_array($tiles) ? array_slice($tiles, 0, 4) : [] as $tile) {
            $categoryId = (int) ($tile['result_category_id'] ?? 0);
            $kpiTiles[] = ['label' => (string) ($tile['label'] ?? 'KPI'), 'count' => (int) ($counts[$categoryId] ?? 0)];
        }

        $aov = $salesCount > 0 ? (int) floor($revenue / $salesCount) : 0;

        return [
            'tiles' => $kpiTiles,
            'sales_count' => $salesCount,
            'revenue_cents' => $revenue,
            'aov_cents' => $aov,
        ];
    }

    public function getInputsSummary(DateRange $range): array
    {
        $activities = $this->activityRepository->list($range, ['limit' => 500]);
        $byChannel = [];
        $cash = 0;
        $teamMinutes = 0;
        $ownerMinutes = 0;

        foreach ($activities as $activity) {
            $channel = (string) ($activity['channel_name'] ?? 'Uncategorized');
            $quantity = (int) ($activity['quantity'] ?? 0);
            $cashCost = (int) ($activity['cash_investment_cents'] ?? 0);

            $byChannel[$channel] = $byChannel[$channel] ?? ['quantity' => 0, 'cash_cents' => 0];
            $byChannel[$channel]['quantity'] += $quantity;
            $byChannel[$channel]['cash_cents'] += $cashCost;

            $cash += $cashCost;
            $teamMinutes += (int) ($activity['team_time_minutes'] ?? 0);
            $ownerMinutes += (int) ($activity['owner_time_minutes'] ?? 0);
        }

        $teamRate = (int) $this->settingsRepository->get('team_time_cost_per_hour_cents', '0');
        $ownerRate = (int) $this->settingsRepository->get('owner_time_cost_per_hour_cents', '0');
        $teamCost = (int) round(($teamMinutes / 60) * $teamRate);
        $ownerCost = (int) round(($ownerMinutes / 60) * $ownerRate);

        return [
            'recent' => $activities,
            'totals_by_channel' => $byChannel,
            'cash_cents' => $cash,
            'team_minutes' => $teamMinutes,
            'owner_minutes' => $ownerMinutes,
            'team_cost_cents' => $teamCost,
            'owner_cost_cents' => $ownerCost,
            'total_investment_cents' => $cash + $teamCost + $ownerCost,
        ];
    }

    public function getOutputsSummary(DateRange $range): array
    {
        $results = $this->resultRepository->list($range, ['limit' => 500]);
        $byCategory = [];
        $byChannel = [];
        $revenue = 0;

        foreach ($results as $result) {
            $category = (string) ($result['result_category_name'] ?? 'Uncategorized');
            $channel = (string) ($result['channel_name'] ?? 'Uncategorized');

            $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;
            $byChannel[$channel] = ($byChannel[$channel] ?? 0) + 1;
            $revenue += (int) ($result['value_cents'] ?? 0);
        }

        return [
            'recent' => $results,
            'totals_by_category' => $byCategory,
            'totals_by_channel' => $byChannel,
            'revenue_cents' => $revenue,
        ];
    }
}
