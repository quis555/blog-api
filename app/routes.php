<?php

use App\Controller\DocController;
use App\Controller\NotFoundController;
use App\Controller\UserController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('/api', function (Group $group) {

        $group->get('/doc', DocController::class . '::index');
        $group->get('/doc.json', DocController::class . '::json');
        $group->get('/doc.yaml', DocController::class . '::yaml');
        $group->get('/doc.yml', DocController::class . '::yaml');

        $group->post('/user/register', UserController::class . '::register');
//        $group->post('/user/login', [UserController::class, 'login']);

//        $group->group('', function (Group $group) {
//            $group->get('/user', [UserController::class, 'get']);
//        })->add(AuthorizationMiddleware::class);
    });
    $app->any('/[{path:.*}]', NotFoundController::class . '::notFound');
};