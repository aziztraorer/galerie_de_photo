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

    private const MAX_FILE_SIZE =
        5 * 1024 * 1024;

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

        $uploadedFile = $this->extractImageFile($files);

        if ($uploadedFile !== null) {
            $imagePath = $this->uploadImage($uploadedFile);
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

        $uploadedFile = $this->extractImageFile($files);

        if ($uploadedFile !== null) {
            $imagePath = $this->uploadImage($uploadedFile);

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

    /**
     * Recupere le fichier "image" envoye par le formulaire, sous forme
     * d'objet UploadedFileInterface (PSR-7), tel que fourni par Slim.
     */
    private function extractImageFile(
        array $files
    ): ?UploadedFileInterface {

        $file = $files['image'] ?? null;

        if (!$file instanceof UploadedFileInterface) {
            return null;
        }

        if ($file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    private function uploadImage(
        UploadedFileInterface $file
    ): string {

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new HttpException(
                'Erreur lors de l upload de l image.',
                422
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new HttpException(
                'L image ne doit pas dÃ©passer 5 Mo.',
                422
            );
        }

        $stream = $file->getStream();

        $contents = $stream->getContents();

        $mime = (new \finfo(FILEINFO_MIME_TYPE))
            ->buffer($contents);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mime])) {
            throw new HttpException(
                'Format d image non autorisÃ©.',
                422
            );
        }

        $filename =
            bin2hex(random_bytes(16))
            . '.'
            . $allowedTypes[$mime];

        $destination =
            $this->uploadDirectory
            . DIRECTORY_SEPARATOR
            . $filename;

        // Le flux a deja ete lu plus haut pour detecter le type MIME :
        // on repositionne le curseur avant de deplacer le fichier.
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        try {
            $file->moveTo($destination);
        } catch (\Throwable $e) {
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