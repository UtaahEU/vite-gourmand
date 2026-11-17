<?php
/** @var array $commandes */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Commandes</li>
            </ol>
        </nav>

        <h1 class="mb-4">Commandes</h1>

        <div class="carte h-auto table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Liste des commandes</caption>
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Client</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Prestation</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Statut</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td class="text-nowrap"><?= e($c['numero_commande']) ?></td>
                            <td><?= e($c['prenom'] . ' ' . $c['nom']) ?></td>
                            <td><?= e($c['titre']) ?></td>
                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($c['date_prestation'])) ?></td>
                            <td class="text-end text-nowrap"><?= prix($c['prix_total']) ?> €</td>
                            <td><?= e(statut_commande($c['statut'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-contour btn-sm" href="/employe/commande?id=<?= (int) $c['commande_id'] ?>">
                                    Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>