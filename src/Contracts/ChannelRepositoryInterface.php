<?php

declare(strict_types=1);

namespace MarketingHero\Contracts;

interface ChannelRepositoryInterface
{
    public function listAll(): array;

    public function listGrouped(): array;

    public function create(string $name, string $category): int;

    public function update(int $id, string $name, string $category): bool;

    public function toggle(int $id, bool $isActive): bool;

    public function deleteAndReassignToUncategorized(int $id): bool;

    public function getUncategorizedId(): int;
}
