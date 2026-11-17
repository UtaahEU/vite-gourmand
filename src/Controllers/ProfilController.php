<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Utilisateur;

class ProfilController
{
    // GET /mon-compte/profil (US19)
    public function afficher(): void
    {
        exiger_connexion();
        $this->afficherFormulaire(Utilisateur::trouver(utilisateur()['id']), []);
    }

    // POST /mon-compte/profil
    public function enregistrer(): void
    {
        exiger_connexion();
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/mon-compte/profil');
        }

        $lire = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $saisie = [
            'prenom' => $lire('prenom'),
            'nom' => $lire('nom'),
            'telephone' => str_replace([' ', '.', '-'], '', $lire('telephone')),
            'adresse_postale' => $lire('adresse_postale'),
            'code_postal' => $lire('code_postal'),
            'ville' => $lire('ville'),
        ];

        // Mêmes règles qu'à l'inscription
        $erreurs = [];
        foreach (['prenom' => 'votre prénom', 'nom' => 'votre nom', 'ville' => 'votre ville'] as $champ => $libelle) {
            if ($saisie[$champ] === '' || mb_strlen($saisie[$champ]) > 50) {
                $erreurs[$champ] = "Indiquez $libelle (50 caractères maximum).";
            }
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $saisie['telephone'])) {
            $erreurs['telephone'] = 'Indiquez un numéro à 10 chiffres, par exemple 06 12 34 56 78.';
        }
        if ($saisie['adresse_postale'] === '' || mb_strlen($saisie['adresse_postale']) > 255) {
            $erreurs['adresse_postale'] = 'Indiquez votre adresse (numéro et rue).';
        }
        if (!preg_match('/^\d{5}$/', $saisie['code_postal'])) {
            $erreurs['code_postal'] = 'Le code postal doit contenir 5 chiffres.';
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficherFormulaire($saisie + ['email' => utilisateur()['email']], $erreurs);
            return;
        }

        Utilisateur::modifierProfil(utilisateur()['id'], $saisie);

        // Le menu affiche le prénom : on met aussi la session à jour
        $_SESSION['utilisateur']['prenom'] = $saisie['prenom'];
        $_SESSION['utilisateur']['nom'] = $saisie['nom'];

        flash('succes', 'Vos informations ont été enregistrées.');
        rediriger('/mon-compte/profil');
    }

    private function afficherFormulaire(array $profil, array $erreurs): void
    {
        View::render('compte/profil', ['titre' => 'Mon profil', 'profil' => $profil, 'erreurs' => $erreurs]);
    }
}