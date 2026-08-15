<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Session;
use App\Exceptions\HttpException;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private UserRepository $userRepository
    ) {
    }

    public function register(
        Request $request,
        Response $response
    ): Response {
        try {
            $payload = $request->getParsedBody();

            $user = $this->authService->register(
                is_array($payload) ? $payload : []
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'user' => $user
                    ]
                ],
                201
            );

        } catch (HttpException $e) {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getStatusCode()
            );
        }
    }

    public function login(
        Request $request,
        Response $response
    ): Response {
        try {
            $payload = $request->getParsedBody();

            $user = $this->authService->login(
                is_array($payload) ? $payload : []
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'user' => $user
                    ]
                ]
            );

        } catch (HttpException $e) {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getStatusCode()
            );
        }
    }

    public function logout(
        Request $request,
        Response $response
    ): Response {
        $this->authService->logout();

        return $this->json(
            $response,
            [
                'success' => true,
                'message' => 'Logged out.'
            ]
        );
    }

    public function me(
        Request $request,
        Response $response
    ): Response {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ],
                401
            );
        }

        return $this->json(
            $response,
            [
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ]
        );
    }

    public function changePassword(
        Request $request,
        Response $response
    ): Response {
        try {
            Session::start();

            $userId = Session::get('user_id');

            if (!$userId) {
                throw new HttpException(
                    'Utilisateur non connectÃ©.',
                    401
                );
            }

            $data = $request->getParsedBody();

            if (!is_array($data)) {
                $data = [];
            }

            $currentPassword = (string) (
                $data['current_password'] ?? ''
            );

            $newPassword = (string) (
                $data['new_password'] ?? ''
            );

            $confirmPassword = (string) (
                $data['confirm_password'] ?? ''
            );

            $this->authService->changePassword(
                (int) $userId,
                $currentPassword,
                $newPassword,
                $confirmPassword
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'message' => 'Mot de passe modifiÃ© avec succÃ¨s.'
                ]
            );

        } catch (HttpException $e) {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getStatusCode()
            );
        }
    }

    public function updateAvatar(
        Request $request,
        Response $response
    ): Response {
        try {
            Session::start();

            $userId = Session::get('user_id');

            if (!$userId) {
                throw new HttpException(
                    'Utilisateur non connectÃ©.',
                    401
                );
            }

            $files = $request->getUploadedFiles();

            $user = $this->authService->updateAvatar(
                (int) $userId,
                $files
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'message' => 'Photo de profil mise Ã  jour avec succÃ¨s.',
                    'data' => [
                        'user' => $user
                    ]
                ]
            );

        } catch (HttpException $e) {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                $e->getStatusCode()
            );
        }
    }

    public function getLockStatus(
        Request $request,
        Response $response
    ): Response {
        $params = $request->getQueryParams();
        $email = $params['email'] ?? '';
        
        if ($email === '') {
            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => 'Email requis'
                ],
                400
            );
        }
        
        $lockStatus = $this->userRepository->getLockStatus($email);
        
        if (!$lockStatus) {
            return $this->json(
                $response,
                [
                    'success' => true,
                    'is_locked' => false,
                    'remaining_attempts' => null
                ]
            );
        }
        
        $isLocked = (bool) $lockStatus['is_locked'];
        $lockedUntil = $lockStatus['locked_until'];
        $attempts = (int) $lockStatus['login_attempts'];
        
        $lockMinutes = 0;
        if ($isLocked && $lockedUntil !== null) {
            $pdo = $this->userRepository->getPdo();
            $stmt = $pdo->prepare(
                'SELECT TIMESTAMPDIFF(SECOND, NOW(), :locked_until) AS seconds_remaining'
            );
            $stmt->execute(['locked_until' => $lockedUntil]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $secondsRemaining = (int) ($result['seconds_remaining'] ?? 0);
            $lockMinutes = ceil($secondsRemaining / 60);
            
            if ($secondsRemaining <= 0) {
                $isLocked = false;
                $this->userRepository->unlockAccount($email);
            }
        }
        
        $remainingAttempts = null;
        if (!$isLocked) {
            $remainingAttempts = max(0, 3 - $attempts);
        }
        
        return $this->json(
            $response,
            [
                'success' => true,
                'is_locked' => $isLocked,
                'lock_minutes' => $lockMinutes,
                'remaining_attempts' => $remainingAttempts,
                'attempts' => $attempts
            ]
        );
    }

    private function json(
        Response $response,
        array $data,
        int $status = 200
    ): Response {
        $response->getBody()->write(
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            )
        );

        return $response
            ->withStatus($status)
            ->withHeader(
                'Content-Type',
                'application/json'
            );
    }
}