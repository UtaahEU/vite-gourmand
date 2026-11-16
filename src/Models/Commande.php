<?php

namespace App\Models;

use App\Core\Database;

class Commande
{
    // RG3 : -10 % à partir du minimum + 5 personnes / RG4 : Livraison gratuite à Bordeaux, sinon 5 € + 0,59 €/km
    public static function calculerPrix(array $menu, int $personnes, bool $bordeaux, float $km): array
    {
        $prixMenu = round($menu['prix_par_personne'] * $personnes, 2);
        $reduction = $personnes >= $menu['nombre_personne_minimum'] + 5 ? round($prixMenu * 0.10, 2) : 0.0;
        $livraison = $bordeaux ? 0.0 : round(5 + 0.59 * $km, 2);

        return [
            'menu' => $prixMenu,
            'reduction' => $reduction,
            'livraison' => $livraison,
            'total' => round($prixMenu - $reduction +$livraison, 2),
        ];
    }

    // RG5 : vérifie et baisse le stock, créer la commande et son premier suivi.
    // Transaction : tout est enregistré, ou rien. Renvoie le numéro, ou null si le menu est épuisé.
    public static function creer(int $utilisateurId, array $c) : ?string
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            // FOR UPDATE verrouille la ligne : deux clients ne peuvent pas prendre le dernier menu en même temps
            $requete = $pdo->prepare('SELECT quantite_restante FROM menu WHERE menu_id = :id FOR UPDATE');
            $requete->execute(['id' => $c['menu_id']]);
            if ((int) $requete->fetchColumn() < 1) {
                $pdo->rollBack();
                return null;
            }

            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = :id')
                -> execute(['id' => $c['menu_id']]);

            $numero = 'VG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $pdo->prepare(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                    adresse_livraison, code_postal_livraison, ville_livraison, distance_km, nombre_personne,
                    prix_menu, reduction, prix_livraison, prix_total)
                VALUES (:numero, :utilisateur, :menu, :date, :heure, :adresse, :cp, :ville, :km, :personnes,
                    :prix_menu, :reduction, :livraison, :total)'
            )->execute([
                'numero' => $numero,
                'utilisateur' => $utilisateurId,
                'menu' => $c['menu_id'],
                'date' => $c['date'],
                'heure' => $c['heure'],
                'adresse' => $c['adresse'],
                'cp' => $c['code_postal'],
                'ville' => $c['ville'],
                'km' => $c['km'],
                'personnes' => $c['personnes'],
                'prix_menu' => $c['prix']['menu'],
                'reduction' => $c['prix']['reduction'],
                'livraison' => $c['prix']['livraison'],
                'total' => $c['prix']['total'],
            ]);

            // Premier suivi horodaté (RG8)
            $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, 'en_attente')")
                ->execute(['id' => (int) $pdo->lastInsertId()]);
            
            $pdo->commit();
            return $numero;
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }
}