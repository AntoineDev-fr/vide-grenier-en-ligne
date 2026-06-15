<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepositoryInterface;
use App\Services\AuthenticationService;
use App\Utility\Hash;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    public function testAuthenticationSucceedsWithCorrectPassword(): void
    {
        $user = [
            'id' => 12,
            'username' => 'antoine',
            'email' => 'antoine@example.com',
            'salt' => 'salt123',
            'password' => Hash::generate('secret', 'salt123')
        ];

        $loggedInUser = null;
        $rememberMe = null;

        $service = new AuthenticationService(
            $this->makeRepository([$user]),
            function (array $authenticatedUser, bool $remember) use (&$loggedInUser, &$rememberMe): void {
                $loggedInUser = $authenticatedUser;
                $rememberMe = $remember;
            }
        );

        $result = $service->attempt([
            'email' => 'antoine@example.com',
            'password' => 'secret',
            'remember_me' => '1'
        ]);

        $this->assertTrue($result);
        $this->assertSame($user, $loggedInUser);
        $this->assertTrue($rememberMe);
    }

    public function testAuthenticationFailsWithIncorrectPassword(): void
    {
        $user = [
            'id' => 12,
            'username' => 'antoine',
            'email' => 'antoine@example.com',
            'salt' => 'salt123',
            'password' => Hash::generate('secret', 'salt123')
        ];

        $loginCalled = false;

        $service = new AuthenticationService(
            $this->makeRepository([$user]),
            function () use (&$loginCalled): void {
                $loginCalled = true;
            }
        );

        $result = $service->attempt([
            'email' => 'antoine@example.com',
            'password' => 'wrong-password'
        ]);

        $this->assertFalse($result);
        $this->assertFalse($loginCalled);
    }

    public function testAuthenticationFailsWhenUserDoesNotExist(): void
    {
        $loginCalled = false;

        $service = new AuthenticationService(
            $this->makeRepository([]),
            function () use (&$loginCalled): void {
                $loginCalled = true;
            }
        );

        $result = $service->attempt([
            'email' => 'missing@example.com',
            'password' => 'secret'
        ]);

        $this->assertFalse($result);
        $this->assertFalse($loginCalled);
    }

    private function makeRepository(array $users): UserRepositoryInterface
    {
        return new class($users) implements UserRepositoryInterface {
            private array $users;

            public function __construct(array $users)
            {
                $this->users = $users;
            }

            public function findByEmail(string $email): ?array
            {
                foreach ($this->users as $user) {
                    if ($user['email'] === $email) {
                        return $user;
                    }
                }

                return null;
            }

            public function findById(int $id): ?array
            {
                foreach ($this->users as $user) {
                    if ($user['id'] === $id) {
                        return $user;
                    }
                }

                return null;
            }

            public function create(array $data): int
            {
                throw new \BadMethodCallException('create() is not used in this test.');
            }
        };
    }
}
