<?php
/** @var array $commande */
/** @var array $suivi */
$id = (int) $commande['commande_id'];
$enAttente = $commande['statut'] === 'en_attente';
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '›';">
                <li class="breadcrumb-item"><a href="/mon-compte/commandes">Mes commandes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($commande['numero_commande']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-7">
                <h1 class="mb-3">Commande <?= e($commande['numero_commande']) ?></h1>

                <div class="carte h-auto">
                    <h2 class="h3"><?= e($commande['titre']) ?></h2>
                    <p>
                        Statut : <strong><?= e(statut_commande($commande['statut'])) ?></strong><br>
                        Pour <?= (int) $commande['nombre_personne'] ?> personnes,
                        le <?= date('d/m/Y', strtotime($commande['date_prestation'])) ?>
                        à <?= e(substr($commande['heure_livraison'], 0, 5)) ?><br>
                        Livraison : <?= e($commande['adresse_livraison']) ?>,
                        <?= e($commande['code_postal_livraison']) ?> <?= e($commande['ville_livraison']) ?>
                    </p>
                    <p class="mb-0">
                        Menu : <?= prix($commande['prix_menu']) ?> €
                        <?php if ($commande['reduction'] > 0): ?>
                            · Réduction : -<?= prix($commande['reduction']) ?> €
                        <?php endif; ?>
                        · Livraison : <?= prix($commande['prix_livraison']) ?> €<br>
                        <strong>Total : <?= prix($commande['prix_total']) ?> €</strong>
                    </p>
                </div>

                <?php if ($enAttente): ?>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a class="btn btn-contour" href="/mon-compte/commande/modifier?id=<?= $id ?>">Modifier</a>
                        <form method="post" action="/mon-compte/commande/annuler"
                              onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?');">
                            <?= csrf_champ() ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button class="btn btn-brique" type="submit">Annuler la commande</button>
                        </form>
                    </div>
                    <p class="petit mt-2">Modification et annulation possibles tant que notre équipe n'a pas accepté la commande.</p>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <h2 class="h3 mt-lg-5">Suivi</h2>
                <?php if ($enAttente): ?>
                    <p>Votre commande attend la validation de notre équipe. Le suivi s'affichera dès qu'elle sera acceptée.</p>
                <?php else: ?>
                    <ol class="suivi">
                        <?php foreach ($suivi as $etape): ?>
                            <li>
                                <strong><?= e(statut_commande($etape['statut'])) ?></strong><br>
                                <time class="petit" datetime="<?= date('c', strtotime($etape['date_modification'])) ?>">
                                    le <?= date('d/m/Y à H:i', strtotime($etape['date_modification'])) ?>
                                </time>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>