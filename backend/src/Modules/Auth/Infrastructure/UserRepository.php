<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        return $this->hydrateUser($statement->fetch() ?: null);
    }

    public function findById(int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);

        return $this->hydrateUser($statement->fetch() ?: null);
    }

    public function create(string $name, string $email, string $passwordHash, string $role = 'user'): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)'
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        return $this->findById((int) $this->pdo->lastInsertId()) ?? [];
    }

    private function hydrateUser(?array $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $user['user_id'] = (int) $user['user_id'];

        return $user;
    }
}
