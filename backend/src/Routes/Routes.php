<?php

declare(strict_types=1);

use App\Controllers\AnimalController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\FavoriteController;
use App\Controllers\PublicationController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {

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

        $api->group('', function (RouteCollectorProxy $protected): void {

            $protected->post('/auth/change-password', [AuthController::class, 'changePassword']);

            $protected->get('/favorites', [FavoriteController::class, 'list']);
            $protected->post('/favorites', [FavoriteController::class, 'add']);
            $protected->delete('/favorites/{animal_id:[0-9]+}', [FavoriteController::class, 'remove']);

            $protected->get('/publications', [PublicationController::class, 'list']);
            $protected->get('/publications/{id:[0-9]+}', [PublicationController::class, 'show']);
            $protected->post('/publications', [PublicationController::class, 'create']);
            $protected->put('/publications/{id:[0-9]+}', [PublicationController::class, 'update']);
            $protected->delete('/publications/{id:[0-9]+}', [PublicationController::class, 'delete']);

        })->add(new AuthMiddleware());

        // NOTE : ces routes d'administration n'ont pas de méthode correspondante
        // dans AnimalController / CategoryController (pas de store/update/destroy,
        // ni de service ou repository pour créer/modifier/supprimer). Elles sont
        // désactivées pour éviter une erreur fatale "Method not found".
        // Dis-moi si tu veux que je construise ce CRUD admin (service + repository
        // + contrôleur), ce serait un ajout de fonctionnalité, pas une correction.
        //
        // $api->group('/admin', function (RouteCollectorProxy $admin): void {
        //     $admin->post('/animals', [AnimalController::class, 'store']);
        //     $admin->put('/animals/{id:[0-9]+}', [AnimalController::class, 'update']);
        //     $admin->delete('/animals/{id:[0-9]+}', [AnimalController::class, 'destroy']);
        //     $admin->post('/categories', [CategoryController::class, 'store']);
        //     $admin->put('/categories/{id:[0-9]+}', [CategoryController::class, 'update']);
        //     $admin->delete('/categories/{id:[0-9]+}', [CategoryController::class, 'destroy']);
        // })->add(new AdminMiddleware())->add(new AuthMiddleware());
    });
};