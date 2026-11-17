<?php
namespace App\Models;

use App\Core\Database;

class Horaire
{
    // Les 7 jours dans l'ordre (heures NULL = fermé)
    public static function tous(): array
    {
        return Database::get()
            ->query('SELECT jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY horaire_id')
            ->fetchAll();
    }
}