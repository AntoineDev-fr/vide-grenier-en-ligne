<?php

namespace App\Services;

use App\Auth;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Utility\Hash;

class AuthenticationService
{
    private UserRepositoryInterface $users;
    private $loginHandler;

    public function __construct(UserRepositoryInterface $users = null, callable $loginHandler = null)
    {
        $this->users = $users ?: new UserRepository();
        $this->loginHandler = $loginHandler ?: [Auth::class, 'login'];
    }

    public function attempt(array $credentials): bool
    {
        if (empty($credentials['email']) || !array_key_exists('password', $credentials)) {
            return false;
        }

        $user = $this->users->findByEmail($credentials['email']);

        if (!$user) {
            return false;
        }

        if (Hash::generate($credentials['password'], $user['salt']) !== $user['password']) {
            return false;
        }

        call_user_func($this->loginHandler, $user, !empty($credentials['remember_me']));

        return true;
    }
}
