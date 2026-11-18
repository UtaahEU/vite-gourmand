<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Core\Router;
use App\Controllers\PageController;
use App\Controllers\ContactController;
use App\Controllers\AuthController;
use App\Controllers\CommandeController;
use App\Controllers\AvisController;
use App\Controllers\ProfilController;
use App\Controllers\EmployeController;
use App\Controllers\GestionMenuController;
use App\Controllers\GestionPlatController;
use App\Controllers\GestionCommandeController;
use App\Controllers\GestionAvisController;
use App\Controllers\GestionHoraireController;
use App\Controllers\AdminController;

// Serveur PHP intégré : on laisse passer les fichiers réels (CSS, images, JS)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Heure frnaçaise pour PHP
date_default_timezone_set('Europe/Paris');

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
$_ENV += getenv();

// Session sécurisée : cookie inaccessible au JavaScript, protégé contre le CSRF de base
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/menus', [MenuController::class, 'index']);
$router->get('/mentions-legales', [PageController::class, 'mentionsLegales']);
$router->get('/cgv', [PageController::class, 'cgv']);
$router->get('/contact', [ContactController::class, 'formulaire']);
$router->post('/contact', [ContactController::class, 'envoyer']);
$router->get('/api/menus', [MenuController::class, 'api']);
$router->get('/menu', [MenuController::class, 'detail']);
$router->get('/inscription', [AuthController::class, 'inscription']);
$router->post('/inscription', [AuthController::class, 'inscrire']);
$router->get('/connexion', [AuthController::class, 'connexion']);
$router->post('/connexion', [AuthController::class, 'connecter']);
$router->post('/deconnexion', [AuthController::class, 'deconnecter']);
$router->get('/mot-de-passe-oublie', [AuthController::class, 'motDePasseOublie']);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'envoyerLien']);
$router->get('/reinitialiser', [AuthController::class, 'reinitialisation']);
$router->post('/reinitialiser', [AuthController::class, 'reinitialiser']);
$router->get('/commande', [CommandeController::class, 'formulaire']);
$router->post('/commande', [CommandeController::class, 'verifier']);
$router->get('/commande/recapitulatif', [CommandeController::class, 'recapitulatif']);
$router->post('/commande/confirmer', [CommandeController::class, 'confirmer']);
$router->get('/mon-compte/commandes', [CommandeController::class, 'mesCommandes']);
$router->get('/mon-compte/commande', [CommandeController::class, 'suivi']);
$router->get('/mon-compte/commande/modifier', [CommandeController::class, 'modifier']);
$router->post('/mon-compte/commande/annuler', [CommandeController::class, 'annuler']);
$router->post('/mon-compte/avis', [AvisController::class, 'deposer']);
$router->get('/mon-compte/profil', [ProfilController::class, 'afficher']);
$router->post('/mon-compte/profil', [ProfilController::class, 'enregistrer']);
$router->get('/employe', [EmployeController::class, 'tableauDeBord']);
$router->get('/employe/menus', [GestionMenuController::class, 'liste']);
$router->get('/employe/menu', [GestionMenuController::class, 'formulaire']);
$router->post('/employe/menu', [GestionMenuController::class, 'enregistrer']);
$router->post('/employe/menu/supprimer', [GestionMenuController::class, 'supprimer']);
$router->get('/employe/plats', [GestionPlatController::class, 'liste']);
$router->get('/employe/plat', [GestionPlatController::class, 'formulaire']);
$router->post('/employe/plat', [GestionPlatController::class, 'enregistrer']);
$router->post('/employe/plat/supprimer', [GestionPlatController::class, 'supprimer']);
$router->get('/employe/commandes', [GestionCommandeController::class, 'liste']);
$router->get('/employe/commande', [GestionCommandeController::class, 'detail']);
$router->post('/employe/commande/statut', [GestionCommandeController::class, 'changerStatut']);
$router->post('/employe/commande/modifier', [GestionCommandeController::class, 'modifierOuAnnuler']);
$router->get('/employe/avis', [GestionAvisController::class, 'liste']);
$router->post('/employe/avis', [GestionAvisController::class, 'moderer']);
$router->get('/employe/horaires', [GestionHoraireController::class, 'formulaire']);
$router->post('/employe/horaires', [GestionHoraireController::class, 'enregistrer']);
$router->get('/admin/employes', [AdminController::class, 'employes']);
$router->post('/admin/employes', [AdminController::class, 'creerEmploye']);
$router->post('/admin/employe/actif', [AdminController::class, 'changerActif']);
$router->get('/admin/statistiques', [AdminController::class, 'statistiques']);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);