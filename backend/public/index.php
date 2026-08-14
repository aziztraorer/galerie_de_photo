<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Construire le conteneur
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Créer l'application
AppFactory::setContainer($container);
$app = AppFactory::create();

// Ajouter les middlewares de base
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Ajouter le middleware d'erreur (optionnel, pour le développement)
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Charger les routes
$routes = require_once __DIR__ . '/../src/Routes/Routes.php';
$routes($app);

// Lancer l'application
$app->run();