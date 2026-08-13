<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AdminMiddleware
{
    public function __invoke(
        Request $request,
        Handler $handler
    ): Response {
        $user = $request->getAttribute('user');

        if (
            !$user ||
            ($user['role'] ?? 'user') !== 'admin'
        ) {
            $response = new SlimResponse();

            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Forbidden.'
                ])
            );

            return $response
                ->withStatus(403)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }

        return $handler->handle($request);
    }
}