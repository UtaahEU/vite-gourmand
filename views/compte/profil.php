<?php
/** @var array $profil */
/** @var array $erreurs */
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4">Mon profil</h1>

                <form class="carte h-auto" method="post" action="/mon-compte/profil" novalidate>
                    <?= csrf_champ() ?>

                    <p class="mb-4">
                        Adresse e-mail : <strong><?= e($profil['email']) ?></strong><br>
                        <span class="petit">Elle sert à vous connecter et ne peut pas être modifiée ici.</span>
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="prenom">Prénom</label>
                            <input <?= attributs_champ($erreurs, 'prenom') ?> type="text" id="prenom" name="prenom"
                                    maxlength="50" autocomplete="given-name" required value="<?= e($profil['prenom'] ?? '') ?>">
                            <?= erreur_champ($erreurs, 'prenom') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="nom">Nom</label>
                            <input <?= attributs_champ($erreurs, 'nom') ?> type="text" id="nom" name="nom"
                                        maxlength="50" autocomplete="family-name" required value="<?= e($profil['nom'] ?? '') ?>">
                            <?= erreur_champ($erreurs, 'nom') ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="telephone">Téléphone (GSM)</label>
                        <input <?= attributs_champ($erreurs, 'telephone') ?> type="tel" id="telephone" name="telephone"
                               autocomplete="tel" required value="<?= e($profil['telephone'] ?? '') ?>">
                        <?= erreur_champ($erreurs, 'telephone') ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="adresse_postale">Numéro et rue</label>
                        <input <?= attributs_champ($erreurs, 'adresse_postale') ?> type="text" id="adresse_postale" name="adresse_postale"
                               maxlength="255" autocomplete="street-address" required value="<?= e($profil['adresse_postale'] ?? '') ?>">
                        <?= erreur_champ($erreurs, 'adresse_postale') ?>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="code_postal">Code postal</label>
                            <input <?= attributs_champ($erreurs, 'code_postal') ?> type="text" id="code_postal" name="code_postal"
                                   inputmode="numeric" maxlength="5" autocomplete="postal-code" required value="<?= e($profil['code_postal'] ?? '') ?>">
                            <?= erreur_champ($erreurs, 'code_postal') ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="ville">Ville</label>
                            <input <?= attributs_champ($erreurs, 'ville') ?> type="text" id="ville" name="ville"
                                   maxlength="50" autocomplete="address-level2" required value="<?= e($profil['ville'] ?? '') ?>">
                            <?= erreur_champ($erreurs, 'ville') ?>
                        </div>
                    </div>

                    <button class="btn btn-brique w-100" type="submit">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</section>