<?php

declare(strict_types=1);

namespace MarketingHero\Contracts;

interface ResultCategoryRepositoryInterface
{
    public function listAll(): array;

    public function create(string $name): int;

    public function update(int $id, string $name): bool;

    public function toggle(int $id, bool $isActive): bool;

    public function deleteAndReassignToUncategorized(int $id): bool;

    public function getUncategorizedId(): int;
}
