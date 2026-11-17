<?php
/** @var array $menus */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Menus</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="mb-0">Gérer les menus</h1>
            <a class="btn btn-brique" href="/employe/menu">Nouveau menu</a>
        </div>

        <div class="carte h-auto table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Liste des menus</caption>
                <thead>
                    <tr>
                        <th scope="col">Titre</th>
                        <th scope="col">Thème</th>
                        <th scope="col" class="text-end">Prix / pers.</th>
                        <th scope="col" class="text-end">Minimum</th>
                        <th scope="col" class="text-end">Stock</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $m): ?>
                        <?php $id = (int) $m['menu_id']; ?>
                        <tr>
                            <td><?= e($m['titre']) ?></td>
                            <td><?= e($m['theme']) ?></td>
                            <td class="text-end text-nowrap"><?= prix($m['prix_par_personne']) ?> €</td>
                            <td class="text-end"><?= (int) $m['nombre_personne_minimum'] ?></td>
                            <td class="text-end">
                                <?= (int) $m['quantite_restante'] ?>
                                <?php if ((int) $m['quantite_restante'] === 0): ?>
                                    <span class="petit">(épuisé)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                    <a class="btn btn-contour btn-sm" href="/employe/menu?id=<?= $id ?>">
                                        Modifier<span class="visually-hidden"> le menu <?= e($m['titre']) ?></span>
                                    </a>
                                    <form method="post" action="/employe/menu/supprimer"
                                          onsubmit="return confirm('Supprimer définitivement ce menu ?');">
                                        <?= csrf_champ() ?>
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button class="btn btn-brique btn-sm" type="submit">
                                            Supprimer<span class="visually-hidden"> le menu <?= e($m['titre']) ?></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>