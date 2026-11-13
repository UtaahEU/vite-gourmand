<?php

namespace App\Core;

// Affiche une vue à l'intérieur du gabarit commun (view/layout.php)
class View
{
    public static function render(string $vue, array $donnees = []): void
    {
        extract($donnees);

        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $vue . '.php';
        $contenu = ob_get_clean();

        require dirname(__DIR__,2) . '/views/layout.php';
    }
}