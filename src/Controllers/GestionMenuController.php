<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Menu;
use App\Models\Plat;
use App\Models\Regime;
use App\Models\Theme;

// US20 : gestion des menus par l'employé (et l'administrateur, US29)
class GestionMenuController
{
    // GET /employe/menus
    public function liste(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        View::render('employe/menus', ['titre' => 'Gérer les menus', 'menus' => Menu::pourGestion()]);
    }

    // GET /employe/menu (nouveau) ou /employe/menu?id=3 (modification)
    public function formulaire(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        $valeurs = [
            'titre' => '', 'description' => '', 'theme_id' => '', 'regime_id' => '',
            'nombre_personne_minimum' => '', 'prix_par_personne' => '', 'conditions_menu' => '',
            'quantite_restante' => '', 'plats' => [],
            'image1' => '', 'alt1' => '', 'image2' => '', 'alt2' => '',
        ];

        if ($id !== null) {
            $menu = Menu::trouver($id);
            if ($menu === null) {
                http_response_code(404);
                View::render('erreurs/404', ['titre' => 'Page introuvable']);
                return;
            }
            // On garde du menu seulement les champs du formulaire
            $valeurs = array_merge($valeurs, array_intersect_key($menu, $valeurs));
            $valeurs['plats'] = Menu::platsIds($id);
            foreach (Menu::images($id) as $index => $image) {
                if ($index < 2) {
                    $valeurs['image' . ($index + 1)] = $image['chemin'];
                    $valeurs['alt' . ($index + 1)] = $image['texte_alternatif'];
                }
            }
        }

        $this->afficherFormulaire($id, $valeurs, []);
    }

    // POST /employe/menu
    public function enregistrer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/employe/menus');
        }
        if ($id !== null && Menu::trouver($id) === null) {
            rediriger('/employe/menus');
        }

        $lire = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $plats = is_array($_POST['plats'] ?? null) ? $_POST['plats'] : [];

        $v = [
            'titre'                   => $lire('titre'),
            'description'             => $lire('description'),
            'theme_id'                => $lire('theme_id'),
            'regime_id'               => $lire('regime_id'),
            'nombre_personne_minimum' => $lire('nombre_personne_minimum'),
            'prix_par_personne'       => str_replace(',', '.', $lire('prix_par_personne')),
            'conditions_menu'         => $lire('conditions_menu'),
            'quantite_restante'       => $lire('quantite_restante'),
            // On ne garde que les plats qui existent vraiment en base
            'plats'                   => array_values(array_intersect(array_map('intval', $plats), array_column(Plat::tous(), 'plat_id'))),
            'image1' => $lire('image1'), 'alt1' => $lire('alt1'),
            'image2' => $lire('image2'), 'alt2' => $lire('alt2'),
        ];

        $erreurs = [];
        if ($v['titre'] === '' || mb_strlen($v['titre']) > 100) {
            $erreurs['titre'] = 'Indiquez un titre (100 caractères maximum).';
        }
        if ($v['description'] === '') {
            $erreurs['description'] = 'Indiquez une description.';
        }
        if (!in_array((int) $v['theme_id'], array_column(Theme::tous(), 'theme_id'))) {
            $erreurs['theme_id'] = 'Choisissez un thème.';
        }
        if (!in_array((int) $v['regime_id'], array_column(Regime::tous(), 'regime_id'))) {
            $erreurs['regime_id'] = 'Choisissez un régime.';
        }
        if (filter_var($v['nombre_personne_minimum'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]) === false) {
            $erreurs['nombre_personne_minimum'] = 'Indiquez un nombre entier entre 1 et 500.';
        }
        if (filter_var($v['prix_par_personne'], FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.01, 'max_range' => 999.99]]) === false) {
            $erreurs['prix_par_personne'] = 'Indiquez un prix entre 0,01 et 999,99 €.';
        }
        if (filter_var($v['quantite_restante'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]) === false) {
            $erreurs['quantite_restante'] = 'Indiquez un stock entre 0 et 9999.';
        }
        if ($v['plats'] === []) {
            $erreurs['plats'] = 'Choisissez au moins un plat.';
        }

        // Photo 1 obligatoire, photo 2 facultative. Texte alternatif obligatoire pour chaque photo (RGAA).
        $photos = $this->photos();
        if (!in_array($v['image1'], $photos, true)) {
            $erreurs['image1'] = 'Choisissez la photo principale.';
        }
        if ($v['image2'] !== '' && !in_array($v['image2'], $photos, true)) {
            $erreurs['image2'] = 'Choisissez une photo de la liste.';
        }
        foreach ([1, 2] as $n) {
            if ($v["image$n"] !== '' && ($v["alt$n"] === '' || mb_strlen($v["alt$n"]) > 255)) {
                $erreurs["alt$n"] = 'Décrivez la photo pour les personnes malvoyantes (255 caractères maximum).';
            }
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficherFormulaire($id, $v, $erreurs);
            return;
        }

        $v['prix_par_personne'] = round((float) $v['prix_par_personne'], 2);
        $v['images'] = [['chemin' => $v['image1'], 'alt' => $v['alt1']]];
        if ($v['image2'] !== '') {
            $v['images'][] = ['chemin' => $v['image2'], 'alt' => $v['alt2']];
        }

        Menu::enregistrer($id, $v);

        flash('succes', $id === null ? "Le menu « {$v['titre']} » a été créé." : "Le menu « {$v['titre']} » a été modifié.");
        rediriger('/employe/menus');
    }

    // POST /employe/menu/supprimer
    public function supprimer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!csrf_valide() || !$id) {
            flash('erreur', 'Action impossible. Merci de réessayer.');
            rediriger('/employe/menus');
        }

        if (Menu::supprimer($id)) {
            flash('succes', 'Le menu a été supprimé.');
        } else {
            flash('erreur', 'Ce menu a déjà été commandé : il ne peut pas être supprimé (historique des clients). Mettez son stock à 0 pour arrêter de le vendre.');
        }
        rediriger('/employe/menus');
    }

    // Photos livrées avec le site. Heroku efface les fichiers envoyés à chaque redémarrage :
    // on choisit donc parmi ces photos (évolution possible : stockage externe type S3).
    private function photos(): array
    {
        $fichiers = glob(dirname(__DIR__, 2) . '/public/assets/img/menus/*.jpg');
        return array_map(fn(string $f): string => 'assets/img/menus/' . basename($f), $fichiers);
    }

    private function afficherFormulaire(?int $id, array $valeurs, array $erreurs): void
    {
        View::render('employe/menu-formulaire', [
            'titre'   => $id ? 'Modifier un menu' : 'Nouveau menu',
            'id'      => $id,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
            'themes'  => Theme::tous(),
            'regimes' => Regime::tous(),
            'plats'   => Plat::tous(),
            'photos'  => $this->photos(),
        ]);
    }
}