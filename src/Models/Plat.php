<?php

namespace App\Models;

use App\Core\Database;

class Plat
{
    // Tous les plats, triés entrée -> plat -> dessert (US20, US21)
    public static function tous(): array
    {
        return Database::get()->query(
            "SELECT plat_id, titre_plat, type FROM plat
             ORDER BY FIELD(type, 'entree', 'plat', 'dessert'), titre_plat"
        )->fetchAll();
    }
}