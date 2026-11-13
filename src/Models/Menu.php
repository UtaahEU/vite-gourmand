<?php

namespace App\Models;

use App\Core\Database;

class Menu
{
    // Menus correspondant aux filtres (chaque filtre est facultatif : null = ignoré)
    public static function rechercher(array $filtres): array
    {
        $sql = "SELECT m.menu_id, m.titre, m.description, m.nombre_personne_minimum, m.prix_par_personne,
                        t.libelle AS theme, r.libelle AS regime,
                        i.chemin AS image, i.texte_alternatif AS image_alt
                FROM menu m
                JOIN theme t ON t.theme_id = m.theme_id
                JOIN regime r ON r.regime_id = m.regime_id
                LEFT JOIN menu_image i ON i.menu_id = m.menu_id AND i.ordre = 1
                WHERE 1 = 1";
        $parametres = [];

        if ($filtres['prix_max'] !== null) {
            $sql .= " AND m.prix_par_personne <= :prix_max";
            $parametres['prix_max'] = $filtres['prix_max'];
        }
        if ($filtres['fourchette_min'] !== null) {
            $sql .= " AND m.prix_par_personne >= :fourchette_min";
            $parametres['fourchette_min'] = $filtres['fourchette_min'];
        }
        if ($filtres['fourchette_max'] !== null) {
            $sql .= " AND m.prix_par_personne <= :fourchette_max";
            $parametres['fourchette_max'] = $filtres['fourchette_max'];
        }
        if ($filtres['theme'] !== null) {
            $sql .= " AND m.theme_id = :theme";
            $parametres['theme'] = $filtres['theme'];
        }
        if ($filtres['regime'] !== null) {
            $sql .= " AND m.regime_id = :regime";
            $parametres['regime'] = $filtres['regime'];
        }
        if ($filtres['personnes'] !== null) {
            $sql .= " AND m.nombre_personne_minimum <= :personnes";
            $parametres['personnes'] = $filtres['personnes'];
        }

        $sql .= ' ORDER BY m.menu_id';

        $requete = Database::get()->prepare($sql);
        $requete->execute($parametres);

        return $requete->fetchAll();
    }

    // Un menu avec son thème et son régime, ou null s'il n'existe pas
    public static function trouver(int $id): ?array
    {
        $requete = Database::get()->prepare(
            "SELECT m.*, t.libelle AS theme, r.libelle AS regime
            FROM menu m
            JOIN theme t ON t.theme_id = m.theme_id
            JOIN regime r ON r.regime_id = m.regime_id
            WHERE m.menu_id = :id"
        );
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
    }

    // Les images du menu, dans l'ordre de la galerie
    public static function images(int $id): array
    {
        $requete = Database::get()->prepare(
            "SELECT chemin, texte_alternatif FROM menu_image WHERE menu_id = :id ORDER BY ordre"
        );
        $requete->execute(['id' => $id]);

        return $requete->fetchAll();
    }

    // Les plats du menu avec leurs allergènes regroupés, triés entrée -> plat -> dessert
    public static function plats(int $id): array
    {
        $requete = Database::get()->prepare(
            "SELECT p.plat_id, p.titre_plat, p.type,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes
            FROM menu_plat mp
            JOIN plat p ON p.plat_id = mp.plat_id
            LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
            LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
            WHERE mp.menu_id = :id
            GROUP BY p.plat_id
            ORDER BY FIELD(p.type, 'entree', 'plat', 'dessert'), p.titre_plat"
        );
        $requete->execute(['id' => $id]);

        return $requete->fetchAll();
    }
}