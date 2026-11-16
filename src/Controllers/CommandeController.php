<?php

namespace App\Controllers;

use App\Core\Adresse;
use App\Core\View;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Utilisateur;

class CommandeController
{
    // GET /commande?menu=ID : formulaire prérempli avec le compte du client
    public function formulaire(): void
    {
        exiger_connexion();
        $menu =$this->menuCommandable(filter_input(INPUT_GET, 'menu', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));
        $client = Utilisateur::trouver(utilisateur()['id']);

        $this->afficherFormulaire($menu, $client, [
            'personnes' => $menu['nombre_personne_minimum'],
            'date' => '',
            'heure' => '',
            'adresse' => $client['adresse_postale'] ?? '',
            'code_postal' => $client['code_postal'] ?? '',
            'ville' => $client['ville'] ?? '',
        ], []);
    }

    // POST /commande : vérifie, calcule le prix, puis affiche le récapitulatif
    public function verifier(): void
    {
        exiger_connexion();
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/menus');
        }

        $menu = $this->menuCommandable(filter_input(INPUT_POST, 'menu', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));
        $client = Utilisateur::trouver(utilisateur()['id']);

        $texte = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $valeurs = [
            'personnes' => $texte('personnes'),
            'date' => $texte('date'),
            'heure' => $texte('heure'),
            'adresse' => $texte('adresse'),
            'code_postal' => $texte('code_postal'),
            'ville' => $texte('ville'),
        ];

        $erreurs = [];
        $minimum = (int) $menu['nombre_personne_minimum'];
        $personnes = filter_var($valeurs['personnes'], FILTER_VALIDATE_INT);
        if ($personnes === false || $personnes < $minimum) { // RG2
            $erreurs['personnes'] = "Ce menu se commande pour $minimum personnes minimum.";
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $valeurs['date']);
        if (!$date || $date->format('Y-m-d') !== $valeurs['date'] || $date <= new \DateTime('today')) {
            $erreurs['date'] = 'Choisissez une date à partir de demain.';
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valeurs['heure'])) {
            $erreurs['heure'] = 'Indiquez une heure valide.';
        }
        if ($valeurs['adresse'] === '' || mb_strlen($valeurs['adresse']) > 255) {
            $erreurs['adresse'] = 'Indiquez l\'adresse de livraison.';
        }
        if (!preg_match('/^\d{5}$/', $valeurs['code_postal'])) {
            $erreurs['code_postal'] = 'Le code postal doit contenir 5 chiffres.';
        }
        if ($valeurs['ville'] === '' || mb_strlen($valeurs['ville']) > 100) {
            $erreurs['ville'] = 'Indiquez la ville.';
        }

        // On n'interroge l'API que si le reste du formulaire est correct
        $lieu = null;
        if ($erreurs === []) {
            $lieu = Adresse::localiser($valeurs['adresse'], $valeurs['code_postal'], $valeurs['ville']);
            if ($lieu === null) {
                $erreurs['adresse'] = 'Adresse introuvable. Vérifiez l\'adresse, le code postal et la ville.';
            }
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficherFormulaire($menu, $client, $valeurs, $erreurs);
            return;
        }

        // Le prix est calculé côte serveur et gardé en session : le client ne peut pas le modifier
        $_SESSION['commande'] = [
            'menu_id' => (int) $menu['menu_id'],
            'personnes' => $personnes,
            'date' => $valeurs['date'],
            'heure' => $valeurs['heure'],
            'adresse' => $valeurs['adresse'],
            'code_postal' => $valeurs['code_postal'],
            'ville' => $lieu['ville'],
            'km' => $lieu['km'],
            'prix' => Commande::calculerPrix($menu, $personnes, $lieu['bordeaux'], $lieu['km']),
        ];

        rediriger('/commande/recapitulatif');
    }

    // GET /commande/recapitulatif
    public function recapitulatif(): void
    {
        exiger_connexion();
        $commande = $_SESSION['commande'] ?? null;
        if ($commande === null) {
            rediriger('/menus');
        }

        View::render('commandes/recapitulatif', [
            'titre' => 'Récapitulatif de la commande',
            'commande' => $commande,
            'menu' => Menu::trouver($commande['menu_id']),
            'client' => Utilisateur::trouver(utilisateur()['id']),
        ]);
    }

    // POST /commande/confirmer : enregistrement définitif
    public function confirmer(): void
    {
        exiger_connexion();
        $commande = $_SESSION['commande'] ?? null;
        if (!csrf_valide() || $commande === null) {
            flash('erreur', 'Votre session a expiré. Merci de recommencer la commande.');
            rediriger('/menus');
        }

        $numero = Commande::creer(utilisateur()['id'], $commande);
        unset($_SESSION['commande']);

        if ($numero === null) {
            flash('erreur', 'Désolé, ce menu vient d\'être épuisé.');
            rediriger('/menu?id=' . $commande['menu_id']);
        }

        flash('succes', "Merci ! Votre commande n° $numero est enregistrée.");
        rediriger('/');
    }

    // Menu existant et encore en stock, sinon 404 ou retrour à la fiche du menu
    private function menuCommandable(mixed $id): array
    {
        $menu = $id ? Menu::trouver($id) : null;

        if ($menu === null) {
            http_response_code(404);
            View::render('erreurs/404', ['titre' => 'Page introuvable']);
            exit;
        }
        if ($menu['quantite_restante'] < 1) {
            flash('erreur', 'Ce menu n\'est plus disponible pour le moment.');
            rediriger('/menu?id=' . $menu['menu_id']);
        }

        return $menu;
    }

    private function afficherFormulaire(array $menu, array $client, array $valeurs, array $erreurs): void
    {
        View::render('commandes/formulaire', [
            'titre' => 'Commander : ' . $menu['titre'],
            'menu' => $menu,
            'client' => $client,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
        ]);
    }
}