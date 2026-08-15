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

    // Configuration des tentatives de connexion
    private const MAX_ATTEMPTS = 3;
    private const LOCK_MINUTES_1 = 5;
    private const LOCK_MINUTES_2 = 10;
    private const LOCK_DAYS = 10;

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

        // Vérifier si l'utilisateur existe
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new HttpException('Email ou mot de passe incorrect.', 401);
        }

        // ============================================
        // VÉRIFICATION DU VERROUILLAGE
        // ============================================
        try {
            $lockStatus = $this->userRepository->getLockStatus($email);
            
            if ($lockStatus) {
                $isLocked = (bool) $lockStatus['is_locked'];
                $lockedUntil = $lockStatus['locked_until'];
                $attempts = (int) $lockStatus['login_attempts'];
                
                if ($isLocked && $lockedUntil !== null) {
                    $pdo = $this->userRepository->getPdo();
                    $stmt = $pdo->prepare(
                        'SELECT TIMESTAMPDIFF(SECOND, NOW(), :locked_until) AS seconds_remaining'
                    );
                    $stmt->execute(['locked_until' => $lockedUntil]);
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $secondsRemaining = (int) ($result['seconds_remaining'] ?? 0);
                    
                    if ($secondsRemaining > 0) {
                        $minutes = ceil($secondsRemaining / 60);
                        $message = $this->getLockMessage($minutes, $attempts);
                        throw new HttpException($message, 423);
                    } else {
                        $this->userRepository->unlockAccount($email);
                    }
                }
            }
        } catch (\Exception $e) {
            // Si une erreur survient, on continue sans verrouillage
            error_log('Erreur lors de la vérification du verrouillage: ' . $e->getMessage());
        }

        // Vérifier le mot de passe
        if (!password_verify($password, (string) $user['password'])) {
            try {
                $attempts = $this->userRepository->incrementLoginAttempt($email);
                $lockResult = $this->checkAndLockAccount($email, $attempts);
                
                if ($lockResult['locked']) {
                    throw new HttpException($lockResult['message'], 423);
                }
                
                $remaining = self::MAX_ATTEMPTS - $attempts;
                $message = $remaining > 0 
                    ? "Email ou mot de passe incorrect. Il vous reste {$remaining} tentative(s) avant que votre compte ne soit bloqué."
                    : "Email ou mot de passe incorrect.";
                
                throw new HttpException($message, 401);
            } catch (HttpException $e) {
                throw $e;
            } catch (\Exception $e) {
                // Si une erreur survient, on affiche juste un message simple
                throw new HttpException('Email ou mot de passe incorrect.', 401);
            }
        }

        // Connexion réussie - Réinitialiser les tentatives
        try {
            $this->userRepository->resetLoginAttempts($email);
        } catch (\Exception $e) {
            // Ignorer les erreurs de réinitialisation
        }

        Session::start();
        $_SESSION['user_id'] = (int) $user['id'];
        Session::set('user_id', (int) $user['id']);

        try {
            $this->userRepository->updateLastActivity((int) $user['id']);
        } catch (\Exception $e) {
            // Ignorer les erreurs de mise à jour
        }

        return $this->sanitizeUser($user);
    }

    private function checkAndLockAccount(string $email, int $attempts): array
    {
        try {
            if ($attempts === 4) {
                $this->userRepository->lockAccount($email, self::LOCK_MINUTES_1);
                return [
                    'locked' => true,
                    'message' => 'Compte bloqué pour 5 minutes. Veuillez réessayer plus tard.'
                ];
            }
            
            if ($attempts === 7) {
                $this->userRepository->lockAccount($email, self::LOCK_MINUTES_2);
                return [
                    'locked' => true,
                    'message' => 'Compte bloqué pour 10 minutes. Veuillez réessayer plus tard.'
                ];
            }

            if ($attempts >= 8) {
                $this->userRepository->lockAccount($email, self::LOCK_DAYS * 24 * 60);
                return [
                    'locked' => true,
                    'message' => 'Compte bloqué pour 10 jours en raison de trop nombreuses tentatives.'
                ];
            }
        } catch (\Exception $e) {
            error_log('Erreur lors du verrouillage du compte: ' . $e->getMessage());
        }

        return ['locked' => false, 'message' => ''];
    }

    private function getLockMessage(int $minutes, int $attempts): string
    {
        if ($minutes >= 10 * 24 * 60) {
            return 'Votre compte est bloqué pour 10 jours.';
        }
        
        if ($minutes >= 10) {
            return "Votre compte est bloqué pour {$minutes} minutes.";
        }
        
        return "Votre compte est bloqué pour {$minutes} minutes.";
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function currentUser(): ?array
    {
        Session::start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $userId = Session::get('user_id');
        }

        if (!$userId) {
            return null;
        }

        $user = $this->userRepository->findById((int) $userId);

        if ($user === null) {
            Session::destroy();
            return null;
        }

        try {
            $this->userRepository->updateLastActivity((int) $userId);
        } catch (\Exception $e) {
            // Ignorer les erreurs
        }

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
        unset($user['login_attempts']);
        unset($user['last_login_attempt']);
        unset($user['locked_until']);
        unset($user['is_locked']);
        return $user;
    }
}