<?php
namespace App\Repositories;

use App\Database\Database;
use PDO;

class CategoryRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getPdo();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAnimalsByCategoryId(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM animals WHERE category_id = :id ORDER BY name ASC');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }
}
