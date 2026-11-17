<?php
/** @var array $commande */
/** @var array $suivi */
/** @var array|null $avis */
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
                                <?php if ($commande['statut'] === 'terminee'): ?>
                    <h2 class="h3 mt-4">Votre avis</h2>

                    <?php if ($avis): ?>
                        <div class="carte h-auto">
                            <p class="mb-1">
                                <span aria-hidden="true"><?= str_repeat('★', (int) $avis['note']) . str_repeat('☆', 5 - (int) $avis['note']) ?></span>
                                <span class="visually-hidden">Note : <?= (int) $avis['note'] ?> sur 5</span>
                            </p>
                            <p><?= nl2br(e($avis['commentaire'])) ?></p>
                            <p class="petit mb-0">
                                <?= ['en_attente' => 'En attente de validation par notre équipe', 'valide' => 'Publié sur notre site', 'refuse' => 'Non publié'][$avis['statut']] ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <form class="carte h-auto" method="post" action="/mon-compte/avis" novalidate>
                            <?= csrf_champ() ?>
                            <input type="hidden" name="commande" value="<?= $id ?>">

                            <fieldset class="mb-3">
                                <legend class="form-label fs-6">Votre note (sur 5)</legend>
                                <?php for ($n = 1; $n <= 5; $n++): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="note" id="note-<?= $n ?>" value="<?= $n ?>" required>
                                        <label class="form-check-label" for="note-<?= $n ?>"><?= $n ?> <span aria-hidden="true">★</span></label>
                                    </div>
                                <?php endfor; ?>
                            </fieldset>

                            <div class="mb-3">
                                <label class="form-label" for="commentaire">Votre commentaire</label>
                                <textarea class="form-control" id="commentaire" name="commentaire" rows="4"
                                          maxlength="1000" required aria-describedby="aide-commentaire"></textarea>
                                <p id="aide-commentaire" class="form-text mb-0">
                                    Entre 10 et 1000 caractères. Votre avis sera publié après validation par notre équipe.
                                </p>
                            </div>

                            <button class="btn btn-brique" type="submit">Envoyer mon avis</button>
                        </form>
                    <?php endif; ?>
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