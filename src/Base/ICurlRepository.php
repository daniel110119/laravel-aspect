<?php

declare(strict_types=1);

namespace Daniel\LaravelAspect\Base;

use Illuminate\Database\Eloquent\Collection;
use Daniel\LaravelAspect\Bean;
use Daniel\LaravelAspect\Optional;

interface ICurlRepository
{

    public function findById(int|string $id): Optional;

    public function existsById(int|string $id): bool;

    public function existsInIds(array $ids): bool;

    public function create(Bean $bean): int|string;

    public function batchCreate(array $records): void;

    public function createWithArray(array $data): int|string;

    public function remove(int|string $id): void;

    public function modify(int|string $id, Bean $params, array $excludes = []): void;

    public function modifyWithArray(int|string $id, array $params, array $excludes = []): void;

    public function findAll(): Collection;

    public function findByIds(array $ids): array;

    public function existsByFields(array $conditions): bool;
}
