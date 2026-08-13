<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\HttpException;
use App\Services\AnimalService;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AnimalController
{
    public function __construct(
        private AnimalService $animalService
    ) {
    }

    public function list(
        Request $request,
        Response $response
    ): Response {
        try {

            $animals = $this->animalService->list();

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'animals' => $animals
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

            $animal = $this->animalService->show(
                (int) $args['id']
            );

            return $this->json(
                $response,
                [
                    'success' => true,
                    'data' => [
                        'animal' => $animal
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