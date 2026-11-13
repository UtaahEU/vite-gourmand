<?php

namespace App\Core;

// Associe une URL + une méthode HTTP à une méthode de contrôleur
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $chemin, array $action): void
    {
        $this->routes['GET'][$chemin] = $action;
    }

    public function post(string $chemin, array $action): void
    {
        $this->routes['POST'][$chemin] = $action;
    }

    public function dispatch(string $uri, string $methode): void
    {
        $chemin = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($chemin !== '/') {
            $chemin = rtrim($chemin, '/');
        }

        $action = $this->routes[$methode][$chemin] ?? null;

        if ($action === null) {
            http_response_code(404);
            View::render('erreurs/404', ['titre' => 'Page introuvable']);
            return;
        }

        [$classe, $methodeControleur] = $action;
        (new $classe())->$methodeControleur();
    }
}