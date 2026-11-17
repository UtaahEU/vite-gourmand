<?php
/** @var array $commande */
/** @var array $suivi */
/** @var array $suivants */
/** @var bool $modifiable */
/** @var array $erreurs */
/** @var array $valeurs */
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
                    <?php if ($commande['motif_modification']): ?>
                        <h3 class="h5 mt-3">Historique des contacts client</h3>
                        <p class="petit mb-0" style="white-space: pre-line;"><?= e($commande['motif_modification']) ?></p>
                    <?php endif; ?>
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
                <?php if ($modifiable): ?>
                    <form method="post" action="/employe/commande/modifier" class="carte h-auto mt-4" novalidate>
                        <h2 class="h3">Modifier ou annuler</h2>
                        <p class="petit">Uniquement après avoir contacté le client (RG7). Il recevra un e-mail récapitulatif.</p>
                        <?= csrf_champ() ?>
                        <input type="hidden" name="id" value="<?= $id ?>">

                        <fieldset class="mb-3" <?= isset($erreurs['mode_contact']) ? 'aria-describedby="erreur-mode_contact"' : '' ?>>
                            <legend class="form-label fs-6">Client contacté par <span aria-hidden="true">*</span></legend>
                            <?php foreach (['gsm' => 'Téléphone', 'mail' => 'E-mail'] as $mode => $libelle): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode_contact" id="mode_<?= $mode ?>"
                                           value="<?= $mode ?>" required <?= $valeurs['mode_contact'] === $mode ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="mode_<?= $mode ?>"><?= $libelle ?></label>
                                </div>
                            <?php endforeach; ?>
                            <?php if (isset($erreurs['mode_contact'])): ?>
                                <div id="erreur-mode_contact" class="invalid-feedback d-block"><?= e($erreurs['mode_contact']) ?></div>
                            <?php endif; ?>
                        </fieldset>

                        <div class="mb-3">
                            <label class="form-label" for="motif">Motif <span aria-hidden="true">*</span></label>
                            <textarea name="motif" id="motif" rows="3" maxlength="500" required <?= attributs_champ($erreurs, 'motif') ?>><?= e($valeurs['motif']) ?></textarea>
                            <?= erreur_champ($erreurs, 'motif') ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <label class="form-label" for="personnes">Personnes</label>
                                <input type="number" name="personnes" id="personnes" min="1" value="<?= e((string) $valeurs['personnes']) ?>" <?= attributs_champ($erreurs, 'personnes') ?>>
                                <?= erreur_champ($erreurs, 'personnes') ?>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="date">Date</label>
                                <input type="date" name="date" id="date" value="<?= e($valeurs['date']) ?>" <?= attributs_champ($erreurs, 'date') ?>>
                                <?= erreur_champ($erreurs, 'date') ?>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label" for="heure">Heure</label>
                                <input type="time" name="heure" id="heure" value="<?= e($valeurs['heure']) ?>" <?= attributs_champ($erreurs, 'heure') ?>>
                                <?= erreur_champ($erreurs, 'heure') ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-brique" type="submit" name="action" value="modifier">Enregistrer la modification</button>
                            <button class="btn btn-contour" type="submit" name="action" value="annuler">Annuler la commande</button>
                        </div>
                        <p class="petit mt-2 mb-0">« Annuler » ignore la date, l'heure et le nombre de personnes, et remet le menu en stock.</p>
                    </form>
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