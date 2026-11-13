<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Menu;
use App\Models\Regime;
use App\Models\Theme;

class MenuController
{
    // Page complète /menus
    public function index(): void
    {
        $filtres = $this->lireFiltres();

        View:: render('menus/index', [
            'titre' => 'Nos menus',
            'menus' => Menu::rechercher($filtres),
            'themes' => Theme::tous(),
            'regimes' => Regime::tous(),
            'filtres' => $filtres,
        ]);
    }

    // /api/menus, appelée par fetch : renvoie le nombre de menus et le HTML des cartes
    public function api(): void
    {
        $menus = Menu::rechercher($this->lireFiltres());

        ob_start();
        require dirname(__DIR__, 2) . '/views/menus/_cartes.php';
        $html = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['nombre' => count($menus), 'html' => $html]);
    }

    // Lit les filtres dans l'URL : un nombre positif, sinon null (filtre ignoré)
    private function lireFiltres(): array
    {
        $lire = function (string $cle, bool $decimal = false): int|float|null {
            $valeur = isset($_GET[$cle]) && is_string($_GET[$cle]) ? trim($_GET[$cle]) : '';
            $valeur = str_replace(',', '.', $valeur);

            if ($valeur === '' || !is_numeric($valeur) || $valeur < 0) {
                return null;
            }
            return $decimal ? (float) $valeur : (int) $valeur;
        };

        return [
            'prix_max' => $lire('prix_max', true),
            'fourchette_min' => $lire('fourchette_min', true),
            'fourchette_max' => $lire('fourchette_max', true),
            'theme' => $lire('theme'),
            'regime' => $lire('regime'),
            'personnes' => $lire('personnes'),
        ];
    }
}
