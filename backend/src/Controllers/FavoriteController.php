<?php
namespace App\Controllers;

use App\Services\FavoriteService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FavoriteController
{
    public function __construct(private FavoriteService $favoriteService)
    {
    }

    public function list(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        $favorites = $this->favoriteService->listForUser((int) $user['id']);

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $favorites,
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function add(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $payload = $request->getParsedBody() ?? [];
            $animalId = (int) ($payload['animal_id'] ?? 0);
            $favorites = $this->favoriteService->toggle((int) $user['id'], $animalId);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $favorites,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
            return $response->withStatus($e->getStatusCode())->withHeader('Content-Type', 'application/json');
        }
    }

    public function remove(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $animalId = (int) ($args['animal_id'] ?? 0);
            $favorites = $this->favoriteService->remove((int) $user['id'], $animalId);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $favorites,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
            return $response->withStatus($e->getStatusCode())->withHeader('Content-Type', 'application/json');
        }
    }
}
