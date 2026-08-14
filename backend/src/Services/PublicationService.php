<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Repositories\PublicationRepository;
use Psr\Http\Message\UploadedFileInterface;

class PublicationService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private PublicationRepository $publicationRepository
    ) {
    }

    public function list(): array
    {
        return $this->publicationRepository->findAll();
    }

    public function show(int $id): array
    {
        $publication = $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException('Publication introuvable.', 404);
        }

        return $publication;
    }

    public function create(
        int $userId,
        array $data,
        ?UploadedFileInterface $imageFile
    ): array {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($title === '') {
            throw new HttpException('Le titre est obligatoire.', 422);
        }

        if ($description === '') {
            throw new HttpException('La description est obligatoire.', 422);
        }

        if (!$imageFile) {
            throw new HttpException('L\'image est obligatoire.', 422);
        }

        $this->validateFile($imageFile);

        $filename = $this->generateFilename($imageFile);
        $uploadDirectory = $this->getUploadDirectory();

        $imageFile->moveTo($uploadDirectory . DIRECTORY_SEPARATOR . $filename);

        $imageUrl = '/uploads/publications/' . $filename;

        return $this->publicationRepository->create(
            $userId,
            $title,
            $description,
            $imageUrl
        );
    }

    public function update(
        int $userId,
        int $id,
        array $data,
        ?UploadedFileInterface $imageFile = null
    ): array {
        $publication = $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException('Publication introuvable.', 404);
        }

        if ((int) $publication['user_id'] !== $userId) {
            throw new HttpException('Vous ne pouvez pas modifier cette publication.', 403);
        }

        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($title === '') {
            throw new HttpException('Le titre est obligatoire.', 422);
        }

        if ($description === '') {
            throw new HttpException('La description est obligatoire.', 422);
        }

        $imageUrl = null;

        if ($imageFile) {
            $this->validateFile($imageFile);
            $filename = $this->generateFilename($imageFile);
            $uploadDirectory = $this->getUploadDirectory();
            $imageFile->moveTo($uploadDirectory . DIRECTORY_SEPARATOR . $filename);
            $imageUrl = '/uploads/publications/' . $filename;
        }

        $this->publicationRepository->update($id, $title, $description, $imageUrl);

        if ($imageUrl !== null) {
            $this->deletePhysicalFile($publication['image_url'] ?? null);
        }

        return $this->publicationRepository->findById($id);
    }

    public function delete(int $userId, int $id): void
    {
        $publication = $this->publicationRepository->findById($id);

        if ($publication === null) {
            throw new HttpException('Publication introuvable.', 404);
        }

        if ((int) $publication['user_id'] !== $userId) {
            throw new HttpException('Vous ne pouvez pas supprimer cette publication.', 403);
        }

        $this->publicationRepository->delete($id);
        $this->deletePhysicalFile($publication['image_url'] ?? null);
    }

    private function validateFile(UploadedFileInterface $file): void
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new HttpException('Erreur lors de l\'upload de l\'image.', 422);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new HttpException('L\'image ne doit pas dépasser 5 Mo.', 422);
        }

        $stream = $file->getStream();
        $contents = $stream->getContents();
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new HttpException('Seuls les formats JPG, PNG et WEBP sont autorisés.', 422);
        }
    }

    private function generateFilename(UploadedFileInterface $file): string
    {
        $extension = match ($this->getMimeType($file)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        return uniqid('publication_', true) . '.' . $extension;
    }

    private function getMimeType(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();
        $contents = $stream->getContents();
        return (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
    }

    private function getUploadDirectory(): string
    {
        $directory = dirname(__DIR__, 2) . '/public/uploads/publications';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory;
    }

    private function deletePhysicalFile(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        $filename = basename($imageUrl);
        $path = $this->getUploadDirectory() . DIRECTORY_SEPARATOR . $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}