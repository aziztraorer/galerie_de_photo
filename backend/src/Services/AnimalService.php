<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Repositories\AnimalRepository;
use App\Repositories\CategoryRepository;

class AnimalService
{
    public function __construct(
        private AnimalRepository $animalRepository,
        private CategoryRepository $categoryRepository
    ) {
    }

    public function list(): array
    {
        return $this->animalRepository->findAll();
    }

    public function show(int $id): array
    {
        $animal = $this->animalRepository->findById($id);

        if ($animal === null) {
            throw new HttpException(
                'Animal introuvable.',
                404
            );
        }

        return $animal;
    }
}