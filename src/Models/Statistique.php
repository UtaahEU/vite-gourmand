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

    // US30 + US31 : nombre de commandes par menu et chiffre d'affaires par menu (commande annulées exclues)
    // Filtres facultatifs : menu (id), du / au (dates AAAA-MM-JJ, heure de Paris)
    // Calcul fait par MongoDB lui même ("pipeline d'agrégation"). Renvoie null si MongoDB ne répond pas.
    public static function parMenu(array $filtres = []): ?array
    {
        try {
            $condition = ['statut' => ['$ne' => 'annulee']];
            if (!empty($filtres['menu'])) {
                $condition['menu_id'] = (int) $filtres['menu'];
            }
            if (!empty($filtres['du'])) {
                $condition['date_commande']['$gte'] = self::dateMongo($filtres['du']);
            }
            if (!empty($filtres['au'])) {
                // Jour de fin inclus : on prend tout ce qui est avant minuit le lendemain
                $condition['date_commande']['$lt'] = self::dateMongo($filtres['au'], '+1 day');
            }

            $resultats = Mongo::base()->selectCollection('commandes')->aggregate([
                ['$match' => $condition],
                ['$group' => [
                    '_id' => '$menu_id',
                    'menu_titre' => ['$last' => '$menu_titre'],
                    'nombre_commandes' => ['$sum' => 1],
                    'chiffre_affaires' => ['$sum' => '$prix_total'],
                ]],
                ['$sort' => ['nombre_commandes' => -1]],
            ]);

            $lignes = [];
            foreach ($resultats as $resultat) {
                $lignes[] = [
                    'menu' => (string) $resultat['menu_titre'],
                    'commandes' => (int) $resultat['nombre_commandes'],
                    'chiffre_affaires' => round((float) $resultat['chiffre_affaires'], 2),
                ];
            }
            return $lignes;
        } catch (\Throwable $erreur) {
            self::noterErreur($erreur);
            return null;
        }
    }

    // "2026-11-18" (heure de Paris) -> date MongoDB (stockée en UTC, en millisecondes)
    private static function dateMongo(string $jour, string $decalage = '+0 day'): UTCDateTime
    {
        $date = (new \DateTimeImmutable($jour))->modify($decalage);
        return new UTCDateTime($date->getTimestamp() * 1000);
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