<?php
namespace App\Models;

use App\Core\Database;

class Horaire
{
    // Les 7 jours dans l'ordre (heures NULL = fermé)
    public static function tous(): array
    {
        return Database::get()
            ->query('SELECT horaire_id, jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY horaire_id')
            ->fetchAll();
    }

    // US22 : enregistre les 7 jours d'un coup. Transaction : tout est enregistré, ou rien.
    // $horaires = [horaire_id => ['ouverture' => '09:00' ou null, 'fermeture' => '18:00' ou null]]
    public static function enregistrer(array $horaires): void
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare(
                'UPDATE horaire SET heure_ouverture = :ouverture, heure_fermeture = :fermeture WHERE horaire_id = :id'
            );
            foreach ($horaires as $id => $h) {
                $requete->execute(['ouverture' => $h['ouverture'], 'fermeture' => $h['fermeture'], 'id' => $id]);
            }
            $pdo->commit();
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }
}