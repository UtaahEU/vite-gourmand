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

        // Avis déjà laissé pour une commande, ou null
    public static function pourCommande(int $commandeId): ?array
    {
        $requete = Database::get()->prepare('SELECT note, commentaire, statut FROM avis WHERE commande_id = :id');
        $requete->execute(['id' => $commandeId]);

        return $requete->fetch() ?: null;
    }

    // US18 : nouvel avis, en attente de validation par un employé (RG11)
    public static function creer(int $utilisateurId, int $commandeId, int $note, string $commentaire): void
    {
        Database::get()->prepare(
            'INSERT INTO avis (note, commentaire, utilisateur_id, commande_id)
             VALUES (:note, :commentaire, :utilisateur, :commande)'
        )->execute([
            'note'        => $note,
            'commentaire' => $commentaire,
            'utilisateur' => $utilisateurId,
            'commande'    => $commandeId,
        ]);
    }

    
    // US26 : avis en attente de validation, les plus anciens d'abord (premier arrivé, premier traité)
    public static function enAttente(): array
    {
        return Database::get()->query(
            "SELECT a.avis_id, a.note, a.commentaire, a.date_creation,
                    u.prenom, u.nom, c.numero_commande, m.titre
             FROM avis a
             JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
             JOIN commande c ON c.commande_id = a.commande_id
             JOIN menu m ON m.menu_id = c.menu_id
             WHERE a.statut = 'en_attente'
             ORDER BY a.date_creation"
        )->fetchAll();
    }

    // US26 : valide ou refuse un avis encore en attente.
    // false si un autre employé l'a déjà traité entre-temps (WHERE statut = 'en_attente').
    public static function moderer(int $id, string $statut): bool
    {
        $requete = Database::get()->prepare(
            "UPDATE avis SET statut = :statut WHERE avis_id = :id AND statut = 'en_attente'"
        );
        $requete->execute(['statut' => $statut, 'id' => $id]);

        return $requete->rowCount() === 1;
    }
}