<?php

namespace App\Core;

use App\Models\Horaire;

// Afiche une vue à l'intérieur du gabarit commun (views/layout.php)
class View
{
    public static function render(string $vue, array $donnees = []): void
    {
        extract($donnees);

        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $vue . '.php';
        $contenu = ob_get_clean();
        
        $horaires = Horaire::tous(); // pour le footer

        require dirname(__DIR__, 2) . '/views/layout.php';
    }
}