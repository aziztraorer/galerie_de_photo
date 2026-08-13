<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\PublicationService;
use App\Auth\Session;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PublicationController
{
    public function __construct(
        private PublicationService $publicationService
    ) {
    }

    public function list(
        Request $request,
        Response $response
    ): Response {

        try {

            $publications =
                $this->publicationService->list();

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'publications' => $publications
                    ]
                ]
            );

        } catch (\Throwable $e) {

            return $this->json(
                $response,
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response {

        try {

            $publication =
                $this->publicationService->show(
                    (int) $args['id']
                );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'publication' => $publication
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

    public function create(
        Request $request,
        Response $response
    ): Response {

        try {

            Session::start();

            $userId = Session::get('user_id');

            if (!$userId) {
                throw new HttpException(
                    'Utilisateur non connecté.',
                    401
                );
            }

            $data = $request->getParsedBody();

            if (!is_array($data)) {
                $data = [];
            }

            $files = $request->getUploadedFiles();

            $publication =
                $this->publicationService->create(
                    (int) $userId,
                    $data,
                    $files
                );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'message' => 'Publication créée avec succès.',
                    'data' => [
                        'publication' => $publication
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

    public function update(
        Request $request,
        Response $response,
        array $args
    ): Response {

        try {

            Session::start();

            $userId = Session::get('user_id');

            if (!$userId) {
                throw new HttpException(
                    'Utilisateur non connecté.',
                    401
                );
            }

            $data = $request->getParsedBody();

            if (!is_array($data)) {
                $data = [];
            }

            $files = $request->getUploadedFiles();

            $publication =
                $this->publicationService->update(
                    (int) $userId,
                    (int) $args['id'],
                    $data,
                    $files
                );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'message' => 'Publication modifiée avec succès.',
                    'data' => [
                        'publication' => $publication
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

    public function delete(
        Request $request,
        Response $response,
        array $args
    ): Response {

        try {

            Session::start();

            $userId = Session::get('user_id');

            if (!$userId) {
                throw new HttpException(
                    'Utilisateur non connecté.',
                    401
                );
            }

            $this->publicationService->delete(
                (int) $userId,
                (int) $args['id']
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'message' => 'Publication supprimée.'
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