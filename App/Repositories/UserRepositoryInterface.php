<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?array;

    public function findById(int $id): ?array;

    public function create(array $data): int;
}
