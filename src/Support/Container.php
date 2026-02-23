<?php

declare(strict_types=1);

namespace MarketingHero\Support;

use MarketingHero\Contracts\ActivityRepositoryInterface;
use MarketingHero\Contracts\CampaignRepositoryInterface;
use MarketingHero\Contracts\ResultRepositoryInterface;
use MarketingHero\Infrastructure\Db\DbActivityRepository;
use MarketingHero\Infrastructure\Db\DbCampaignRepository;
use MarketingHero\Infrastructure\Db\DbResultRepository;
use MarketingHero\Services\DashboardService;

final class Container
{
    private array $instances = [];

    public function activities(): ActivityRepositoryInterface
    {
        return $this->instances[ActivityRepositoryInterface::class] ??= new DbActivityRepository();
    }

    public function results(): ResultRepositoryInterface
    {
        return $this->instances[ResultRepositoryInterface::class] ??= new DbResultRepository();
    }

    public function campaigns(): CampaignRepositoryInterface
    {
        return $this->instances[CampaignRepositoryInterface::class] ??= new DbCampaignRepository();
    }

    public function dashboard(): DashboardService
    {
        return $this->instances[DashboardService::class] ??= new DashboardService($this->activities(), $this->results());
    }
}
