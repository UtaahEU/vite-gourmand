<?php

namespace App\Core;

use PDO;

// Connexion unique à la base (singleton) : on ne l'ouvre qu'une seule fois par requête
class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME']
            );

            self::$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs SQL = exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // résultats en tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
            ]);
        }

        return self::$pdo;
    }
}