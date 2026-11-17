<?php
/** @var array $avis */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Avis</li>
            </ol>
        </nav>

        <h1 class="mb-2">Avis à valider</h1>
        <p class="mb-4">Un avis validé est publié sur la page d'accueil. Un avis refusé n'est jamais affiché.</p>

        <?php if ($avis === []): ?>
            <p class="carte h-auto">Aucun avis en attente. Tout est à jour !</p>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($avis as $unAvis): ?>
                <div class="col-md-6">
                    <article class="carte h-100 d-flex flex-column">
                        <p class="etoiles mb-2">
                            <span class="visually-hidden">Note : <?= (int) $unAvis['note'] ?> sur 5</span>
                            <span aria-hidden="true"><?= str_repeat('★', (int) $unAvis['note']) . str_repeat('☆', 5 - (int) $unAvis['note']) ?></span>
                        </p>
                        <blockquote class="flex-grow-1">
                            <p>« <?= e($unAvis['commentaire']) ?> »</p>
                        </blockquote>
                        <p class="petit">
                            <?= e($unAvis['prenom'] . ' ' . $unAvis['nom']) ?>, le <?= date('d/m/Y', strtotime($unAvis['date_creation'])) ?><br>
                            <?= e($unAvis['titre']) ?>, commande <?= e($unAvis['numero_commande']) ?>
                        </p>
                        <form method="post" action="/employe/avis" class="d-flex gap-2">
                            <?= csrf_champ() ?>
                            <input type="hidden" name="id" value="<?= (int) $unAvis['avis_id'] ?>">
                            <button class="btn btn-brique" type="submit" name="decision" value="valide">
                                Valider<span class="visually-hidden"> l'avis de <?= e($unAvis['prenom']) ?></span>
                            </button>
                            <button class="btn btn-contour" type="submit" name="decision" value="refuse">
                                Refuser<span class="visually-hidden"> l'avis de <?= e($unAvis['prenom']) ?></span>
                            </button>
                        </form>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>