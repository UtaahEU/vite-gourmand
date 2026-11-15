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

    // GET /connexion
    public function connexion(): void
    {
        View::render('auth/connexion', [
            'titre' => 'Connexion',
            'erreur' => null,
            'email' => '',
            'retour' => $this->retourSur($_GET['retour'] ?? ''),
        ]);
    }

    // POST /connexion
    public function connecter(): void
    {
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de vous reconnecter.');
            rediriger('/connexion');
        }

        $email = is_string($_POST['email'] ?? null) ? mb_strtolower(trim($_POST['email'])) : '';
        $motDePasse= is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $retour = $this->retourSur($_POST['retour'] ?? '');

        $compte = Utilisateur::parEmail($email);

        // Même message que l'e-mail ou le mot de passe soit faux :
        // on ne révèle pas à un pirate quelles adresses ont un compte
        if ($compte === null || !password_verify($motDePasse, $compte['password'])) {
            $erreur = 'Email ou mot de passe incorrect.';
        } elseif (!$compte['actif']) {
            $erreur = 'Votre compte a été désactivé. Contactez-nous pour plus d\'informations.'; // RG13
        } else {
            // Nouvel identifiant de session à la connexion (protection contre la fixation de session)
            session_regenerate_id(true);
            $_SESSION['utilisateur'] = [
                'id' => (int) $compte['utilisateur_id'],
                'prenom' => $compte['prenom'],
                'nom' => $compte['nom'],
                'email' => $compte['email'],
                'role' => $compte['role']
            ];

            flash('succes', 'Bonjour ' . $compte['prenom'] . ', vous êtes connecté.');
            rediriger($retour ?: '/');
        }

        http_response_code(401);
        View::render('auth/connexion', [
            'titre' => 'Connexion',
            'erreur' => $erreur,
            'email' => $email,
            'retour' => $retour
        ]);
    }

    // POST /deconnexion (en POST + CSRF : un lien piégé ne peut pas déconnecter quelqu'un)
    public function deconnecter(): void
    {
        if (csrf_valide()) {
            $_SESSION = [];
            session_regenerate_id(true);
            flash('succes', 'Vous êtes déconnecté. À bientôt !');
        }
        rediriger('/');
    }

    // N'accepte qu'une adrezsse interne au site ("/menu?id=1")
    private function retourSur(mixed $retour): string
    {
        if (!is_string($retour) || !str_starts_with($retour, '/') || str_starts_with($retour, '//')
            || str_contains($retour, '\\')) {
            return '';
        }
        return $retour;
    }
}