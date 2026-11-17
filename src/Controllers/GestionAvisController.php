<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Avis;

// US26 : l'employé valide ou refuse les avis des clients avant leur publication (RG11)
class GestionAvisController
{
    // GET /employe/avis
    public function liste(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        View::render('employe/avis', ['titre' => 'Avis à valider', 'avis' => Avis::enAttente()]);
    }

    // POST /employe/avis : le bouton cliqué envoie decision = valide ou refuse
    public function moderer(): void
    {
        exiger_connexion(ROLES_EMPLOYE);

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger('/employe/avis');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $decision = $_POST['decision'] ?? '';

        // Liste fermée : seules ces 2 valeurs sont acceptées
        if (!$id || !in_array($decision, ['valide', 'refuse'], true)) {
            flash('erreur', 'Action impossible.');
            rediriger('/employe/avis');
        }

        if (Avis::moderer($id, $decision)) {
            flash('succes', $decision === 'valide' ? 'Avis validé : il est maintenant visible sur l\'accueil.' : 'Avis refusé.');
        } else {
            flash('erreur', 'Cet avis a déjà été traité.');
        }
        rediriger('/employe/avis');
    }
}