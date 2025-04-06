<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Middleware\VerifyTokenMiddleware;
use App\Controllers\NoteController;

return function (App $app) {
    $container = require __DIR__ . '/../config.php';

    $app->post('/register', function ($request, $response) use ($container) {
        $controller = new AuthController($container);
        return $controller->register($request, $response);
    });

    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode(['message' => 'notes-api çalışıyor 🚀']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/login', function ($request, $response) use ($container) {
        $controller = new AuthController($container);
        return $controller->login($request, $response);
    });

    $app->get('/me', function ($request, $response) use ($container) {
        $controller = new AuthController($container);
        return $controller->me($request, $response);
    })->add(new VerifyTokenMiddleware());

    $app->post('/notes', function ($request, $response) use ($container) {
        $controller = new NoteController($container);
        return $controller->create($request, $response);
    })->add(new VerifyTokenMiddleware());
    
    $app->get('/notes', function ($request, $response) use ($container) {
        $controller = new NoteController($container);
        return $controller->list($request, $response);
    })->add(new VerifyTokenMiddleware());

    $app->put('/notes/{id}', function ($request, $response, $args) use ($container) {
        $controller = new NoteController($container);
        return $controller->update($request, $response, $args);
    })->add(new VerifyTokenMiddleware());
    
    $app->delete('/notes/{id}', function ($request, $response, $args) use ($container) {
        $controller = new NoteController($container);
        return $controller->delete($request, $response, $args);
    })->add(new VerifyTokenMiddleware());
    
    $app->get('/notes/{id}', function ($request, $response, $args) use ($container) {
        $controller = new NoteController($container);
        return $controller->show($request, $response, $args);
    })->add(new VerifyTokenMiddleware());
    
};
