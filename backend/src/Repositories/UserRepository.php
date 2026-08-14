<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo =
            $pdo ??
            Database::getInstance()->getPdo();
    }

    public function findByEmail(
        string $email
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $row =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(
        array $data
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users
            (name, email, password)
            VALUES
            (:name, :email, :password)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        $id =
            (int) $this->pdo->lastInsertId();

        $user = $this->findById($id);

        if ($user === null) {
            throw new \RuntimeException(
                'User creation failed.'
            );
        }

        return $user;
    }

    public function findById(
        int $id
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $user =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function updatePassword(
        int $userId,
        string $password
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET password = :password
             WHERE id = :id'
        );

        return $stmt->execute([
            'password' => $password,
            'id' => $userId
        ]);
    }
}