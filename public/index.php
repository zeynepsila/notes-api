<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();

// JSON dönüşleri düzgün olsun diye
$app->addBodyParsingMiddleware();

// Rotaları yükle
(require __DIR__ . '/../src/Routes/api.php')($app);

$app->run();
