<?php

namespace App\Controllers;

use App\Core\View;

// Pages d'information statiques
class PageController
{
    public function mentionsLegales(): void
    {
        View::render('pages/mentions-legales', ['titre' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        View::render('pages/cgv', ['titre' => 'Conditions générales de vente']);
    }
}