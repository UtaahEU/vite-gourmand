<?php
/** @var array|null $parMenu  null = MongoDB indisponible */
/** @var array $filtres menu, du, au (null = pas de filtre) */
/** @var array $menus liste des menus pour le filtre */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Statistiques</li>
            </ol>
        </nav>

        <h1 class="mb-4">Statistiques des commandes</h1>

        <form method="get" action="/admin/statistiques" class="carte h-auto mb-4">
            <h2 class="h3">Filtrer</h2>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="menu">Menu</label>
                    <select class="form-select" id="menu" name="menu">
                        <option value="">Tous les menus</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?= (int) $menu['menu_id'] ?>" <?= $filtres['menu'] === (int) $menu['menu_id'] ? 'selected' : ''  ?>>
                                <?= e($menu['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="du">Du</label>
                    <input class="form-control" type="date" id="du" name="du" value="<?= e($filtres['du'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="au">Au</label>
                    <input class="form-control" type="date" id="au" name="au" value="<?= e($filtres['au'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-brique" type="submit">Filtrer</button>
                    <a class="btn btn-contour" href="/admin/statistiques">Tout afficher</a>
                </div>
            </div>
        </form>

        <?php if ($parMenu === null): ?>
            <div class="alert alert-danger" role="alert">
                Statistiques indisponibles : la base MongoDB ne répond pas. Réessayez plus tard.
            </div>
        <?php elseif ($parMenu === []): ?>
            <p>Aucune commande pour ces critères.</p>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="carte h-auto">
                        <h2 class="h3">Nombre de commandes par menu</h2>
                        <canvas id="graphique-commandes" role="img"
                                aria-label="Graphique en barres du nombre de commandes par menu. Les valeurs sont dans le tableau."></canvas>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="carte h-auto table-responsive">
                        <table class="table align-middle mb-0">
                            <caption class="petit">Commandes et chiffre d'affaires par menu (hors commandes annulées)</caption>
                            <thead>
                                <tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">Chiffre d'affaires</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parMenu as $ligne): ?>
                                    <tr>
                                        <td><?= e($ligne['menu']) ?></td>
                                        <td class="text-end"><?= $ligne['commandes'] ?></td>
                                        <td class="text-end"><?= prix($ligne['chiffre_affaires']) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row">Total</th>
                                    <td class="text-end fw-bold"><?= array_sum(array_column($parMenu, 'commandes')) ?></td>
                                    <td class="text-end fw-bold"><?= prix(array_sum(array_column($parMenu, 'chiffre_affaires'))) ?> €</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Données passées au JavaScript : json_encode + JSON_HEX_TAG empêche de fermer la balise <script> -->
            <script type="application/json" id="donnees-commandes"><?= json_encode($parMenu, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" defer></script>
            <script src="/assets/js/statistiques.js" defer></script>
        <?php endif; ?>
    </div>
</section>