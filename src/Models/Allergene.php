<?php

namespace App\Models;

use App\Core\Database;

class Allergene
{
    // Les 14 allergènes majeurs (règlement européen INCO) : liste fixe, triée par nom
    public static function tous(): array
    {
        return Database::get()->query('SELECT allergene_id, libelle FROM allergene ORDER BY libelle')->fetchAll();
    }
}