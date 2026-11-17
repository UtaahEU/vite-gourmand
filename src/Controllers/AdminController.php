<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;
use App\Models\Utilisateur;

// Point 17 : l'administrateur gère les comptes employés (US27 créer, US28 désactiver)
class AdminController
{
    private const ROLE_ADMIN = ['administrateur'];

    // GET /admin/employes
    public function employes(): void
    {
        exiger_connexion(self::ROLE_ADMIN);
        $this->afficher([], []);
    }

    // POST /admin/employes (US27)
    public function creerEmploye(): void
    {
        exiger_connexion(self::ROLE_ADMIN);
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger('/admin/employes');
        }

        $texte = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $valeurs = [
            'prenom' => $texte('prenom'),
            'nom' => $texte('nom'),
            'email' => mb_strtolower($texte('email')),
        ];
        // Le mot de passe n'est pas "trimé" : un espace peut en faire partie
        $motDePasse = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        $erreurs = [];
        if ($valeurs['prenom'] === '' || mb_strlen($valeurs['prenom']) > 50) {
            $erreurs['prenom'] = 'Indiquez le prénom (50 caractères au plus).';
        }
        if ($valeurs['nom'] === '' || mb_strlen($valeurs['nom']) > 50) {
            $erreurs['nom'] = 'Indiquez le nom (50 caractères au plus).';
        }
        if (!filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Indiquez une adresse e-mail valide.';
        } elseif (Utilisateur::emailExiste($valeurs['email'])) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse.';
        }
        if (!preg_match(AuthController::REGEX_MOT_DE_PASSE, $motDePasse)) {
            $erreurs['password'] = 'Le mot de passe ne respecte pas les règles indiquées.';
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficher($erreurs, $valeurs);
            return;
        }

        Utilisateur::creerEmploye($valeurs + ['password' => password_hash($motDePasse, PASSWORD_DEFAULT)]);

        // US27 : l'e-mail ne contient PAS le mot de passe (l'administrateur le transmet en main propre)
        Mailer::envoyer(
            $valeurs['email'],
            'Votre compte employé Vite & Gourmand',
            "Bonjour {$valeurs['prenom']},\n\n"
            . "Un compte employé vient d'être créé pour vous sur le site Vite & Gourmand.\n\n"
            . "Votre identifiant : {$valeurs['email']}\n"
            . "Votre mot de passe vous sera communiqué directement par José.\n\n"
            . "Connexion : " . rtrim($_ENV['APP_URL'], '/') . "/connexion\n\n"
            . "Bienvenue dans l'équipe !\nJosé – Vite & Gourmand"
        );

        flash('succes', "Compte employé créé pour {$valeurs['prenom']} {$valeurs['nom']}. Un e-mail lui a été envoyé.");
        rediriger('/admin/employes');
    }

    // POST /admin/employe/actif (US28) : actif = 0 pour désactiver, 1 pour réactiver
    public function changerActif(): void
    {
        exiger_connexion(self::ROLE_ADMIN);
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger('/admin/employes');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $actif = ($_POST['actif'] ?? '') === '1';

        if ($id && Utilisateur::changerActif($id, $actif)) {
            flash('succes', $actif ? 'Compte réactivé.' : 'Compte désactivé : cette personne ne peut plus se connecter.');
        } else {
            flash('erreur', 'Aucun changement : compte introuvable ou déjà dans cet état.');
        }
        rediriger('/admin/employes');
    }

    private function afficher(array $erreurs, array $valeurs): void
    {
        View::render('admin/employes', [
            'titre' => 'Comptes employés',
            'employes' => Utilisateur::employes(),
            'erreurs' => $erreurs,
            'valeurs' => $valeurs + ['prenom' => '', 'nom' => '', 'email' => ''],
        ]);
    }
}