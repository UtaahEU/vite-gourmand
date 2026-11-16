<?php

namespace App\Models;

use App\Core\Mongo;
use MongoDB\BSON\UTCDateTime;

// Statistiques des commandes, stockées dans MongoDB (collection "commandes")
// Si MongoDB ne répond pas, la commande reste valide : on note seulement l'erreur.
class Statistique
{
    // Copie simplifiée de la commande, utilisée par les graphiques de l'administrateur
    public static function enregistrerCommande(string $numero, array $menu, array $commande): void
    {
        try {
            Mongo::base()->selectCollection('commandes')->insertOne([
                'numero'          => $numero,
                'menu_id'         => (int) $menu['menu_id'],
                'menu_titre'      => $menu['titre'],
                'nombre_personne' => $commande['personnes'],
                'prix_total'      => $commande['prix']['total'],
                'statut'          => 'en_attente',
                'date_commande'   => new UTCDateTime(),
            ]);
        } catch (\Throwable $erreur) {
            self::noterErreur($erreur);
        }
    }

    // Met à jour la copie MongoDB quand la commande change (statut, prix, personnes)
    public static function mettreAJour(string $numero, array $champs): void
    {
        try {
            // '$set' entre apostrophes : c'est un opérateur MongoDB, pas une variable PHP
            Mongo::base()->selectCollection('commandes')->updateOne(['numero' => $numero], ['$set' => $champs]);
        } catch (\Throwable $erreur) {
            self::noterErreur($erreur);
        }
    }

    private static function noterErreur(\Throwable $erreur): void
    {
        error_log(
            date('c') . ' MongoDB : ' . $erreur->getMessage() . PHP_EOL,
            3,
            dirname(__DIR__, 2) . '/logs/erreurs.log'
        );
    }
}