<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;
use App\Models\Commande;
use App\Models\Statistique;

// US24 : suivi des commandes par l'employé (et l'administrateur, US29)
class GestionCommandeController
{
    // GET /employe/commandes
    public function liste():void
    {
        exiger_connexion(ROLES_EMPLOYE);
        View::render('employe/commandes', ['titre' => 'Commandes', 'commandes' => Commande::pourEmploye()]);
    }

    // GET /employe/commandes?id=3
    public function detail(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $commande = $id ? Commande::trouverPourEmploye($id) : null;

        if ($commande === null) {
            http_response_code(404);
            View::render('erreurs/404', ['titre' => 'Page introuvable']);
            return;
        }

        View::render('employe/commande', [
            'titre' => 'Commande ' . $commande['numero_commande'],
            'commande' => $commande,
            'suivi' => Commande::suivi($id),
            'suivants' => Commande::ETAPES_SUIVANTES[$commande['statut']] ?? [],
        ]);
    }

    // POST /employe/commande/statut
    public function changerStatut(): void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $retour = '/employe/commande?id=' . (int) $id;

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger($retour);
        }

        $commande = $id ? Commande::trouverPourEmploye($id) : null;
        $nouveau = is_string($_POST['nouveau'] ?? null) ? $_POST['nouveau'] : '';

        // Le nouveau statu doit être un des suivants autorisés pour le statut actuel
        if ($commande === null || !in_array($nouveau, Commande::ETAPES_SUIVANTES[$commande['statut']] ?? [], true)) {
            flash('erreur', 'Ce changement de statut n\'est pas possible.');
            rediriger($commande ? $retour : '/employe/commandes');
        }

        if (!Commande::changerStatut($id, $commande['statut'], $nouveau)) {
            flash('erreur', 'La commande a déjà été modifiée par quelqu\'un d\'autre. Vérifiez son statut.');
            rediriger($retour);
        }

        Statistique::mettreAJour($commande['numero_commande'], ['statut' => $nouveau]);

        // Mails automatiqque (RG9 et RG10)
        if ($nouveau === 'attente_retour_materiel') {
            $this->mailRetourMateriel($commande);
        } elseif ($nouveau === 'terminee') {
            $this->mailInvitationAvis($commande);
        }

        flash('succes', "Commande {$commande['numero_commande']} : " . statut_commande($nouveau) . '.');
        rediriger($retour);
    }

    // RG9 : du méteriel a été prêté, le client a 10 jours ouvrés pour le rendre
    private function mailRetourMateriel(array $commande): void
    {
        $texte = "Bonjour {$commande['prenom']},\n\n"
            . "Votre commande n° {$commande['numero_commande']} a été livrée avec du matériel prêté par Vite & Gourmand.\n\n"
            . "Merci de nous le restituer dans un délai de 10 jours ouvrés. Passé ce délai, "
            . "des frais de 600 € vous seront facturés, conformément à nos conditions générales de vente.\n\n"
            . "Pour organiser le retour, contactez-nous par téléphone ou en répondant à cet e-mail.\n\n"
            . "Julie et José – Vite & Gourmand";

        Mailer::envoyer($commande['email'], "Restitution du matériel – commande {$commande['numero_commande']}", $texte);
    }

    // RG10 / US18 : la commande est terminée, on invite le client à donner son avis
    private function mailInvitationAvis(array $commande): void
    {
        $lien = rtrim($_ENV['APP_URL'], '/') . '/mon-compte/commande?id=' . $commande['commande_id'];

        $texte = "Bonjour {$commande['prenom']},\n\n"
            . "Votre commande n° {$commande['numero_commande']} ({$commande['titre']}) est terminée. "
            . "Merci de nous avoir fait confiance ! \n\n"
            . "Votre avis nous aide à nous améliorer. Vous pouvez nous laisser une note et un commentaire ici : \n "
            . "$lien\n\n"
            . "Julie et José – Vite & Gourmand";

        Mailer::envoyer($commande['email'], "Votre avis sur la commande {$commande['numero_commande']}", $texte);
    }
}