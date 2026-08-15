<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Repositories\UserRepository;
use App\Repositories\PublicationRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController
{
    public function __construct(
        private UserRepository $userRepository,
        private PublicationRepository $publicationRepository
    ) {
    }

    public function getUsers(Request $request, Response $response): Response
    {
        try {
            $users = $this->userRepository->findAllWithActivity();
            
            foreach ($users as &$user) {
                $publications = $this->publicationRepository->findByUserId((int) $user['id']);
                $user['publications_count'] = count($publications);
            }
            
            return $this->json($response, [
                'success' => true,
                'data' => [
                    'users' => $users,
                    'total' => count($users)
                ]
            ]);
            
        } catch (\Throwable $e) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs'
            ], 500);
        }
    }

    public function getOnlineUsers(Request $request, Response $response): Response
    {
        try {
            $minutes = (int) ($request->getQueryParams()['minutes'] ?? 5);
            $users = $this->userRepository->getOnlineUsers($minutes);
            $count = $this->userRepository->getOnlineCount($minutes);
            
            return $this->json($response, [
                'success' => true,
                'data' => [
                    'users' => $users,
                    'count' => $count,
                    'minutes' => $minutes
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs en ligne'
            ], 500);
        }
    }

    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = (int) $args['id'];
            
            $currentUser = $request->getAttribute('user');
            if (!$currentUser) {
                throw new HttpException('Non authentifié.', 401);
            }
            
            if (($currentUser['role'] ?? 'user') !== 'admin') {
                throw new HttpException('Accès refusé.', 403);
            }
            
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new HttpException('Utilisateur introuvable.', 404);
            }
            
            if (($user['role'] ?? 'user') === 'admin') {
                throw new HttpException('Impossible de supprimer un administrateur.', 403);
            }
            
            $this->publicationRepository->deleteByUserId($userId);
            $this->userRepository->delete($userId);
            
            return $this->json($response, [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès.'
            ]);
            
        } catch (HttpException $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}