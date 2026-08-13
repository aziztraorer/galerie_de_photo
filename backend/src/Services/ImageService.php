<?php

namespace App\Services;

use App\Auth\Session;
use App\Repositories\ImageRepository;
use App\Exceptions\HttpException;
use Psr\Http\Message\UploadedFileInterface;

class ImageService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private const MAX_FILE_SIZE =
        5 * 1024 * 1024;

    public function __construct(
        private ImageRepository $imageRepository
    ) {
    }

    public function list(): array
    {
        $userId = $this->getUserId();

        return $this->imageRepository
            ->listByUser($userId);
    }

    public function create(
        string $title,
        ?UploadedFileInterface $file
    ): array {
        $userId = $this->getUserId();

        $title = trim($title);

        if ($title === '') {
            throw new HttpException(
                'Title is required.',
                422
            );
        }

        if (!$file) {
            throw new HttpException(
                'Image is required.',
                422
            );
        }

        $this->validateFile($file);

        $filename =
            $this->generateFilename($file);

        $uploadDirectory =
            $this->getUploadDirectory();

        $file->moveTo(
            $uploadDirectory .
            DIRECTORY_SEPARATOR .
            $filename
        );

        $imageUrl =
            '/uploads/images/' .
            $filename;

        return $this->imageRepository->create(
            $userId,
            $title,
            $imageUrl
        );
    }

    public function update(
        int $id,
        string $title,
        ?UploadedFileInterface $file
    ): array {
        $userId = $this->getUserId();

        $image =
            $this->imageRepository
                ->findById($id);

        if (!$image) {
            throw new HttpException(
                'Image not found.',
                404
            );
        }

        if (
            (int) $image['user_id'] !==
            $userId
        ) {
            throw new HttpException(
                'You cannot modify this image.',
                403
            );
        }

        $title = trim($title);

        if ($title === '') {
            throw new HttpException(
                'Title is required.',
                422
            );
        }

        $newImageUrl = null;

        if ($file) {
            $this->validateFile($file);

            $filename =
                $this->generateFilename($file);

            $uploadDirectory =
                $this->getUploadDirectory();

            $file->moveTo(
                $uploadDirectory .
                DIRECTORY_SEPARATOR .
                $filename
            );

            $newImageUrl =
                '/uploads/images/' .
                $filename;
        }

        $updatedImage =
            $this->imageRepository->update(
                $id,
                $userId,
                $title,
                $newImageUrl
            );

        if (!$updatedImage) {
            throw new HttpException(
                'Unable to update image.',
                500
            );
        }

        if ($newImageUrl !== null) {
            $this->deletePhysicalFile(
                $image['image_url']
            );
        }

        return $updatedImage;
    }

    public function delete(
        int $id
    ): void {
        $userId = $this->getUserId();

        $image =
            $this->imageRepository
                ->findById($id);

        if (!$image) {
            throw new HttpException(
                'Image not found.',
                404
            );
        }

        if (
            (int) $image['user_id'] !==
            $userId
        ) {
            throw new HttpException(
                'You cannot delete this image.',
                403
            );
        }

        $deleted =
            $this->imageRepository->delete(
                $id,
                $userId
            );

        if (!$deleted) {
            throw new HttpException(
                'Unable to delete image.',
                500
            );
        }

        $this->deletePhysicalFile(
            $image['image_url']
        );
    }

    private function getUserId(): int
    {
        $userId =
            Session::get('user_id');

        if (!$userId) {
            throw new HttpException(
                'Unauthenticated.',
                401
            );
        }

        return (int) $userId;
    }

    private function validateFile(
        UploadedFileInterface $file
    ): void {
        if (
            $file->getError() !==
            UPLOAD_ERR_OK
        ) {
            throw new HttpException(
                'Image upload failed.',
                422
            );
        }

        if (
            $file->getSize() >
            self::MAX_FILE_SIZE
        ) {
            throw new HttpException(
                'Image must not exceed 5 MB.',
                422
            );
        }

        $stream = $file->getStream();

        $contents =
            $stream->getContents();

        $mimeType =
            (new \finfo(FILEINFO_MIME_TYPE))
                ->buffer($contents);

        if (
            !in_array(
                $mimeType,
                self::ALLOWED_MIME_TYPES,
                true
            )
        ) {
            throw new HttpException(
                'Only JPG, PNG and WEBP images are allowed.',
                422
            );
        }
    }

    private function generateFilename(
        UploadedFileInterface $file
    ): string {
        $extension =
            match ($this->getMimeType($file)) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

        return uniqid(
            'image_',
            true
        ) . '.' . $extension;
    }

    private function getMimeType(
        UploadedFileInterface $file
    ): string {
        $stream = $file->getStream();

        $contents =
            $stream->getContents();

        return (new \finfo(FILEINFO_MIME_TYPE))
            ->buffer($contents);
    }

    private function getUploadDirectory(): string
    {
        $directory =
            dirname(__DIR__, 2) .
            '/public/uploads/images';

        if (!is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
        }

        return $directory;
    }

    private function deletePhysicalFile(
        string $imageUrl
    ): void {
        $filename =
            basename($imageUrl);

        $path =
            $this->getUploadDirectory() .
            DIRECTORY_SEPARATOR .
            $filename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}