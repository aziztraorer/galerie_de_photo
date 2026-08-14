<?php

declare(strict_types=1);

namespace App\Services;

use App\Auth\Session;
use App\Exceptions\HttpException;
use App\Repositories\UserRepository;

class AuthService
{
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
            throw new HttpException(
                'Le nom est obligatoire.',
                422
            );
        }

        if (
            $email === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new HttpException(
                'Adresse email invalide.',
                422
            );
        }

        if (strlen($password) < 8) {
            throw new HttpException(
                'Le mot de passe doit contenir au moins 8 caractères.',
                422
            );
        }

        if ($this->userRepository->findByEmail($email) !== null) {
            throw new HttpException(
                'Cette adresse email est déjà utilisée.',
                409
            );
        }

        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            )
        ]);

        return $this->sanitizeUser($user);
    }

    public function login(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new HttpException(
                'Email et mot de passe obligatoires.',
                422
            );
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new HttpException(
                'Email ou mot de passe incorrect.',
                401
            );
        }

        if (
            !password_verify(
                $password,
                (string) $user['password']
            )
        ) {
            throw new HttpException(
                'Email ou mot de passe incorrect.',
                401
            );
        }

        Session::start();

        Session::set(
            'user_id',
            (int) $user['id']
        );

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

        $user = $this->userRepository->findById(
            (int) $userId
        );

        if ($user === null) {
            Session::destroy();

            return null;
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
            throw new HttpException(
                'L\'ancien mot de passe est obligatoire.',
                422
            );
        }

        if (strlen($newPassword) < 8) {
            throw new HttpException(
                'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                422
            );
        }

        if ($newPassword !== $confirmPassword) {
            throw new HttpException(
                'Les nouveaux mots de passe ne correspondent pas.',
                422
            );
        }

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new HttpException(
                'Utilisateur introuvable.',
                404
            );
        }

        if (
            !password_verify(
                $currentPassword,
                (string) $user['password']
            )
        ) {
            throw new HttpException(
                'Ancien mot de passe incorrect.',
                401
            );
        }

        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        if (
            !$this->userRepository->updatePassword(
                $userId,
                $hashedPassword
            )
        ) {
            throw new HttpException(
                'Impossible de modifier le mot de passe.',
                500
            );
        }
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password']);

        return $user;
    }
}