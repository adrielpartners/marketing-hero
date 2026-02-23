<?php

declare(strict_types=1);

namespace MarketingHero\Contracts;

interface IntegrationProviderInterface
{
    public function key(): string;

    public function label(): string;

    public function isConfigured(): bool;
}
