<?php
namespace App\Repositories;

use App\Database\Database;
use PDO;

class FavoriteRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function listByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, c.id AS category_id, c.name AS category_name, c.description AS category_description, c.icon AS category_icon
             FROM favorites f
             JOIN animals a ON a.id = f.animal_id
             LEFT JOIN categories c ON c.id = a.category_id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'scientific_name' => $row['scientific_name'],
                'short_description' => $row['short_description'],
                'description' => $row['description'],
                'diet' => $row['diet'],
                'habitat' => $row['habitat'],
                'characteristics' => $row['characteristics'],
                'lifespan' => $row['lifespan'],
                'image_url' => $row['image_url'],
                'category_id' => (int) $row['category_id'],
                'category' => [
                    'id' => (int) $row['category_id'],
                    'name' => $row['category_name'],
                    'description' => $row['category_description'],
                    'icon' => $row['category_icon'],
                ],
                'created_at' => $row['created_at'],
                'is_favorite' => true,
            ];
        }, $rows);
    }

    public function exists(int $userId, int $animalId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :user_id AND animal_id = :animal_id LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'animal_id' => $animalId]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $userId, int $animalId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO favorites (user_id, animal_id) VALUES (:user_id, :animal_id)');
        $stmt->execute(['user_id' => $userId, 'animal_id' => $animalId]);
    }

    public function remove(int $userId, int $animalId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM favorites WHERE user_id = :user_id AND animal_id = :animal_id');
        $stmt->execute(['user_id' => $userId, 'animal_id' => $animalId]);
    }
}
