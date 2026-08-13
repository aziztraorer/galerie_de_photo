<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class ImageRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo =
            $pdo ??
            Database::getInstance()->getPdo();
    }

    public function listByUser(
        int $userId
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                user_id,
                title,
                image_url,
                created_at,
                updated_at
             FROM images
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function findById(
        int $id
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                user_id,
                title,
                image_url,
                created_at,
                updated_at
             FROM images
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $image =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $image ?: null;
    }

    public function create(
        int $userId,
        string $title,
        string $imageUrl
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO images
                (user_id, title, image_url)
             VALUES
                (:user_id, :title, :image_url)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'image_url' => $imageUrl,
        ]);

        return $this->findById(
            (int) $this->pdo->lastInsertId()
        );
    }

    public function update(
        int $id,
        int $userId,
        string $title,
        ?string $imageUrl = null
    ): ?array {
        if ($imageUrl !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE images
                 SET
                    title = :title,
                    image_url = :image_url
                 WHERE
                    id = :id
                    AND user_id = :user_id'
            );

            $stmt->execute([
                'title' => $title,
                'image_url' => $imageUrl,
                'id' => $id,
                'user_id' => $userId,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE images
                 SET title = :title
                 WHERE
                    id = :id
                    AND user_id = :user_id'
            );

            $stmt->execute([
                'title' => $title,
                'id' => $id,
                'user_id' => $userId,
            ]);
        }

        return $this->findById($id);
    }

    public function delete(
        int $id,
        int $userId
    ): bool {
        $stmt = $this->pdo->prepare(
            'DELETE FROM images
             WHERE
                id = :id
                AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }
}