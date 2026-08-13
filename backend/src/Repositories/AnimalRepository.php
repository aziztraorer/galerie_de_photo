<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use PDO;

class AnimalRepository
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
                a.*,
                c.name AS category_name,
                c.description AS category_description,
                c.icon AS category_icon
             FROM animals a
             LEFT JOIN categories c
                ON c.id = a.category_id
             ORDER BY a.name ASC'
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRow'], $rows);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                a.*,
                c.name AS category_name,
                c.description AS category_description,
                c.icon AS category_icon
             FROM animals a
             LEFT JOIN categories c
                ON c.id = a.category_id
             WHERE a.id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRow($row) : null;
    }

    private function mapRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'scientific_name' => $row['scientific_name'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'description' => $row['description'] ?? null,
            'diet' => $row['diet'] ?? null,
            'habitat' => $row['habitat'] ?? null,
            'characteristics' => $row['characteristics'] ?? null,
            'lifespan' => $row['lifespan'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'category_id' => $row['category_id'] !== null ? (int) $row['category_id'] : null,
            'category' => $row['category_id'] !== null ? [
                'id' => (int) $row['category_id'],
                'name' => $row['category_name'],
                'description' => $row['category_description'],
                'icon' => $row['category_icon'],
            ] : null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }
}