<?php

namespace App\Controllers;

use App\Core\View;

class EmployeController
{
    public function tableauDeBord(): void
    {
        exiger_connexion(ROLES_EMPLOYE);

        View::render('employe/tableau-de-bord', ['titre' => 'Espace employé']);
    }
}