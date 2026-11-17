<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Avis;

class HomeController
{
    public function index(): void
    {
        View::render('accueil', [
            'titre' => 'Accueil',
            'avis' => Avis::valides(3),
        ]);
    }
}