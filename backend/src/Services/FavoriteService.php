<?php
namespace App\Services;

use App\Repositories\FavoriteRepository;
use App\Exceptions\HttpException;

class FavoriteService
{
    public function __construct(private FavoriteRepository $favoriteRepository)
    {
    }

    public function listForUser(int $userId): array
    {
        return $this->favoriteRepository->listByUserId($userId);
    }

    public function toggle(int $userId, int $animalId): array
    {
        if ($animalId <= 0) {
            throw new HttpException('Invalid animal id.', 422);
        }

        if ($this->favoriteRepository->exists($userId, $animalId)) {
            $this->favoriteRepository->remove($userId, $animalId);
        } else {
            $this->favoriteRepository->add($userId, $animalId);
        }

        return $this->favoriteRepository->listByUserId($userId);
    }

    public function remove(int $userId, int $animalId): array
    {
        if ($animalId <= 0) {
            throw new HttpException('Invalid animal id.', 422);
        }

        $this->favoriteRepository->remove($userId, $animalId);
        return $this->favoriteRepository->listByUserId($userId);
    }
}
