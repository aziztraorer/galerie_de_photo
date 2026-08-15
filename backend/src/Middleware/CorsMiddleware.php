<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class CorsMiddleware
{
    public function __invoke(
        Request $request,
        Handler $handler
    ): Response {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new SlimResponse();
            return $this->addCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(Response $response): Response
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost:5173';
        
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:5174',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:5174'
        ];
        
        $allowedOrigin = in_array($origin, $allowedOrigins) ? $origin : 'http://localhost:5173';

        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH, HEAD')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token, X-HTTP-Method-Override')
            ->withHeader('Access-Control-Expose-Headers', 'Content-Length, X-Kuma-Revision, X-Debug-Token')
            ->withHeader('Access-Control-Max-Age', '86400');
    }
}