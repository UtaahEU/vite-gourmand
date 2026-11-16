<?php

namespace App\Controllers;

use App\Core\Adresse;
use App\Core\Mailer;
use App\Core\View;
use App\Models\Avis;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Statistique;
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

        // Modification d'une commande existante (le menu ne change pas) ou nouvelle commande
        $commandeId = filter_input(INPUT_POST, 'commande', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $menu = $commandeId
            ? Menu::trouver($this->commandeModifiable($commandeId)['menu_id'])
            : $this->menuCommandable(filter_input(INPUT_POST, 'menu', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));
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
            $this->afficherFormulaire($menu, $client, $valeurs, $erreurs, $commandeId);
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
            'commande_id' => $commandeId,
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

        if (!empty($commande['commande_id'])) {
            $this->enregistrerModification($commande);
        }

        $numero = Commande::creer(utilisateur()['id'], $commande);
        unset($_SESSION['commande']);

        if ($numero === null) {
            flash('erreur', 'Désolé, ce menu vient d\'être épuisé.');
            rediriger('/menu?id=' . $commande['menu_id']);
        }

        $menu = Menu::trouver($commande['menu_id']);
        $this->envoyerConfirmation($numero, $menu, $commande);
        Statistique::enregistrerCommande($numero, $menu, $commande);

        flash('succes', "Merci ! Votre commande n° $numero est enregistrée. Un e-mail de confirmation vous a été envoyé.");
        rediriger('/');
    }

    // GET /mon-compte/commandes (US15)
    public function mesCommandes(): void
    {
        exiger_connexion();

        View::render('compte/commandes', [
            'titre' => 'Mes commandes',
            'commandes' => Commande::duClient(utilisateur()['id']),
        ]);
    }

    // GET /mon-compte/commande?id=N : détail et suivi (US17)
    public function suivi(): void
    {
        exiger_connexion();
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $commande = $id ? Commande::trouverPourClient($id, utilisateur()['id']) : null;

        // Commande inexistante OU d'un autre client : même réponse 404
        if ($commande === null) {
            http_response_code(404);
            View::render('erreurs/404', ['titre' => 'Page introuvable']);
            return;
        }

        View::render('compte/commande', [
            'titre' => 'Commande ' . $commande['numero_commande'],
            'commande' => $commande,
            'suivi' => Commande::suivi($id),
            'avis' => Avis::pourCommande($id),
        ]);
    }

    // GET /mon-compte/commande/modifier?id=N : formulaire prérempli avec la commande (US16)
    public function modifier(): void
    {
        exiger_connexion();
        $commande = $this->commandeModifiable(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));

        $this->afficherFormulaire(Menu::trouver($commande['menu_id']), Utilisateur::trouver(utilisateur()['id']), [
            'personnes'   => $commande['nombre_personne'],
            'date'        => $commande['date_prestation'],
            'heure'       => substr($commande['heure_livraison'], 0, 5),
            'adresse'     => $commande['adresse_livraison'],
            'code_postal' => $commande['code_postal_livraison'],
            'ville'       => $commande['ville_livraison'],
        ], [], (int) $commande['commande_id']);
    }

    // POST /mon-compte/commande/annuler (US16)
    public function annuler(): void
    {
        exiger_connexion();
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger('/mon-compte/commandes');
        }

        $commande = $this->commandeModifiable(filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]));
        Commande::annuler((int) $commande['commande_id']);
        Statistique::mettreAJour($commande['numero_commande'], ['statut' => 'annulee']);

        flash('succes', "La commande n° {$commande['numero_commande']} a été annulée.");
        rediriger('/mon-compte/commande?id=' . $commande['commande_id']);
    }
        // US14 : mail récapitulatif envoyé au client
    private function envoyerConfirmation(string $numero, array $menu, array $c): void
    {
        $client = utilisateur();
        $p = $c['prix'];

        $texte = "Bonjour {$client['prenom']},\n\n"
            . "Merci pour votre commande n° $numero !\n\n"
            . "Menu : {$menu['titre']} pour {$c['personnes']} personnes\n"
            . 'Livraison le ' . date('d/m/Y', strtotime($c['date'])) . " à {$c['heure']}\n"
            . "Adresse : {$c['adresse']}, {$c['code_postal']} {$c['ville']}\n\n"
            . 'Prix du menu : ' . prix($p['menu']) . " €\n"
            . ($p['reduction'] > 0 ? 'Réduction de 10 % : -' . prix($p['reduction']) . " €\n" : '')
            . 'Livraison : ' . prix($p['livraison']) . " €\n"
            . 'Total : ' . prix($p['total']) . " €\n\n"
            . "Vous pouvez modifier ou annuler votre commande tant qu'elle n'a pas été acceptée par notre équipe.\n\n"
            . "Julie et José – Vite & Gourmand";

        Mailer::envoyer($client['email'], "Confirmation de votre commande $numero", $texte);
    }

    // Commande du client connecté, encore en attente (RG6), sinon retour a "Mes commandes"
    private function commandeModifiable(mixed $id): array
    {
        $commande = $id ? Commande::trouverPourClient((int) $id, utilisateur()['id']) : null;

        if ($commande === null || $commande['statut'] !== 'en_attente') {
            flash('erreur', 'Cette commande ne peut plus être modifiée ni annulée. Contactez-nous si besoin.');
            rediriger('/mon-compte/commandes');
        }

        return $commande;
    }

    // Enregistre une modification validée sur le récapitulatif
    private function enregistrerModification(array $commande): never
    {
        $existante = $this->commandeModifiable($commande['commande_id']);
        Commande::modifier((int) $existante['commande_id'], $commande);
        unset($_SESSION['commande']);

        Statistique::mettreAJour($existante['numero_commande'], [
            'nombre_personne' => $commande['personnes'],
            'prix_total' => $commande['prix']['total'],
        ]);

        flash('succes', "Votre commande n° {$existante['numero_commande']} a été modifiée.");
        rediriger('/mon-compte/commande?id=' . $existante['commande_id']);
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

    private function afficherFormulaire(array $menu, array $client, array $valeurs, array $erreurs, ?int $commandeId = null): void
    {
        View::render('commandes/formulaire', [
            'titre' => ($commandeId ? 'Modifier : ' : 'Commander : ') . $menu['titre'],
            'menu' => $menu,
            'client' => $client,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
            'commandeId' => $commandeId,
        ]);
    }
}