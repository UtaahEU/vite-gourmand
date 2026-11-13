<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$nb = App\Core\Database::get()->query('SELECT COUNT(*) FROM menu')->fetchColumn();
echo 'Connexion OK: ' . $nb . ' menus en base';