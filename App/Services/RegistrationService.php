<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Utility\Hash;
use DomainException;
use InvalidArgumentException;

class RegistrationService
{
    private UserRepositoryInterface $users;
    private $saltGenerator;

    public function __construct(UserRepositoryInterface $users = null, callable $saltGenerator = null)
    {
        $this->users = $users ?: new UserRepository();
        $this->saltGenerator = $saltGenerator ?: [Hash::class, 'generateSalt'];
    }

    public function register(array $data): int
    {
        if (($data['password'] ?? null) !== ($data['password-check'] ?? null)) {
            throw new InvalidArgumentException('Les mots de passe ne correspondent pas.');
        }

        if (empty($data['username']) || empty($data['email']) || !isset($data['password'])) {
            throw new InvalidArgumentException('Informations d\'inscription invalides.');
        }

        if ($this->users->findByEmail($data['email'])) {
            throw new DomainException('Un compte existe deja avec cet email.');
        }

        $salt = call_user_func($this->saltGenerator, 32);

        return $this->users->create([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::generate($data['password'], $salt),
            'salt' => $salt
        ]);
    }
}
