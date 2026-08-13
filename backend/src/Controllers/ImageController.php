<?php

namespace App\Controllers;

use App\Services\ImageService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ImageController
{
    public function __construct(
        private ImageService $imageService
    ) {
    }

    public function list(
        Request $request,
        Response $response
    ): Response {
        try {
            $images =
                $this->imageService->list();

            $response->getBody()->write(
                json_encode([
                    'success' => true,
                    'data' => [
                        'images' => $images,
                    ],
                ])
            );

            return $response->withHeader(
                'Content-Type',
                'application/json'
            );
        } catch (HttpException $e) {
            return $this->error(
                $response,
                $e->getMessage(),
                $e->getStatusCode()
            );
        }
    }

    public function create(
        Request $request,
        Response $response
    ): Response {
        try {
            $data =
                $request->getParsedBody() ?? [];

            $uploadedFiles =
                $request->getUploadedFiles();

            $file =
                $uploadedFiles['image'] ?? null;

            $title =
                $data['title'] ?? '';

            $image =
                $this->imageService->create(
                    $title,
                    $file
                );

            $response->getBody()->write(
                json_encode([
                    'success' => true,
                    'message' =>
                        'Image added successfully.',
                    'data' => [
                        'image' => $image,
                    ],
                ])
            );

            return $response
                ->withStatus(201)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        } catch (HttpException $e) {
            return $this->error(
                $response,
                $e->getMessage(),
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
            $id =
                (int) ($args['id'] ?? 0);

            $data =
                $request->getParsedBody() ?? [];

            $uploadedFiles =
                $request->getUploadedFiles();

            $file =
                $uploadedFiles['image'] ?? null;

            $title =
                $data['title'] ?? '';

            $image =
                $this->imageService->update(
                    $id,
                    $title,
                    $file
                );

            $response->getBody()->write(
                json_encode([
                    'success' => true,
                    'message' =>
                        'Image updated successfully.',
                    'data' => [
                        'image' => $image,
                    ],
                ])
            );

            return $response->withHeader(
                'Content-Type',
                'application/json'
            );
        } catch (HttpException $e) {
            return $this->error(
                $response,
                $e->getMessage(),
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
            $id =
                (int) ($args['id'] ?? 0);

            $this->imageService->delete($id);

            $response->getBody()->write(
                json_encode([
                    'success' => true,
                    'message' =>
                        'Image deleted successfully.',
                ])
            );

            return $response->withHeader(
                'Content-Type',
                'application/json'
            );
        } catch (HttpException $e) {
            return $this->error(
                $response,
                $e->getMessage(),
                $e->getStatusCode()
            );
        }
    }

    private function error(
        Response $response,
        string $message,
        int $status
    ): Response {
        $response->getBody()->write(
            json_encode([
                'success' => false,
                'message' => $message,
            ])
        );

        return $response
            ->withStatus($status)
            ->withHeader(
                'Content-Type',
                'application/json'
            );
    }
}