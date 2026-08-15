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
        
        if (!$user) {
            return $this->forbidden('Authentication required.');
        }
        
        $role = $user['role'] ?? 'user';
        
        if ($role !== 'admin') {
            return $this->forbidden('You do not have admin privileges.');
        }
        
        return $handler->handle($request);
    }

    private function forbidden(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(
            json_encode([
                'success' => false,
                'message' => $message
            ])
        );

        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
}