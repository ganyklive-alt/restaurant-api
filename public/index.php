<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Router;
use App\Controllers\OrderController;

$router = new Router();

$router->post('/orders', [OrderController::class, 'create']);
$router->get('/orders/active', [OrderController::class, 'listActive']);
$router->post('/orders/{id}/complete', [OrderController::class, 'complete']);

$router->dispatch();