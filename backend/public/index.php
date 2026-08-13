<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Conteneur PHP-DI : sans lui, Slim ne sait pas construire les contrôleurs
// (ex: CategoryController) qui attendent une dépendance dans leur constructeur
// (ex: CategoryService), et échoue avec "must be of type X, null given".
// PHP-DI résout automatiquement ces dépendances via les types déclarés
// (autowiring), sans configuration supplémentaire nécessaire ici.
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();
AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->addErrorMiddleware(
    true,
    true,
    true
);

// Le middleware CORS doit être ajouté APRÈS addErrorMiddleware pour être
// exécuté en dernier (= le plus à l'extérieur). Sinon, quand une exception
// est levée plus bas dans la pile (404, 500...), elle remonte directement
// jusqu'à ErrorMiddleware sans repasser par le "return" de ce middleware,
// et la réponse d'erreur part sans les headers Access-Control-Allow-*.
$app->add(function (
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface {
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();

        return $response
            ->withStatus(200)
            ->withHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }

    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

(require __DIR__ . '/../src/Routes/Routes.php')($app);

$app->run();