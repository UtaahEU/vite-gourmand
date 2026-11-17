<?php
/** @var array $plats */
use App\Models\Plat;
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Plats</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="mb-0">Gérer les plats</h1>
            <a class="btn btn-brique" href="/employe/plat">Nouveau plat</a>
        </div>

        <div class="carte h-auto table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Liste des plats et de leurs allergènes</caption>
                <thead>
                    <tr>
                        <th scope="col">Plat</th>
                        <th scope="col">Type</th>
                        <th scope="col">Allergènes</th>
                        <th scope="col" class="text-end">Menus</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plats as $p): ?>
                        <?php $id = (int) $p['plat_id']; ?>
                        <tr>
                            <td><?= e($p['titre_plat']) ?></td>
                            <td><?= Plat::TYPES[$p['type']] ?></td>
                            <td><?= $p['allergenes'] ? e($p['allergenes']) : '<span class="petit">Aucun</span>' ?></td>
                            <td class="text-end"><?= (int) $p['nb_menus'] ?></td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                    <a class="btn btn-contour btn-sm" href="/employe/plat?id=<?= $id ?>">
                                        Modifier<span class="visually-hidden"> le plat <?= e($p['titre_plat']) ?></span>
                                    </a>
                                    <form method="post" action="/employe/plat/supprimer"
                                          onsubmit="return confirm('Supprimer définitivement ce plat ?');">
                                        <?= csrf_champ() ?>
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button class="btn btn-brique btn-sm" type="submit">
                                            Supprimer<span class="visually-hidden"> le plat <?= e($p['titre_plat']) ?></span>
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