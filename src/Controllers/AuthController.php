<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;
use App\Models\Utilisateur;

class AuthController
{
    // RG1 : 10 caractères minimum, avec une minuscule, une majuscule, un chiffre et un caractère spécial
    private const REGEX_MOT_DE_PASSE = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/';

    // GET /inscription
    public function inscription(): void
    {
        View::render('auth/inscription', ['titre' => 'Créer un compte', 'erreurs' => [], 'saisie' => []]);
    }

    // POST /inscription
    public function inscrire(): void
    {
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/inscription');
        }

        $lire = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $saisie = [
            'prenom'          => $lire('prenom'),
            'nom'             => $lire('nom'),
            'telephone'       => str_replace([' ', '.', '-'], '', $lire('telephone')),
            'email'           => mb_strtolower($lire('email')),
            'adresse_postale' => $lire('adresse_postale'),
            'code_postal'     => $lire('code_postal'),
            'ville'           => $lire('ville'),
        ];
        // Le mot de passe n'est pas "trimé" : un espace peut en faire partie
        $motDePasse   = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmation = is_string($_POST['confirmation'] ?? null) ? $_POST['confirmation'] : '';

        $erreurs = [];
        foreach (['prenom' => 'votre prénom', 'nom' => 'votre nom', 'ville' => 'votre ville'] as $champ => $libelle) {
            if ($saisie[$champ] === '' || mb_strlen($saisie[$champ]) > 50) {
                $erreurs[$champ] = "Indiquez $libelle (50 caractères maximum).";
            }
        }
        if (!preg_match('/^0[1-9]\d{8}$/', $saisie['telephone'])) {
            $erreurs['telephone'] = 'Indiquez un numéro à 10 chiffres, par exemple 06 12 34 56 78.';
        }
        if (!filter_var($saisie['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($saisie['email']) > 255) {
            $erreurs['email'] = 'Indiquez une adresse e-mail valide, par exemple nom@exemple.fr.';
        } elseif (Utilisateur::emailExiste($saisie['email'])) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
        if ($saisie['adresse_postale'] === '' || mb_strlen($saisie['adresse_postale']) > 255) {
            $erreurs['adresse_postale'] = 'Indiquez votre adresse (numéro et rue).';
        }
        if (!preg_match('/^\d{5}$/', $saisie['code_postal'])) {
            $erreurs['code_postal'] = 'Le code postal doit contenir 5 chiffres.';
        }
        if (!preg_match(self::REGEX_MOT_DE_PASSE, $motDePasse)) {
            $erreurs['password'] = 'Le mot de passe ne respecte pas les règles indiquées.';
        } elseif ($motDePasse !== $confirmation) {
            $erreurs['confirmation'] = 'Les deux mots de passe ne correspondent pas.';
        }

        if ($erreurs !== []) {
            http_response_code(422);
            View::render('auth/inscription', ['titre' => 'Créer un compte', 'erreurs' => $erreurs, 'saisie' => $saisie]);
            return;
        }

        // Le mot de passe n'est jamais stocké en clair : on enregistre seulement son hash
        Utilisateur::creer($saisie + ['password' => password_hash($motDePasse, PASSWORD_DEFAULT)]);

        Mailer::envoyer(
            $saisie['email'],
            'Bienvenue chez Vite & Gourmand',
            "Bonjour {$saisie['prenom']},\n\n"
            . "Votre compte a bien été créé. Vous pouvez dès maintenant commander nos menus sur notre site.\n\n"
            . "À bientôt,\nJulie et José – Vite & Gourmand"
        );

        flash('succes', 'Votre compte est créé ! Un e-mail de bienvenue vous a été envoyé.');
        rediriger('/'); // deviendra /connexion au point 8
    }
}