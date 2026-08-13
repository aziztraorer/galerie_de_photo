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

        // Les annonces (publications) sont publiques en lecture : elles doivent
        // s'afficher dans la partie "Animaux" à côté des animaux déjà existants,
        // même pour un visiteur non connecté.
        $api->get('/publications', [PublicationController::class, 'list']);
        $api->get('/publications/{id:[0-9]+}', [PublicationController::class, 'show']);

        $api->group('', function (RouteCollectorProxy $protected): void {

            $protected->post('/auth/change-password', [AuthController::class, 'changePassword']);

            $protected->get('/favorites', [FavoriteController::class, 'list']);
            $protected->post('/favorites', [FavoriteController::class, 'add']);
            $protected->delete('/favorites/{animal_id:[0-9]+}', [FavoriteController::class, 'remove']);

            // Création, modification et suppression d'une annonce : réservées
            // à l'utilisateur connecté (et propriétaire de l'annonce pour
            // modifier/supprimer, vérifié dans PublicationService).
            $protected->post('/publications', [PublicationController::class, 'create']);
            $protected->put('/publications/{id:[0-9]+}', [PublicationController::class, 'update']);
            // Route POST équivalente, utilisée par le formulaire front (envoi de
            // fichiers en multipart/form-data, non fiable en PUT natif en PHP).
            $protected->post('/publications/{id:[0-9]+}/update', [PublicationController::class, 'update']);
            $protected->delete('/publications/{id:[0-9]+}', [PublicationController::class, 'delete']);

        })->add(new AuthMiddleware());

        // NOTE : ces routes d'administration n'ont pas de mÃ©thode correspondante
        // dans AnimalController / CategoryController (pas de store/update/destroy,
        // ni de service ou repository pour crÃ©er/modifier/supprimer). Elles sont
        // dÃ©sactivÃ©es pour Ã©viter une erreur fatale "Method not found".
        // Dis-moi si tu veux que je construise ce CRUD admin (service + repository
        // + contrÃ´leur), ce serait un ajout de fonctionnalitÃ©, pas une correction.
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