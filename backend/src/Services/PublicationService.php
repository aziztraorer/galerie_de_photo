<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Repositories\PublicationRepository;

class PublicationService
{
    private string $uploadDirectory;

    public function __construct(
        private PublicationRepository $publicationRepository
    ) {
        $this->uploadDirectory =
            dirname(__DIR__, 2) . '/public/uploads/publications';

        if (!is_dir($this->uploadDirectory)) {
            mkdir(
                $this->uploadDirectory,
                0775,
                true
            );
        }
    }

    public function list(): array
    {
        return $this->publicationRepository->findAll();
    }

    public function show(int $id): array
    {
        $publication =
            $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException(
                'Publication introuvable.',
                404
            );
        }

        return $publication;
    }

    public function create(
        int $userId,
        array $data,
        array $files
    ): array {

        $title = trim(
            (string) ($data['title'] ?? '')
        );

        $description = trim(
            (string) ($data['description'] ?? '')
        );

        if ($title === '') {
            throw new HttpException(
                'Le titre est obligatoire.',
                422
            );
        }

        $imagePath = null;

        if (
            isset($files['image']) &&
            $files['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $imagePath = $this->uploadImage(
                $files['image']
            );
        }

        return $this->publicationRepository->create(
            $userId,
            $title,
            $description,
            $imagePath
        );
    }

    public function update(
        int $userId,
        int $id,
        array $data,
        array $files
    ): array {

        $publication =
            $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException(
                'Publication introuvable.',
                404
            );
        }

        if ((int) $publication['user_id'] !== $userId) {
            throw new HttpException(
                'Vous ne pouvez pas modifier cette publication.',
                403
            );
        }

        $title = trim(
            (string) (
                $data['title']
                ?? $publication['title']
            )
        );

        $description = trim(
            (string) (
                $data['description']
                ?? $publication['description']
            )
        );

        if ($title === '') {
            throw new HttpException(
                'Le titre est obligatoire.',
                422
            );
        }

        $imagePath = null;

        if (
            isset($files['image']) &&
            $files['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $imagePath = $this->uploadImage(
                $files['image']
            );

            $this->deleteOldImage(
                $publication['image_url'] ?? null
            );
        }

        $this->publicationRepository->update(
            $id,
            $title,
            $description,
            $imagePath
        );

        return $this->publicationRepository->findById($id);
    }

    public function delete(
        int $userId,
        int $id
    ): void {

        $publication =
            $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException(
                'Publication introuvable.',
                404
            );
        }

        if ((int) $publication['user_id'] !== $userId) {
            throw new HttpException(
                'Vous ne pouvez pas supprimer cette publication.',
                403
            );
        }

        $this->deleteOldImage(
            $publication['image_url'] ?? null
        );

        $this->publicationRepository->delete($id);
    }

    private function uploadImage(
        array $file
    ): string {

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException(
                'Erreur lors de l upload de l image.',
                422
            );
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new HttpException(
                'L image ne doit pas dépasser 5 Mo.',
                422
            );
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $mime = mime_content_type(
            $file['tmp_name']
        );

        if (!isset($allowedTypes[$mime])) {
            throw new HttpException(
                'Format d image non autorisé.',
                422
            );
        }

        $filename =
            bin2hex(random_bytes(16))
            . '.'
            . $allowedTypes[$mime];

        $destination =
            $this->uploadDirectory
            . '/'
            . $filename;

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {
            throw new HttpException(
                'Impossible de sauvegarder l image.',
                500
            );
        }

        return '/uploads/publications/' . $filename;
    }

    private function deleteOldImage(
        ?string $imagePath
    ): void {

        if (!$imagePath) {
            return;
        }

        $relativePath = ltrim(
            $imagePath,
            '/'
        );

        $file =
            dirname(__DIR__, 2)
            . '/public/'
            . $relativePath;

        if (is_file($file)) {
            unlink($file);
        }
    }
}