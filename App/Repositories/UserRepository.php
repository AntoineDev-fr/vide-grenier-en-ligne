<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?array
    {
        $user = User::getByLogin($email);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $user = User::getById($id);

        return $user ?: null;
    }

    public function create(array $data): int
    {
        return (int) User::createUser($data);
    }
}
