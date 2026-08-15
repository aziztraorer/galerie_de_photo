<?php

declare(strict_types=1);

namespace App\Services;

use App\Auth\Session;
use App\Exceptions\HttpException;
use App\Repositories\UserRepository;
use Psr\Http\Message\UploadedFileInterface;

class AuthService
{
    private const ALLOWED_AVATAR_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_AVATAR_SIZE = 5 * 1024 * 1024;

    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function register(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($name === '') {
            throw new HttpException('Le nom est obligatoire.', 422);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException('Adresse email invalide.', 422);
        }

        if (strlen($password) < 8) {
            throw new HttpException('Le mot de passe doit contenir au moins 8 caractères.', 422);
        }

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new HttpException('Cette adresse email est déjà utilisée.', 409);
        }

        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return $this->sanitizeUser($user);
    }

    public function login(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new HttpException('Email et mot de passe obligatoires.', 422);
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new HttpException('Email ou mot de passe incorrect.', 401);
        }

        if (!password_verify($password, (string) $user['password'])) {
            throw new HttpException('Email ou mot de passe incorrect.', 401);
        }

        Session::start();
        Session::set('user_id', (int) $user['id']);

        $this->userRepository->updateLastActivity((int) $user['id']);

        return $this->sanitizeUser($user);
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function currentUser(): ?array
    {
        Session::start();

        $userId = Session::get('user_id');

        if (!$userId) {
            return null;
        }

        $user = $this->userRepository->findById((int) $userId);

        if ($user === null) {
            Session::destroy();
            return null;
        }

        $this->userRepository->updateLastActivity((int) $userId);

        return $this->sanitizeUser($user);
    }

    public function changePassword(
        int $userId,
        string $currentPassword,
        string $newPassword,
        string $confirmPassword
    ): void {
        if ($currentPassword === '') {
            throw new HttpException('L\'ancien mot de passe est obligatoire.', 422);
        }

        if (strlen($newPassword) < 8) {
            throw new HttpException('Le nouveau mot de passe doit contenir au moins 8 caractères.', 422);
        }

        if ($newPassword !== $confirmPassword) {
            throw new HttpException('Les nouveaux mots de passe ne correspondent pas.', 422);
        }

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new HttpException('Utilisateur introuvable.', 404);
        }

        if (!password_verify($currentPassword, (string) $user['password'])) {
            throw new HttpException('Ancien mot de passe incorrect.', 401);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        if (!$this->userRepository->updatePassword($userId, $hashedPassword)) {
            throw new HttpException('Impossible de modifier le mot de passe.', 500);
        }
    }

    public function updateAvatar(int $userId, array $files): array
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new HttpException('Utilisateur introuvable.', 404);
        }

        $file = $files['avatar'] ?? null;

        if (!$file instanceof UploadedFileInterface || $file->getError() === UPLOAD_ERR_NO_FILE) {
            throw new HttpException('Aucune image envoyée.', 422);
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new HttpException('Erreur lors de l\'upload de l\'image.', 422);
        }

        if ($file->getSize() > self::MAX_AVATAR_SIZE) {
            throw new HttpException('L\'image ne doit pas dépasser 5 Mo.', 422);
        }

        $stream = $file->getStream();
        $contents = $stream->getContents();
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);

        if (!isset(self::ALLOWED_AVATAR_MIME_TYPES[$mime])) {
            throw new HttpException('Format d\'image non autorisé (jpg, png ou webp uniquement).', 422);
        }

        $uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/avatars';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0775, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_AVATAR_MIME_TYPES[$mime];
        $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        try {
            $file->moveTo($destination);
        } catch (\Throwable $e) {
            throw new HttpException('Impossible de sauvegarder l\'image.', 500);
        }

        $avatarUrl = '/uploads/avatars/' . $filename;
        $this->deleteOldAvatar($user['avatar_url'] ?? null);

        if (!$this->userRepository->updateAvatar($userId, $avatarUrl)) {
            throw new HttpException('Impossible de mettre à jour la photo de profil.', 500);
        }

        $updatedUser = $this->userRepository->findById($userId);
        return $this->sanitizeUser($updatedUser);
    }

    private function deleteOldAvatar(?string $avatarUrl): void
    {
        if (!$avatarUrl) {
            return;
        }

        $relativePath = ltrim($avatarUrl, '/');
        $path = dirname(__DIR__, 2) . '/public/' . $relativePath;

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}