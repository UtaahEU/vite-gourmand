<?php 

namespace App\Models;

use App\Core\Database;

class Theme
{
    public static function tous(): array
    {
        return Database::get()->query('SELECT theme_id, libelle FROM theme ORDER BY theme_id')->fetchAll();
    }
}