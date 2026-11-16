<?php
/** @var array $erreurs */
/** @var array $saisie */
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-2">Créer un compte</h1>
                <p class="mb-4">Déjà client ? <a class="lien-brique" href="/connexion">Se connecter</a></p>

                <form class="carte h-auto" method="post" action="/inscription" novalidate>
                    <?= csrf_champ() ?>
                    <p class="petit">Tous les champs sont obligatoires.</p>

                    <fieldset>
                        <legend class="h3">Vos informations</legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="prenom">Prénom</label>
                                <input <?= attributs_champ($erreurs, 'prenom') ?> type="text" id="prenom" name="prenom"
                                       maxlength="50" autocomplete="given-name" required value="<?= e($saisie['prenom'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'prenom') ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="nom">Nom</label>
                                <input <?= attributs_champ($erreurs, 'nom') ?> type="text" id="nom" name="nom"
                                       maxlength="50" autocomplete="family-name" required value="<?= e($saisie['nom'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'nom') ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="telephone">Téléphone (GSM)</label>
                                <input <?= attributs_champ($erreurs, 'telephone') ?> type="tel" id="telephone" name="telephone"
                                       autocomplete="tel" placeholder="06 12 34 56 78" required value="<?= e($saisie['telephone'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'telephone') ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Adresse e-mail</label>
                                <input <?= attributs_champ($erreurs, 'email') ?> type="email" id="email" name="email"
                                       autocomplete="email" required value="<?= e($saisie['email'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'email') ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-4">
                        <legend class="h3">Adresse de livraison</legend>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="adresse_postale">Numéro et rue</label>
                                <input <?= attributs_champ($erreurs, 'adresse_postale') ?> type="text" id="adresse_postale" name="adresse_postale"
                                       maxlength="255" autocomplete="street-address" required value="<?= e($saisie['adresse_postale'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'adresse_postale') ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="code_postal">Code postal</label>
                                <input <?= attributs_champ($erreurs, 'code_postal') ?> type="text" id="code_postal" name="code_postal"
                                       inputmode="numeric" maxlength="5" autocomplete="postal-code" required value="<?= e($saisie['code_postal'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'code_postal') ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="ville">Ville</label>
                                <input <?= attributs_champ($erreurs, 'ville') ?> type="text" id="ville" name="ville"
                                       maxlength="50" autocomplete="address-level2" required value="<?= e($saisie['ville'] ?? '') ?>">
                                <?= erreur_champ($erreurs, 'ville') ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mt-4 mb-4">
                        <legend class="h3">Mot de passe</legend>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password">Mot de passe</label>
                                <input <?= attributs_champ($erreurs, 'password', 'aide-mdp') ?> type="password" id="password" name="password"
                                       autocomplete="new-password" required>
                                <?= erreur_champ($erreurs, 'password') ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="confirmation">Confirmer le mot de passe</label>
                                <input <?= attributs_champ($erreurs, 'confirmation') ?> type="password" id="confirmation" name="confirmation"
                                       autocomplete="new-password" required>
                                <?= erreur_champ($erreurs, 'confirmation') ?>
                            </div>
                            <div class="col-12">
                                <p id="aide-mdp" class="form-text mb-0">
                                    Au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial (! ? @ # …).
                                </p>
                            </div>
                        </div>
                    </fieldset>

                    <button class="btn btn-brique" type="submit">Créer mon compte</button>
                </form>
            </div>
        </div>
    </div>
</section>