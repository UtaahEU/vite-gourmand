<?php
/** @var array $menus  Liste des menus, fournie par MenuController */
?>
<?php if (!$menus): ?>
    <p class="col-12"> Aucun menu ne correspond à vos critères.</p>
<?php endif; ?>

<?php foreach ($menus as $menu): ?>
    <div class="col-md-6 col-lg-4">
        <article class="carte carte-menu">
            <?php if ($menu['image']): ?>
                <img class="carte-menu__photo" src="/<?= e($menu['image']) ?>" alt="<?= e($menu['image_alt']) ?>" width="400" height="200" loading="lazy">
            <?php endif; ?>
            <div class="carte-menu__corps">
                <p class="mb-2">
                    <span class="badge-kraft"><?= e($menu['theme']) ?></span>
                    <?php if ($menu['regime'] !== $menu['theme']): ?>
                        <span class="badge-kraft"><?= e($menu['regime']) ?></span>
                    <?php endif; ?>
                </p>
                <h2 class="h3"><?= e($menu['titre']) ?></h2>
                <p class="petit"><?= e($menu['description']) ?></p>
                <p class="prix">
                    Dès <?= number_format($menu['prix_par_personne'] * $menu['nombre_personne_minimum'], 0, ',', ' ') ?> €
                    <span class="text-nowrap">· <?= (int) $menu['nombre_personne_minimum'] ?> pers. min.</span>
                </p>
                <a class="btn btn-brique mt-auto align-self-start" href="/menu?id=<?= (int) $menu['menu_id'] ?>">
                    Voir le détail<span class="visually-hidden"> du menu <?= e($menu['titre']) ?></span>
                </a>
            </div>
        </article>
    </div>
<?php endforeach; ?>