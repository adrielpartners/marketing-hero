<?php

declare(strict_types=1);

namespace MarketingHero\Support;

use MarketingHero\Contracts\ActivityRepositoryInterface;
use MarketingHero\Contracts\CampaignRepositoryInterface;
use MarketingHero\Contracts\ChannelRepositoryInterface;
use MarketingHero\Contracts\ResultCategoryRepositoryInterface;
use MarketingHero\Contracts\ResultRepositoryInterface;
use MarketingHero\Contracts\SettingsRepositoryInterface;
use MarketingHero\Infrastructure\Db\DbActivityRepository;
use MarketingHero\Infrastructure\Db\DbCampaignRepository;
use MarketingHero\Infrastructure\Db\DbChannelRepository;
use MarketingHero\Infrastructure\Db\DbResultCategoryRepository;
use MarketingHero\Infrastructure\Db\DbResultRepository;
use MarketingHero\Infrastructure\Db\DbSettingsRepository;
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

    public function channels(): ChannelRepositoryInterface
    {
        return $this->instances[ChannelRepositoryInterface::class] ??= new DbChannelRepository();
    }

    public function resultCategories(): ResultCategoryRepositoryInterface
    {
        return $this->instances[ResultCategoryRepositoryInterface::class] ??= new DbResultCategoryRepository();
    }

    public function settings(): SettingsRepositoryInterface
    {
        return $this->instances[SettingsRepositoryInterface::class] ??= new DbSettingsRepository();
    }

    public function dashboard(): DashboardService
    {
        return $this->instances[DashboardService::class] ??= new DashboardService(
            $this->activities(),
            $this->results(),
            $this->settings(),
            $this->resultCategories()
        );
    }
}
