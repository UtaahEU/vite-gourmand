<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;
use App\Models\Commande;
use App\Models\Menu;
use App\Models\Statistique;

// US24 : suivi des commandes par l'employé (et l'administrateur, US29)
class GestionCommandeController
{
    // GET /employe/commandes?statut=terminee&client=martin (US23 : filtres facultatifs)
    public function liste(): void
    {
        exiger_connexion(ROLES_EMPLOYE);

        // Statut : accepté seulement s'il fait partie de la liste connue (sinon pas de filtre)
        $statut = $_GET['statut'] ?? '';
        $statut = is_string($statut) && in_array($statut, Commande::STATUTS, true) ? $statut : null;

        // Client : texte libre, espace retirés, 100 caractères au plus
        $client = is_string($_GET['client'] ?? null)  ? trim($_GET['client']) : '';
        $client = $client !== '' ? mb_substr($client, 0, 100) : null;

        View::render('employe/commandes', [
            'titre' => 'Commandes',
            'commandes' => Commande::pourEmploye($statut, $client),
            'statut' => $statut,
            'client' => $client,
        ]);
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

        $this->afficher($commande);
    }

    // Page de détail. Quand le formulaire US25 est renvoyé avec des erreurs, on réaffiche ce que l'employé a tapé.
    private function afficher(array $commande, array $erreurs = [], array $valeurs = []): void
    {
        View::render('employe/commande', [
            'titre' => 'Commande ' . $commande['numero_commande'],
            'commande' => $commande,
            'suivi' => Commande::suivi((int) $commande['commande_id']),
            'suivants' => Commande::ETAPES_SUIVANTES[$commande['statut']] ?? [],
            'modifiable' => in_array($commande['statut'], Commande::MODIFIABLES_EMPLOYE, true),
            'erreurs' => $erreurs,
            'valeurs' => $valeurs + [
                'mode_contact' => '',
                'motif' => '',
                'personnes' => $commande['nombre_personne'],
                'date' => $commande['date_prestation'],
                'heure' => substr($commande['heure_livraison'], 0, 5),
            ],
        ]);
    }

    // POST /employe/commande/modifier (US25 / RG7) : le bouton cliqué envoie action = modifier ou annuler
    public function modifierOuAnnuler():void
    {
        exiger_connexion(ROLES_EMPLOYE);
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $retour = '/employe/commande?id=' . (int) $id;

        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de réessayer.');
            rediriger($retour);
        }

        $commande = $id ? Commande::trouverPourEmploye($id) : null;
        if ($commande === null || !in_array($commande['statut'], Commande::MODIFIABLES_EMPLOYE, true)) {
            flash('erreur', 'Cette commande ne peut plus être modifiée ni annulée.');
        rediriger($commande ? $retour : '/employe/commandes');
        }

        $texte = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $action = $texte('action') === 'annuler' ? 'annuler' : 'modifier';
        $valeurs = [
            'mode_contact' => $texte('mode_contact'),
            'motif' => $texte('motif'),
            'personnes' => $texte('personnes'),
            'date' => $texte('date'),
            'heure' => $texte('heure'),
        ];

        // RG7 : le client doit avoir été contacté, et le motif est obligatoire
        $erreurs = [];
        if (!in_array($valeurs['mode_contact'], ['gsm', 'mail'], true)) {
            $erreurs['mode_contact'] = 'Indiquez comment le client a été contacté.';
        }
        if ($valeurs['motif'] === '' || mb_strlen($valeurs['motif']) > 500) {
            $erreurs['motif'] = 'Indiquez le motif (500 caractères au plus).';
        }

        $minimum = (int) Menu::trouver((int) $commande['menu_id'])['nombre_personne_minimum'];
        $personnes = filter_var($valeurs['personnes'], FILTER_VALIDATE_INT);
        if ($action === 'modifier') {
            if ($personnes === false || $personnes < $minimum) { // RG2
                $erreurs['personnes'] = "Ce menu se commande pour $minimum personnes minimum.";
            }
            $date = \DateTime::createFromFormat('!Y-m-d', $valeurs['date']);
            if (!$date || $date->format('Y-m-d') !== $valeurs['date'] || $date < new \DateTime('today')) {
                $erreurs['date'] = 'Choisissez une date à partir d\'aujourd\'hui.';
            }
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valeurs['heure'])) {
                $erreurs['heure'] = 'Indiquez une heure valide.';
            }
        }

        if ($erreurs !== []) {
            http_response_code(422);
            $this->afficher($commande, $erreurs, $valeurs);
            return;
        }

        if ($action === 'annuler') {
            $ok = Commande::annulerParEmploye($id, $commande['statut'], $valeurs['mode_contact'], $valeurs['motif']);
            $mongo = ['statut' => 'annulee'];
            $resume = 'Votre commande a été annulée.';
        } else {
            // Même prix par personne qu'au moment de la commande, même livraison (l'adresse ne change pas)
            $menu = [
                'prix_par_personne' => $commande['prix_menu'] / $commande['nombre_personne'],
                'nombre_personne_minimum' => $minimum,
            ];
            $prix = Commande::calculerPrix($menu, $personnes, (float) $commande['prix_livraison'] === 0.0, (float) $commande['distance_km']);
            $ok = Commande::modifierParEmploye($id, $commande['statut'], [
                'date' => $valeurs['date'],
                'heure' => $valeurs['heure'],
                'personnes' => $personnes,
                'prix' => $prix,
            ], $valeurs['mode_contact'], $valeurs['motif']);
            $mongo = ['nombre_personne' => $personnes, 'prix_total' => $prix['total']];
            $resume = "Votre commande a été modifiée : $personnes personnes, le "
                . date('d/m/Y', strtotime($valeurs['date'])) . " à {$valeurs['heure']}. "
                . 'Nouveau total : ' . prix($prix['total']) . ' €.';
        }

        if (!$ok) {
            flash('erreur', 'La commande a déjà été modifiée par quelqu\'un d\'autre. Vérifiez son statut.');
            rediriger($retour);
        }

        Statistique::mettreAJour($commande['numero_commande'], $mongo);
        $this->mailModification($commande, $resume, $valeurs['motif']);

        flash('succes', "Commande {$commande['numero_commande']} : " . ($action === 'annuler' ? 'annulée' : 'modifiée') . '.');
        rediriger($retour);
    }

    // US25 : le client reçoit par écrit ce qui a été convenu avec lui
    private function mailModification(array $commande, string $resume, string $motif): void
    {
        $texte = "Bonjour {$commande['prenom']},\n\n"
            . "Suite à notre échange au sujet de votre commande n° {$commande['numero_commande']} ({$commande['titre']}) :\n\n"
            . "$resume\n"
            . "Motif : $motif\n\n"
            . "Pour toute question, répondez simplement à cet e-mail.\n\n"
            . "Julie et José – Vite & Gourmand";

        Mailer::envoyer($commande['email'], "Votre commande {$commande['numero_commande']}", $texte);
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