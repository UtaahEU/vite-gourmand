<?php
/** @var array $menu */
/** @var array $images */
/** @var array $composition */
/** @var bool $connecte */
$libelles = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];
$principale = $images[0] ?? null;
$vignettes = array_slice($images, 1);
$id = (int) $menu['menu_id'];
$minimum = (int) $menu['nombre_personne_minimum'];
$stock = (int) $menu['quantite_restante'];
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '›';">
                <li class="breadcrumb-item"><a href="/">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/menus">Menus</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Galerie -->
            <div class="col-lg-7">
                <?php if ($principale): ?>
                    <img class="detail-photo" src="/<?= e($principale['chemin']) ?>"
                         alt="<?= e($principale['texte_alternatif']) ?>" width="800" height="500">
                <?php endif; ?>

                <?php if ($vignettes): ?>
                    <div class="row g-3 mt-1">
                        <?php foreach ($vignettes as $image): ?>
                            <div class="col-4">
                                <img class="detail-vignette" src="/<?= e($image['chemin']) ?>"
                                     alt="<?= e($image['texte_alternatif']) ?>" width="260" height="120" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Infos -->
            <div class="col-lg-5">
                <p class="mb-2">
                    <span class="badge-kraft badge-papier"><?= e($menu['theme']) ?></span>
                    <?php if ($menu['regime'] !== $menu['theme']): ?>
                        <span class="badge-kraft badge-papier"><?= e($menu['regime']) ?></span>
                    <?php endif; ?>
                </p>

                <h1><?= e($menu['titre']) ?></h1>
                <p><?= e($menu['description']) ?></p>

                <p class="prix-grand"><?= prix($menu['prix_par_personne']) ?> € / personne</p>
                <p class="petit mb-1">
                    <?= $minimum ?> personnes minimum ·
                    <span class="text-nowrap">soit dès <?= prix($menu['prix_par_personne'] * $minimum) ?> €</span>
                </p>

                <?php if ($stock > 0): ?>
                    <p class="petit texte-brique">
                        Plus que <?= $stock ?> commande<?= $stock > 1 ? 's' : '' ?> possible<?= $stock > 1 ? 's' : '' ?>
                    </p>
                <?php endif; ?>

                <?php if ($menu['conditions_menu']): ?>
                    <div class="encadre-conditions my-3">
                        <h2 class="h3 texte-brique"><span aria-hidden="true">⚠️</span> Conditions du menu</h2>
                        <p class="mb-0"><?= nl2br(e($menu['conditions_menu'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($stock <= 0): ?>
                    <p class="fw-bold">Ce menu n'est plus disponible pour le moment.</p>
                <?php elseif ($connecte): ?>
                    <a class="btn btn-brique w-100" href="/commande?menu=<?= $id ?>">Commander ce menu</a>
                <?php else: ?>
                    <a class="btn btn-brique w-100" href="/connexion?retour=<?= urlencode('/menu?id=' . $id) ?>">
                        Commander ce menu
                    </a>
                    <p class="petit allergenes mt-2">Connexion requise pour commander</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section pt-0" aria-labelledby="titre-composition">
    <div class="container">
        <h2 id="titre-composition" class="mb-4">Composition du menu</h2>
        <div class="row g-4">
            <?php foreach ($composition as $type => $plats): ?>
                <?php if (!$plats) { continue; } ?>
                <div class="col-md-4">
                    <div class="carte">
                        <h3 class="texte-brique"><?= $libelles[$type] . (count($plats) > 1 ? 's' : '') ?></h3>
                        <?php foreach ($plats as $plat): ?>
                            <p class="mb-1"><?= e($plat['titre_plat']) ?></p>
                            <p class="petit allergenes">Allergènes : <?= e($plat['allergenes'] ?? 'aucun') ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>