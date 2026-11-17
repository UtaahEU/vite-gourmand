<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Horaire;

// US22 : l'employé modifie les horaires affichés dans le pied de page
class GestionHoraireController
{
    // GET /employe/horaires
    public function formulaire(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        View::render('employe/horaires', ['titre' => 'Horaires', 'jours' => Horaire::tous(), 'erreurs' => []]);
    }

    // POST /employe/horaires
    public function enregistrer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger('/employe/horaires');
        }

        // Le formulaire envoie horaires[id][ferme], horaires[id][ouverture], horaires[id][fermeture]
        $saisie = is_array($_POST['horaires'] ?? null) ? $_POST['horaires'] : [];
        $texte = fn(array $ligne, string $cle): string => is_string($ligne[$cle] ?? null) ? trim($ligne[$cle]) : '';
        $format = '/^([01]\d|2[0-3]):[0-5]\d$/';

        $horaires = [];
        $jours = [];   // pour réafficher le formulaire tel que l'employé l'a rempli
        $erreurs = [];

        // On part des 7 jours de la base : un id inventé dans le formulaire est ignoré
        foreach (Horaire::tous() as $jour) {
            $id = (int) $jour['horaire_id'];
            $ligne = is_array($saisie[$id] ?? null) ? $saisie[$id] : [];
            $ferme = isset($ligne['ferme']);
            $ouverture = $texte($ligne, 'ouverture');
            $fermeture = $texte($ligne, 'fermeture');

            $jours[] = [
                'horaire_id' => $id,
                'jour' => $jour['jour'],
                'heure_ouverture' => $ferme ? null : $ouverture,
                'heure_fermeture' => $ferme ? null : $fermeture,
            ];

            if ($ferme) {
                $horaires[$id] = ['ouverture' => null, 'fermeture' => null];
                continue;
            }

            // "09:00" < "18:00" : au format HH:MM, la comparaison de textes suit l'ordre des heures
            if (!preg_match($format, $ouverture) || !preg_match($format, $fermeture) || $ouverture >= $fermeture) {
                $erreurs[$id] = "Indiquez une heure d'ouverture avant l'heure de fermeture, ou cochez « Fermé ».";
            }
            $horaires[$id] = ['ouverture' => $ouverture, 'fermeture' => $fermeture];
        }

        if ($erreurs !== []) {
            http_response_code(422);
            View::render('employe/horaires', ['titre' => 'Horaires', 'jours' => $jours, 'erreurs' => $erreurs]);
            return;
        }

        Horaire::enregistrer($horaires);
        flash('succes', 'Horaires enregistrés : ils sont à jour dans le pied de page.');
        rediriger('/employe/horaires');
    }
}