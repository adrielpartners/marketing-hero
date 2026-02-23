<?php

declare(strict_types=1);

namespace MarketingHero\Contracts;

interface SyncClientInterface
{
    public function push(array $payload): bool;

    public function pull(array $context = []): array;
}
