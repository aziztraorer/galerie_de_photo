<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Session;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(
        Request $request,
        Handler $handler
    ): Response {
        Session::start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $userId = Session::get('user_id');
        }

        if (!$userId) {
            return $this->unauthorized();
        }

        $userRepository = new UserRepository();
        $user = $userRepository->findById((int) $userId);

        if (!$user) {
            Session::destroy();
            return $this->unauthorized();
        }

        $request = $request->withAttribute('user', $user);
        
        return $handler->handle($request);
    }

    private function unauthorized(): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(
            json_encode([
                'success' => false,
                'message' => 'Authentication required.'
            ])
        );

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}