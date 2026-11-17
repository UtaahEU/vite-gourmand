<?php

namespace App\Models;

use App\Core\Database;

class Utilisateur
{
    public static function emailExiste(string $email): bool
    {
        $requete = Database::get()->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $requete->execute(['email' => $email]);

        return (bool) $requete->fetchColumn();
    }

    // Crée un compte avec le rôle "utilisateur" (role_id = 1) et renvoie son id

    public static function creer(array $donnees): int
    {
        $requete = Database::get()->prepare(
            'INSERT INTO utilisateur (nom, prenom, email, password, telephone, adresse_postale, code_postal, ville, role_id)
            VALUES (:nom, :prenom, :email, :password, :telephone, :adresse_postale, :code_postal, :ville, 1)'
        );
        $requete->execute([
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'password' => $donnees['password'],
            'telephone' => $donnees['telephone'],
            'adresse_postale' => $donnees['adresse_postale'],
            'code_postal' => $donnees['code_postal'],
            'ville' => $donnees['ville']
        ]);

        return (int) Database::get()->lastInsertId();
    }

    // Compte correspondant à un e-mail, avec le libellé de son rôle, ou null
    public static function parEmail(string $email): ?array
    {
        $requete = Database::get()->prepare(
            'SELECT u.utilisateur_id, u.prenom, u.nom, u.email, u.password, u.actif, r.libelle AS role
            FROM utilisateur u
            JOIN role r ON r.role_id = u.role_id
            WHERE u.email = :email'
        );
        $requete->execute(['email' => $email]);

        return $requete->fetch() ?: null;
    }

    // Enregistre l'empreinte du jeton de réinitialisation, valable 1 heure
    public static function enregistrerJeton(int $id, string $empreinte): void
    {
        $requete = Database::get()->prepare(
            'UPDATE utilisateur
            SET reset_token_hash = :empreinte, reset_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE utilisateur_id = :id'
        );
        $requete->execute(['empreinte' => $empreinte, 'id' => $id]);
    }

    // Compte actif dont le jeton correspond et n'a pas expiré, ou null
    public static function parJeton(string $empreinte): ?array
    {
        $requete = Database::get()->prepare(
            'SELECT utilisateur_id, prenom FROM utilisateur
            WHERE reset_token_hash = :empreinte AND reset_expiration > NOW() AND actif =1'
        );
        $requete->execute(['empreinte' => $empreinte]);

        return $requete->fetch() ?: null;
    }

    // Nouveau mot de passe + supression du jeton (le lien est unique)
    public static function changerMotDePasse(int $id, string $hash): void
    {
        $requete = Database::get()->prepare(
            'UPDATE utilisateur
            SET password = :hash, reset_token_hash = NULL, reset_expiration = NULL
            WHERE utilisateur_id = :id'
        );
        $requete->execute(['hash' => $hash, 'id' => $id]);
    }

    // Infos du client pour préremplir la commande,
    public static function trouver(int $id): ?array
    {
        $requete = Database::get()->prepare(
            'SELECT utilisateur_id, prenom, nom, email, telephone, adresse_postale, code_postal, ville
            FROM utilisateur
            WHERE utilisateur_id = :id'
        );
        $requete->execute(['id' => $id]);

        return $requete->fetch() ?: null;
    }

    // US19 : le client modifie ses informations (l'e-mail et le rôle ne changent pas ici)
    public static function modifierProfil(int $id, array $profil): void
    {
        Database::get()->prepare(
            'UPDATE utilisateur
             SET prenom = :prenom, nom = :nom, telephone = :telephone,
                 adresse_postale = :adresse_postale, code_postal = :code_postal, ville = :ville
             WHERE utilisateur_id = :id'
        )->execute($profil + ['id' => $id]);
    }

    // US27 : crée un compte employé. Le rôle est écrit en dur (2 = employe) :
    // aucun formulaire ne peut créer un administrateur.
    public static function creerEmploye(array $donnees): int
    {
        $pdo = Database::get();
        $pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, email, password, role_id)
             VALUES (:nom, :prenom, :email, :password, 2)'
        )->execute([
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'password' => $donnees['password'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    // US28 : les comptes employés, actifs d'abord
    public static function employes(): array
    {
        return Database::get()->query(
            'SELECT utilisateur_id, prenom, nom, email, actif, date_creation
             FROM utilisateur
             WHERE role_id = 2
             ORDER BY actif DESC, nom, prenom'
        )->fetchAll();
    }

    // US28 : active ou désactive un compte. "AND role_id = 2" : seuls les employés sont concernés,
    // jamais un client ni l'administrateur. false si le compte n'existe pas ou n'est pas un employé.
    public static function changerActif(int $id, bool $actif): bool
    {
        $requete = Database::get()->prepare(
            'UPDATE utilisateur SET actif = :actif WHERE utilisateur_id = :id AND role_id = 2'
        );
        $requete->execute(['actif' => (int) $actif, 'id' => $id]);

        return $requete->rowCount() === 1;
    }

    // US28 : le compte existe-t-il encore et est-il actif ? (vérifié à chaque page protégée)
    public static function estActif(int $id): bool
    {
        $requete = Database::get()->prepare('SELECT actif FROM utilisateur WHERE utilisateur_id = :id');
        $requete->execute(['id' => $id]);

        return (bool) $requete->fetchColumn();
    }
}