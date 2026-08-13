<?php
namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    public function listCategories(): array
    {
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as &$category) {
            $category['animal_count'] = count($this->categoryRepository->findAnimalsByCategoryId((int) $category['id']));
        }
        return $categories;
    }

    public function getCategoryById(int $id): ?array
    {
        return $this->categoryRepository->findById($id);
    }

    public function getCategoryAnimals(int $id): array
    {
        return $this->categoryRepository->findAnimalsByCategoryId($id);
    }
}
