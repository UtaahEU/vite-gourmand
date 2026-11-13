<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Avis
{
    // Les derniers avis validés par un employé, avec le prénom et le nom du client
    public static function valides(int $limite = 3): array
    {
        $requete = Database::get()->prepare(
            "SELECT a.note, a.commentaire, u.prenom, u.nom
            FROM avis a
            JOIN utilisateur u on u.utilisateur_id = a.utilisateur_id
            WHERE a.statut = 'valide'
            ORDER BY a.date_creation DESC
            LIMIT :limite"
        );
        $requete->bindValue(':limite', $limite, PDO::PARAM_INT);
        $requete->execute();

        return $requete->fetchAll();
    }
}