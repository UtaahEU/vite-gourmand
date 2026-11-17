<?php

namespace App\Models;

use App\Core\Database;

class Plat
{
    // Les 3 types possibles (colonne ENUM de la table plat) et leur libellé affiché
    public const TYPES = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];

    // Tous les plats, triés entrée -> plat -> dessert (US20, US21)
    public static function tous(): array
    {
        return Database::get()->query(
            "SELECT plat_id, titre_plat, type FROM plat
             ORDER BY FIELD(type, 'entree', 'plat', 'dessert'), titre_plat"
        )->fetchAll();
    }

    // US21 : les plats avec leurs allergènes et le nombre de menus qui les utilisent
    public static function pourGestion(): array
    {
        return Database::get()->query(
            "SELECT p.plat_id, p.titre_plat, p.type,
                    GROUP_CONCAT(DISTINCT a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes,
                    COUNT(DISTINCT mp.menu_id) AS nb_menus
             FROM plat p
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             LEFT JOIN menu_plat mp ON mp.plat_id = p.plat_id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type, 'entree', 'plat', 'dessert'), p.titre_plat"
        )->fetchAll();
    }

    // Un plat, ou null s'il n'existe pas
    public static function trouver(int $id): ?array
    {
        $requete = Database::get()->prepare('SELECT plat_id, titre_plat, type FROM plat WHERE plat_id = :id');
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
    }

    // Ids des allergènes d'un plat (pour cocher les cases du formulaire)
    public static function allergenesIds(int $id): array
    {
        $requete = Database::get()->prepare('SELECT allergene_id FROM plat_allergene WHERE plat_id = :id');
        $requete->execute(['id' => $id]);

        return array_map('intval', $requete->fetchAll(\PDO::FETCH_COLUMN));
    }

    // US21 : crée (id null) ou modifie un plat et ses allergènes. Transaction : tout ou rien.
    public static function enregistrer(?int $id, string $titre, string $type, array $allergenes): int
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            if ($id === null) {
                $pdo->prepare('INSERT INTO plat (titre_plat, type) VALUES (:titre, :type)')
                    ->execute(['titre' => $titre, 'type' => $type]);
                $id = (int) $pdo->lastInsertId();
            } else {
                $pdo->prepare('UPDATE plat SET titre_plat = :titre, type = :type WHERE plat_id = :id')
                    ->execute(['titre' => $titre, 'type' => $type, 'id' => $id]);
                // Les allergènes sont remplacés par ceux du formulaire
                $pdo->prepare('DELETE FROM plat_allergene WHERE plat_id = :id')->execute(['id' => $id]);
            }

            $ajout = $pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (:plat, :allergene)');
            foreach ($allergenes as $allergeneId) {
                $ajout->execute(['plat' => $id, 'allergene' => $allergeneId]);
            }

            $pdo->commit();
            return $id;
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    // US21 : suppression refusée si le plat est dans un menu (sinon ON DELETE CASCADE
    // le retirerait des menus sans prévenir). Renvoie le nombre de menus qui l'utilisent (0 = supprimé).
    public static function supprimer(int $id): int
    {
        $requete = Database::get()->prepare('SELECT COUNT(*) FROM menu_plat WHERE plat_id = :id');
        $requete->execute(['id' => $id]);
        $nbMenus = (int) $requete->fetchColumn();

        if ($nbMenus === 0) {
            Database::get()->prepare('DELETE FROM plat WHERE plat_id = :id')->execute(['id' => $id]);
        }
        return $nbMenus;
    }
}