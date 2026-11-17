<?php

namespace App\Models;

use App\Core\Database;

class Commande
{
    // US24: statuts suivants autorisés pour chaque statut (machine à états).
    // "livre" mène soit au retour de matériel (matériel prêté), soit directement à "terminee"
    public const ETAPES_SUIVANTES = [
        'en_attente' => ['accepte'],
        'accepte' => ['en_preparation'],
        'en_preparation' => ['en_livraison'],
        'en_livraison' => ['livre'],
        'livre' => ['attente_retour_materiel', 'terminee'],
        'attente_retour_materiel' => ['terminee'],
    ];

    // Tous les status possible (même ordre que l'ENUM de la base), pour le filtre US23
    public const STATUTS = [
        'en_attente', 'accepte', 'en_preparation', 'en_livraison', 'livre',
        'attente_retour_materiel', 'terminee', 'annulee',
    ];

    // US25 / RG7 : statuts où l'employé peut encore modifier ou annuler (avant le départ en livraison)
    public const MODIFIABLES_EMPLOYE = ['en_attente', 'accepte', 'en_preparation'];


    // RG3 : -10 % à partir du minimum + 5 personnes / RG4 : Livraison gratuite à Bordeaux, sinon 5 € + 0,59 €/km
    public static function calculerPrix(array $menu, int $personnes, bool $bordeaux, float $km): array
    {
        $prixMenu = round($menu['prix_par_personne'] * $personnes, 2);
        $reduction = $personnes >= $menu['nombre_personne_minimum'] + 5 ? round($prixMenu * 0.10, 2) : 0.0;
        $livraison = $bordeaux ? 0.0 : round(5 + 0.59 * $km, 2);

        return [
            'menu' => $prixMenu,
            'reduction' => $reduction,
            'livraison' => $livraison,
            'total' => round($prixMenu - $reduction +$livraison, 2),
        ];
    }

    // RG5 : vérifie et baisse le stock, créer la commande et son premier suivi.
    // Transaction : tout est enregistré, ou rien. Renvoie le numéro, ou null si le menu est épuisé.
    public static function creer(int $utilisateurId, array $c) : ?string
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            // FOR UPDATE verrouille la ligne : deux clients ne peuvent pas prendre le dernier menu en même temps
            $requete = $pdo->prepare('SELECT quantite_restante FROM menu WHERE menu_id = :id FOR UPDATE');
            $requete->execute(['id' => $c['menu_id']]);
            if ((int) $requete->fetchColumn() < 1) {
                $pdo->rollBack();
                return null;
            }

            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = :id')
                -> execute(['id' => $c['menu_id']]);

            $numero = 'VG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $pdo->prepare(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                    adresse_livraison, code_postal_livraison, ville_livraison, distance_km, nombre_personne,
                    prix_menu, reduction, prix_livraison, prix_total)
                VALUES (:numero, :utilisateur, :menu, :date, :heure, :adresse, :cp, :ville, :km, :personnes,
                    :prix_menu, :reduction, :livraison, :total)'
            )->execute([
                'numero' => $numero,
                'utilisateur' => $utilisateurId,
                'menu' => $c['menu_id'],
                'date' => $c['date'],
                'heure' => $c['heure'],
                'adresse' => $c['adresse'],
                'cp' => $c['code_postal'],
                'ville' => $c['ville'],
                'km' => $c['km'],
                'personnes' => $c['personnes'],
                'prix_menu' => $c['prix']['menu'],
                'reduction' => $c['prix']['reduction'],
                'livraison' => $c['prix']['livraison'],
                'total' => $c['prix']['total'],
            ]);

            // Premier suivi horodaté (RG8)
            $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, 'en_attente')")
                ->execute(['id' => (int) $pdo->lastInsertId()]);
            
            $pdo->commit();
            return $numero;
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    // Commandes d'un client, la plus récente en premier (US15)
    public static function duClient(int $utilisateurId): array
    {
        $requete = Database::get()->prepare(
            'SELECT c.commande_id, c.numero_commande, c.date_prestation, c.prix_total, c.statut, m.titre
             FROM commande c
             JOIN menu m ON m.menu_id = c.menu_id
             WHERE c.utilisateur_id = :id
             ORDER BY c.date_prestation DESC'
        );
        $requete->execute(['id' => $utilisateurId]);

        return $requete->fetchAll();
    }

    // Une commande, seulement si elle appartient à ce client (sinon null)
    public static function trouverPourClient(int $id, int $utilisateurId): ?array
    {
        $requete = Database::get()->prepare(
            'SELECT c.*, m.titre
             FROM commande c
             JOIN menu m ON m.menu_id = c.menu_id
             WHERE c.commande_id = :id AND c.utilisateur_id = :utilisateur'
        );
        $requete->execute(['id' => $id, 'utilisateur' => $utilisateurId]);

        return $requete->fetch() ?: null;
    }

    // Historique horodaté des statuts (RG8, US17)
    public static function suivi(int $id): array
    {
        $requete = Database::get()->prepare(
            'SELECT statut, date_modification FROM commande_suivi
             WHERE commande_id = :id
             ORDER BY date_modification, suivi_id'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetchAll();
    }

    // RG6 : modification par le client (tout sauf le menu), seulement si la commande est encore en attente
    public static function modifier(int $id, array $c): void
    {
        Database::get()->prepare(
            "UPDATE commande
             SET date_prestation = :date, heure_livraison = :heure, adresse_livraison = :adresse,
                 code_postal_livraison = :cp, ville_livraison = :ville, distance_km = :km,
                 nombre_personne = :personnes, prix_menu = :prix_menu, reduction = :reduction,
                 prix_livraison = :livraison, prix_total = :total
             WHERE commande_id = :id AND statut = 'en_attente'"
        )->execute([
            'date' => $c['date'],
            'heure' => $c['heure'],
            'adresse' => $c['adresse'],
            'cp' => $c['code_postal'],
            'ville' => $c['ville'],
            'km' => $c['km'],
            'personnes' => $c['personnes'],
            'prix_menu' => $c['prix']['menu'],
            'reduction' => $c['prix']['reduction'],
            'livraison' => $c['prix']['livraison'],
            'total' => $c['prix']['total'],
            'id' => $id,
        ]);
    }

    // RG6 : annulation par le client. Le menu est remis en stock et le suivi est horodaté.
    public static function annuler(int $id): void
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare("UPDATE commande SET statut = 'annulee' WHERE commande_id = :id AND statut = 'en_attente'");
            $requete->execute(['id' => $id]);

            // Aucune ligne modifiée = commande déjà acceptée entre-temps : on ne touche a rien
            if ($requete->rowCount() === 0) {
                $pdo->rollBack();
                return;
            }

            $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante + 1
                 WHERE menu_id = (SELECT menu_id FROM commande WHERE commande_id = :id)'
            )->execute (['id' => $id]);

            $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, 'annulee')")
                ->execute(['id' => $id]);

            $pdo->commit();
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    // US24: toutes les commandes pour l'espace employé, les plus récentes en premier
    public static function pourEmploye(?string $statut = null, ?string $client = null): array
    {
        $sql = 'SELECT c.commande_id, c.numero_commande, c.date_prestation, c.prix_total, c.statut,
                       m.titre, u.prenom, u.nom
                FROM commande c
                JOIN menu m ON m.menu_id = c.menu_id
                JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
                WHERE 1 = 1';
        $parametres = [];
        if ($statut !== null) {
            $sql .= ' AND c.statut = :statut';
            $parametres['statut'] = $statut;
        }
        if ($client !== null) {
            // Un seul paramètre nommé (EMULATE_PREPARES = false interdit de réutiliser :client).
            // addcslashes : un % ou un _ tapé par l'employé est cherché tel quel, pas comme joker
            $sql .= " AND CONCAT(u.prenom, ' ', u.nom, ' ', u.email) LIKE :client";
            $parametres['client'] = '%' . addcslashes($client, '%_\\') . '%';
        }

        $sql .= ' ORDER BY c.date_commande DESC';

        $requete = Database::get()->prepare($sql);
        $requete->execute($parametres);

        return $requete->fetchAll();
    }

    // Une commande avec son menu et les coordonnées du client (pour l'employé), ou null
    public static function trouverPourEmploye(int $id): ?array
    {
        $requete = Database::get()->prepare(
            'SELECT c.*, m.titre, u.prenom, u.nom, u.email, u.telephone
             FROM commande c
             JOIN menu m ON m.menu_id = c.menu_id
             JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
             WHERE c.commande_id = :id'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
    }

    // US24 : passe la commande de $ancien à $nouveau et horodate le changement (RG8).
    // WHERE statut = :ancien : si un autre employé a changé le statut entre-temps, rien n'est modifié (false).
    public static function changerStatut(int $id, string $ancien, string $nouveau): bool
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            $sql = 'UPDATE commande SET statut = :nouveau';
            if ($nouveau === 'attente_retour_materiel') {
                $sql .= ', pret_materiel = 1'; // du matériel a été prêté
            }
            if ($ancien === 'attente_retour_materiel') {
                $sql .= ', restitution_materiel = 1'; // du matériel a été rendu
            }
            $sql .= ' WHERE commande_id = :id AND statut = :ancien';

            $requete = $pdo->prepare($sql);
            $requete->execute(['nouveau' => $nouveau, 'id' => $id, 'ancien' => $ancien]);

            if ($requete->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, :statut)')
                ->execute(['id' => $id, 'statut' => $nouveau]);

            $pdo->commit();
            return true;
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    // US25 / RG7 : ligne ajoutée à l'historique des contacts, ex. "17/11/2026 16:40 - téléphone - annulation : ..."
    private static function ligneContact(string $mode, string $action, string $motif): string
    {
        return date('d/m/Y H:i') . ' – ' . ($mode === 'gsm' ? 'téléphone' : 'e-mail') . " – $action : $motif";
    }

    // US25 / RG7 : annulation par l'employé après contact du client. Menu remis en stock, suivi horodaté.
    // CONCAT_WS ajoute la ligne à la suite des précédentes (on garde l'historique des contacts).
    public static function annulerParEmploye(int $id, string $ancien, string $mode, string $motif): bool
    {
        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare(
                "UPDATE commande
                 SET statut = 'annulee', mode_contact = :mode, motif_modification = CONCAT_WS('\n', motif_modification, :ligne)
                 WHERE commande_id = :id AND statut = :ancien"
            );
            $requete->execute([
                'mode' => $mode,
                'ligne' => self::ligneContact($mode, 'annulation', $motif),
                'id' => $id,
                'ancien' => $ancien,
            ]);

            if ($requete->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare(
                'UPDATE menu SET quantite_restante = quantite_restante + 1
                 WHERE menu_id = (SELECT menu_id FROM commande WHERE commande_id = :id)'
            )->execute(['id' => $id]);

            $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (:id, 'annulee')")
                ->execute(['id' => $id]);
            
            $pdo->commit();
            return true;
        } catch (\Throwable $erreur) {
            $pdo->rollBack();
            throw $erreur;
        }
    }

    // US25 / RG7 : modification (date, heure, personnes) par l'employé après contact. Le prix est recalculé par le contrôleur.
    public static function modifierParEmploye(int $id, string $ancien, array $c, string $mode, string $motif): bool
    {
        $requete = Database::get()->prepare(
            "UPDATE commande
             SET date_prestation = :date, heure_livraison = :heure, nombre_personne = :personnes,
                 prix_menu = :prix_menu, reduction = :reduction, prix_livraison = :livraison, prix_total = :total,
                 mode_contact = :mode, motif_modification = CONCAT_WS('\n', motif_modification, :ligne)
             WHERE commande_id = :id AND statut = :ancien"
        );
        $requete->execute([
            'date' => $c['date'],
            'heure' => $c['heure'],
            'personnes' => $c['personnes'],
            'prix_menu' => $c['prix']['menu'],
            'reduction' => $c['prix']['reduction'],
            'livraison' => $c['prix']['livraison'],
            'total' => $c['prix']['total'],
            'mode' => $mode,
            'ligne' => self::ligneContact($mode, 'modification', $motif),
            'id' => $id,
            'ancien' => $ancien,
        ]);

        return $requete->rowCount() === 1;
    }
}