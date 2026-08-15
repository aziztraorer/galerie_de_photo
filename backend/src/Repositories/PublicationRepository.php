<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use PDO;

class PublicationRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                p.*,
                u.name AS user_name,
                u.email AS user_email
             FROM publications p
             INNER JOIN users u
                ON u.id = p.user_id
             ORDER BY p.id DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.*,
                u.name AS user_name,
                u.email AS user_email
             FROM publications p
             INNER JOIN users u
                ON u.id = p.user_id
             WHERE p.id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $publication = $stmt->fetch(PDO::FETCH_ASSOC);
        return $publication ?: null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM publications WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(
        int $userId,
        string $title,
        string $description,
        ?string $image
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO publications
                (user_id, title, description, image_url)
             VALUES
                (:user_id, :title, :description, :image_url)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'image_url' => $image
        ]);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    public function update(
        int $id,
        string $title,
        string $description,
        ?string $image = null
    ): bool {
        if ($image !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE publications
                 SET title = :title, description = :description, image_url = :image_url
                 WHERE id = :id'
            );
            return $stmt->execute([
                'title' => $title,
                'description' => $description,
                'image_url' => $image,
                'id' => $id
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE publications SET title = :title, description = :description WHERE id = :id'
        );
        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM publications WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM publications WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}