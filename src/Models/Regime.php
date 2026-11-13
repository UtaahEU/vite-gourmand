<?php

namespace App\Models;

use App\Core\Database;

class Regime
{
    public static function tous(): array
    {
        return Database::get()->query('SELECT regime_id, libelle FROM regime ORDER BY regime_id')->fetchAll();
    }
}