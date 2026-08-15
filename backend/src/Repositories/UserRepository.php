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
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, "user")'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $user = $this->findById($id);

        if ($user === null) {
            throw new \RuntimeException('User creation failed.');
        }

        return $user;
    }

    public function updatePassword(int $userId, string $password): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );
        return $stmt->execute(['password' => $password, 'id' => $userId]);
    }

    public function updateAvatar(int $userId, ?string $avatarUrl): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET avatar_url = :avatar_url WHERE id = :id'
        );
        return $stmt->execute(['avatar_url' => $avatarUrl, 'id' => $userId]);
    }

    public function updateLastActivity(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE users SET last_activity = NOW() WHERE id = :id'
            );
            return $stmt->execute(['id' => $userId]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllWithActivity(): array
    {
        try {
            $stmt = $this->pdo->query(
                'SELECT 
                    u.*,
                    TIMESTAMPDIFF(SECOND, u.last_activity, NOW()) AS seconds_since_activity,
                    CASE 
                        WHEN u.last_activity IS NOT NULL 
                        AND TIMESTAMPDIFF(SECOND, u.last_activity, NOW()) < 300 
                        THEN 1 
                        ELSE 0 
                    END AS is_online
                 FROM users u 
                 ORDER BY u.name ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return $this->findAll();
        }
    }

    public function getOnlineUsers(int $minutes = 5): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM users 
                 WHERE last_activity IS NOT NULL 
                 AND TIMESTAMPDIFF(SECOND, last_activity, NOW()) < :seconds
                 ORDER BY last_activity DESC'
            );
            $stmt->execute(['seconds' => $minutes * 60]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getOnlineCount(int $minutes = 5): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM users 
                 WHERE last_activity IS NOT NULL 
                 AND TIMESTAMPDIFF(SECOND, last_activity, NOW()) < :seconds'
            );
            $stmt->execute(['seconds' => $minutes * 60]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM users WHERE id = :id AND role != "admin"'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // GESTION DES TENTATIVES DE CONNEXION
    public function getLockStatus(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT 
                is_locked,
                locked_until,
                login_attempts
             FROM users 
             WHERE email = :email 
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function incrementLoginAttempt(string $email): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users 
             SET login_attempts = login_attempts + 1,
                 last_login_attempt = NOW()
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        
        $stmt = $this->pdo->prepare(
            'SELECT login_attempts FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return (int) $stmt->fetchColumn();
    }

    public function resetLoginAttempts(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users 
             SET login_attempts = 0,
                 last_login_attempt = NULL,
                 locked_until = NULL,
                 is_locked = FALSE
             WHERE email = :email'
        );
        return $stmt->execute(['email' => $email]);
    }

    public function lockAccount(string $email, int $minutes): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users 
             SET is_locked = TRUE,
                 locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                 login_attempts = 0
             WHERE email = :email'
        );
        return $stmt->execute([
            'email' => $email,
            'minutes' => $minutes
        ]);
    }

    public function unlockAccount(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users 
             SET is_locked = FALSE,
                 locked_until = NULL,
                 login_attempts = 0,
                 last_login_attempt = NULL
             WHERE email = :email'
        );
        return $stmt->execute(['email' => $email]);
    }
}