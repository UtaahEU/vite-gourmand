<?php

namespace App\Core;

use MongoDB\Client;
use MongoDB\Database as BaseMongo;

// Connexion unique à MongoDB Atlas (base NoSQL utilisée pour les statistiques)
class Mongo
{
    private static ?BaseMongo $base = null;

    public static function base(): BaseMongo
    {
        if (self::$base === null) {
            // 3 secondes maximum pour trouver le serveur, pour ne pas bloquer la page
            $client = new Client($_ENV['MONGODB_URI'], ['serverSelectionTimeoutMS' => 3000]);
            self::$base = $client->selectDatabase($_ENV['MONGODB_DB']);
        }

        return self::$base;
    }
}