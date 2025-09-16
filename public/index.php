<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/Router.php';
require __DIR__ . '/../app/LinkModel.php';
require __DIR__ . '/../app/LinkController.php';

use App\Router;
use App\LinkController;

$router = new Router();

$router->get('/', [LinkController::class, 'home']);

$router->post('/create', [LinkController::class, 'create']);
$router->post('/delete', [LinkController::class, 'delete']);

$router->get('/r/{code}', [LinkController::class, 'go']);

$router->dispatch();
