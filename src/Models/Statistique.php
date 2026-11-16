<?php

namespace App\Models;

use App\Core\Mongo;
use MongoDB\BSON\UTCDateTime;

// Statistiques des commandes, stockées dans MongoDB (collection "commandes")
class Statistique
{
    // Copie simplifiée de la commande, utilisée par les graphiques de l'administrateur.
    // Si MongoDB ne répond pas, la commande reste valide : on note seulement l'erreur.
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
            error_log(
                date('c') . ' MongoDB : ' . $erreur->getMessage() . PHP_EOL,
                3,
                dirname(__DIR__, 2) . '/logs/erreurs.log'
            );
        }
    }
}