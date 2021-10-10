<?php
declare(strict_types=1);

use App\Api\ErrorHandler\DefaultErrorHandler;
use DI\Bridge\Slim\Bridge;

$container = require __DIR__ . '/../app/bootstrap.php';

// create slim app using php-di slim bridge
$app = Bridge::create($container);

// register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// add error middleware and set default error handler
$errorMiddleware = $app->addErrorMiddleware(DEV_MODE, true, true);
$errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));

// add base middleware
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$app->run();