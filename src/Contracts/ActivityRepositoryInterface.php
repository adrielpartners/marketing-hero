<?php

declare(strict_types=1);

namespace MarketingHero\Contracts;

use MarketingHero\Services\DateRange;

interface ActivityRepositoryInterface
{
    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function find(int $id): ?array;

    public function list(DateRange $range, array $filters = []): array;
}
