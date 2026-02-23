<?php

declare(strict_types=1);

namespace MarketingHero\Services;

use MarketingHero\Contracts\ActivityRepositoryInterface;
use MarketingHero\Contracts\ResultRepositoryInterface;

final class DashboardService
{
    public function __construct(
        private ActivityRepositoryInterface $activityRepository,
        private ResultRepositoryInterface $resultRepository
    ) {
    }

    public function getKpis(DateRange $range): array
    {
        $results = $this->resultRepository->list($range, ['limit' => 500]);

        $leads = 0;
        $booked = 0;
        $salesCount = 0;
        $revenue = 0;

        foreach ($results as $result) {
            $type = $result['type'] ?? '';

            if ($type === 'lead') {
                $leads++;
            }
            if ($type === 'booked') {
                $booked++;
            }
            if ($type === 'sale') {
                $salesCount++;
                $revenue += (int) ($result['value_cents'] ?? 0);
            }
        }

        $aov = $salesCount > 0 ? (int) floor($revenue / $salesCount) : 0;

        return [
            'leads' => $leads,
            'booked' => $booked,
            'sales_count' => $salesCount,
            'revenue_cents' => $revenue,
            'aov_cents' => $aov,
        ];
    }

    public function getInputsSummary(DateRange $range): array
    {
        $activities = $this->activityRepository->list($range, ['limit' => 100]);
        $byType = [];
        $totalSpend = 0;

        foreach ($activities as $activity) {
            $type = $activity['type'] ?? 'unknown';
            $quantity = (int) ($activity['quantity'] ?? 0);
            $cost = (int) ($activity['cost_cents'] ?? 0);

            if (!isset($byType[$type])) {
                $byType[$type] = ['quantity' => 0, 'spend_cents' => 0];
            }

            $byType[$type]['quantity'] += $quantity;
            $byType[$type]['spend_cents'] += $cost;
            $totalSpend += $cost;
        }

        ksort($byType);

        return [
            'recent' => $activities,
            'totals_by_type' => $byType,
            'spend_cents' => $totalSpend,
        ];
    }

    public function getOutputsSummary(DateRange $range): array
    {
        $results = $this->resultRepository->list($range, ['limit' => 100]);
        $byType = [];
        $bySource = [];

        foreach ($results as $result) {
            $type = $result['type'] ?? 'unknown';
            $source = trim((string) ($result['source'] ?? ''));

            if (!isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;

            if ($source !== '') {
                if (!isset($bySource[$source])) {
                    $bySource[$source] = 0;
                }
                $bySource[$source]++;
            }
        }

        ksort($byType);
        ksort($bySource);

        return [
            'recent' => $results,
            'totals_by_type' => $byType,
            'totals_by_source' => $bySource,
        ];
    }
}
