<?php

declare(strict_types=1);

use App\Controllers\AnimalController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\FavoriteController;
use App\Controllers\PublicationController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

    // Ajouter le middleware CORS à toutes les routes
    $app->add(new CorsMiddleware());

    $app->options('/{routes:.*}', function ($request, $response) {
        return $response;
    });

    $app->group('/api', function (RouteCollectorProxy $api): void {

        $api->get('/animals', [AnimalController::class, 'list']);
        $api->get('/animals/{id:[0-9]+}', [AnimalController::class, 'show']);

        $api->get('/categories', [CategoryController::class, 'list']);
        $api->get('/categories/{id:[0-9]+}', [CategoryController::class, 'show']);
        $api->get('/categories/{id:[0-9]+}/animals', [CategoryController::class, 'animals']);

        $api->post('/auth/register', [AuthController::class, 'register']);
        $api->post('/auth/login', [AuthController::class, 'login']);
        $api->get('/auth/me', [AuthController::class, 'me']);
        $api->post('/auth/logout', [AuthController::class, 'logout']);

        $api->get('/publications', [PublicationController::class, 'list']);
        $api->get('/publications/{id:[0-9]+}', [PublicationController::class, 'show']);

        $api->group('', function (RouteCollectorProxy $protected): void {

            $protected->post('/auth/change-password', [AuthController::class, 'changePassword']);
            $protected->post('/auth/avatar', [AuthController::class, 'updateAvatar']);

            $protected->get('/favorites', [FavoriteController::class, 'list']);
            $protected->post('/favorites', [FavoriteController::class, 'add']);
            $protected->delete('/favorites/{animal_id:[0-9]+}', [FavoriteController::class, 'remove']);

            $protected->post('/publications', [PublicationController::class, 'create']);
            $protected->put('/publications/{id:[0-9]+}', [PublicationController::class, 'update']);
            $protected->post('/publications/{id:[0-9]+}/update', [PublicationController::class, 'update']);
            $protected->delete('/publications/{id:[0-9]+}', [PublicationController::class, 'delete']);

        })->add(new AuthMiddleware());
    });
};