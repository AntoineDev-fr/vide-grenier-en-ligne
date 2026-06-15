<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\UserRepositoryInterface;
use App\Services\RegistrationService;
use App\Utility\Hash;
use DomainException;
use InvalidArgumentException;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    public function testRegistrationCreatesAUserWithHashedPassword(): void
    {
        $repository = $this->makeRepository();
        $service = new RegistrationService($repository, function (): string {
            return str_repeat('s', 32);
        });

        $userId = $service->register([
            'username' => 'antoine',
            'email' => 'antoine@example.com',
            'password' => 'secret',
            'password-check' => 'secret'
        ]);

        $storedUser = $repository->findByEmail('antoine@example.com');

        $this->assertSame(1, $userId);
        $this->assertNotNull($storedUser);
        $this->assertSame('antoine', $storedUser['username']);
        $this->assertSame(str_repeat('s', 32), $storedUser['salt']);
        $this->assertSame(Hash::generate('secret', str_repeat('s', 32)), $storedUser['password']);
    }

    public function testRegistrationRejectsAnExistingEmail(): void
    {
        $repository = $this->makeRepository([
            [
                'id' => 1,
                'username' => 'existing-user',
                'email' => 'antoine@example.com',
                'password' => 'hashed-password',
                'salt' => 'salt'
            ]
        ]);

        $service = new RegistrationService($repository);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Un compte existe deja avec cet email.');

        $service->register([
            'username' => 'antoine',
            'email' => 'antoine@example.com',
            'password' => 'secret',
            'password-check' => 'secret'
        ]);
    }

    public function testRegistrationRejectsDifferentPasswords(): void
    {
        $service = new RegistrationService($this->makeRepository());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Les mots de passe ne correspondent pas.');

        $service->register([
            'username' => 'antoine',
            'email' => 'antoine@example.com',
            'password' => 'secret',
            'password-check' => 'different'
        ]);
    }

    private function makeRepository(array $seedUsers = []): UserRepositoryInterface
    {
        return new class($seedUsers) implements UserRepositoryInterface {
            private array $users;
            private int $nextId;

            public function __construct(array $seedUsers)
            {
                $this->users = $seedUsers;
                $this->nextId = empty($seedUsers) ? 1 : max(array_column($seedUsers, 'id')) + 1;
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
                $data['id'] = $this->nextId++;
                $this->users[] = $data;

                return $data['id'];
            }
        };
    }
}
