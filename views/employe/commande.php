<?php
/** @var array $commande */
/** @var array $suivi */
/** @var array $suivants */
$id = (int) $commande['commande_id'];
// Texte des boujtons selon le statut proposé
$bouton = [
    'accepte' => 'Accepter la commande',
    'en_preparation' => 'Passer en préparation',
    'en_livraison' => 'Partir en livraison',
    'livre' => 'Marquer comme livrée',
    'attente_retour_materiel' => 'Du matériel a été prêté : attendre son retour',
    'terminee' => $commande['statut'] === 'attente_retour_materiel' ? 'Matériel rendu : terminer la commande' : 'Terminer la commande (pas de matériel prêté)',
];
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item"><a href="/employe/commandes">Commandes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Commande <?= e($commande['numero_commande']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-7">
                <h1 class="mb-3">Commande <?= e($commande['numero_commande']) ?></h1>

                <div class="carte h-auto mb-4">
                    <h2 class="h3"><?= e($commande['titre']) ?></h2>
                    <p>
                        Statut : <strong><?= e(statut_commande($commande['statut'])) ?></strong><br>
                        Pour <?= (int) $commande['nombre_personne'] ?> personnes,
                        le <?= date('d-m-Y', strtotime($commande['date_prestation'])) ?>
                        à <?= e(substr($commande['heure_livraison'], 0, 5)) ?><br>
                        Livraison : <?= e($commande['adresse_livraison']) ?>,
                        <?=  e($commande['code_postal_livraison']) ?> <?=  e($commande['ville_livraison']) ?>
                    </p>
                    <p class="mb-0"><strong>Total : <?= prix($commande['prix_total']) ?> €</strong></p>
                </div>

                <div class="carte h-auto mb-4">
                    <h2 class="h3">Client</h2>
                    <p class="mb-0">
                        <?= e($commande['prenom'] . ' ' . $commande['nom']) ?><br>
                        <a class="lien-brique" href="mailto:<?= e($commande['email']) ?>"><?= e($commande['email']) ?></a><br>
                        <?php if ($commande['telephone']): ?>
                            <a class="lien-brique" href="tel:<?= e($commande['telephone']) ?>"><?= e($commande['telephone']) ?></a>
                            <?php endif; ?>
                    </p>
                </div>

                <?php if ($suivants !== []): ?>
                    <h2 class="h3">Changer le statut</h2>
                    <form method="post" action="/employe/commande/statut" class="d-flex flex-wrap align-items-center gap-2">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <?php foreach ($suivants as $statut): ?>
                            <button class="btn <?= $statut === 'attente_retour_materiel' ? 'btn-contour' : 'btn-brique' ?>"
                                    type="submit" name="nouveau" value="<?= $statut ?>">
                                <?= e($bouton[$statut]) ?>
                            </button>
                        <?php endforeach; ?>
                    </form>
                    <?php if (in_array('attente_retour_materiel', $suivants, true)): ?>
                        <p class="petit mt-2">« Matériel prêté » envoie au client un e-mail : retour sous 10 jours ouvrés, sinon 600 € de frais.</p>
                    <?php endif; ?>
                    <?php if (in_array('terminee', $suivants, true)): ?>
                        <p class="petit mt-2">« Terminer » envoie au client une invitation à laisser un avis.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p> Cette commande est <?= e(mb_strtolower(statut_commande($commande['statut']))) ?> : son statut ne peut plus changer.</p>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <h2 class="h3 mt-lg-5">Suivi</h2>
                <ol class="suivi">
                    <?php foreach ($suivi as $etape): ?>
                        <li>
                            <strong><?= e(statut_commande($etape['statut'])) ?></strong><br>
                            <time class="petit" datetime="<?= date('c', strtotime($etape['date_modification'])) ?>">
                                le <?= date('d-m-Y à H:i', strtotime($etape['date_modification'])) ?>
                            </time>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</section>