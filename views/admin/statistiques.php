<?php
/** @var array|null $parMenu  null = MongoDB indisponible */
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

        <?php if ($parMenu === null): ?>
            <div class="alert alert-danger" role="alert">
                Statistiques indisponibles : la base MongoDB ne répond pas. Réessayez plus tard.
            </div>
        <?php elseif ($parMenu === []): ?>
            <p>Aucune commande pour le moment.</p>
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
                            <caption class="petit">Commandes par menu (hors commandes annulées)</caption>
                            <thead>
                                <tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parMenu as $ligne): ?>
                                    <tr>
                                        <td><?= e($ligne['menu']) ?></td>
                                        <td class="text-end"><?= $ligne['commandes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
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