<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Core\Router;

// Serveur PHP intégré : on laisse passer les fichiers réels (CSS, images, JS)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

// Session sécurisée : cookie inaccessible au JavaScript, protégé contre le CSRF de base
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/menus', [MenuController::class, 'index']);
$router->get('/api/menus', [MenuController::class, 'api']);
$router->get('/menu', [MenuController::class, 'detail']);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);