<?php

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Commande;

class AvisController
{
    // POST /mon-compte/avis : avis sur une commande terminée (US18, RG11)
    public function deposer(): void
    {
        exiger_connexion();
        $commandeId = filter_input(INPUT_POST, 'commande', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $retour = '/mon-compte/commande?id=' . (int) $commandeId;

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger($retour);
        }

        // La commande doit être à ce client, terminée, et sans avis
        $commande = $commandeId ? Commande::trouverPourClient($commandeId, utilisateur()['id']) : null;
        if ($commande === null || $commande['statut'] !== 'terminee' || Avis::pourCommande($commandeId) !== null) {
            flash('erreur', 'Vous ne pouvez pas laisser d\'avis sur cette commande.');
            rediriger('/mon-compte/commandes');
        }

        $note = filter_input(INPUT_POST, 'note', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $commentaire = is_string($_POST['commentaire'] ?? null) ? trim($_POST['commentaire']) : '';

        if (!$note || mb_strlen($commentaire) < 10 || mb_strlen($commentaire) > 1000) {
            flash('erreur', 'Choisissez une note de 1 à 5 et écrivez un commentaire de 10 à 1000 caractères.');
            rediriger($retour);
        }

        Avis::creer(utilisateur()['id'], $commandeId, $note, $commentaire);

        flash('succes', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
        rediriger($retour);
    }
}