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
}