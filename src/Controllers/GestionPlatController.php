<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Allergene;
use App\Models\Plat;

// US21 : gestion des plats et de leurs allergènes par l'employé (et l'administrateur, US29)
class GestionPlatController
{
    // GET /employe/plats
    public function liste(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        View::render('employe/plats', ['titre' => 'Gérer les plats', 'plats' => Plat::pourGestion()]);
    }

    // GET /employe/plat (nouveau) ou /employe/plat?id=3 (modification)
    public function formulaire(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $valeurs = ['titre_plat' => '', 'type' => '', 'allergenes' => []];

        if ($id !== null) {
            $plat = Plat::trouver($id);
            if ($plat === null) {
                http_response_code(404);
                View::render('erreurs/404', ['titre' => 'Page introuvable']);
                return;
            }
            $valeurs = ['titre_plat' => $plat['titre_plat'], 'type' => $plat['type'], 'allergenes' => Plat::allergenesIds($id)];
        }

        $this->afficherFormulaire($id, $valeurs, []);
    }

    // POST /employe/plat
    public function enregistrer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/employe/plats');
        }
        if ($id !== null && Plat::trouver($id) === null) {
            rediriger('/employe/plats');
        }

        $allergenes = is_array($_POST['allergenes'] ?? null) ? $_POST['allergenes'] : [];
        $v = [
            'titre_plat' => is_string($_POST['titre_plat'] ?? null) ? trim($_POST['titre_plat']) : '',
            'type'       => is_string($_POST['type'] ?? null) ? $_POST['type'] : '',
            // On ne garde que les allergènes qui existent en base. Aucun allergène coché = plat sans allergène.
            'allergenes' => array_values(array_intersect(array_map('intval', $allergenes), array_column(Allergene::tous(), 'allergene_id'))),
        ];

        $erreurs = [];
        if ($v['titre_plat'] === '' || mb_strlen($v['titre_plat']) > 100) {
            $erreurs['titre_plat'] = 'Indiquez le nom du plat (100 caractères maximum).';
        }
        if (!array_key_exists($v['type'], Plat::TYPES)) {
            $erreurs['type'] = 'Choisissez entrée, plat ou dessert.';
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficherFormulaire($id, $v, $erreurs);
            return;
        }

        Plat::enregistrer($id, $v['titre_plat'], $v['type'], $v['allergenes']);

        flash('succes', $id === null ? "Le plat « {$v['titre_plat']} » a été créé." : "Le plat « {$v['titre_plat']} » a été modifié.");
        rediriger('/employe/plats');
    }

    // POST /employe/plat/supprimer
    public function supprimer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!csrf_valide() || !$id) {
            flash('erreur', 'Action impossible. Merci de réessayer.');
            rediriger('/employe/plats');
        }

        $nbMenus = Plat::supprimer($id);
        if ($nbMenus === 0) {
            flash('succes', 'Le plat a été supprimé.');
        } else {
            flash('erreur', "Ce plat est utilisé dans $nbMenus menu(s) : retirez-le d'abord de ces menus.");
        }
        rediriger('/employe/plats');
    }

    private function afficherFormulaire(?int $id, array $valeurs, array $erreurs): void
    {
        View::render('employe/plat-formulaire', [
            'titre'      => $id ? 'Modifier un plat' : 'Nouveau plat',
            'id'         => $id,
            'valeurs'    => $valeurs,
            'erreurs'    => $erreurs,
            'allergenes' => Allergene::tous(),
        ]);
    }
}