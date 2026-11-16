<?php
/** @var string $jeton */
/** @var array $erreurs */
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <h1 class="mb-4">Nouveau mot de passe</h1>
                
                <form class="carte h-auto" method="post" action="/reinitialiser" novalidate>
                    <?= csrf_champ() ?>
                    <input type="hidden" name="jeton" value="<?= e($jeton) ?>">

                    <div class="mb-3">
                        <label class="form-label" for="password">Nouveau mot de passe</label>
                        <input <?= attributs_champ($erreurs, 'password', 'aide-mdp') ?> type="password" id="password" name="password"
                                autocomplete="new-password" required>
                        <?= erreur_champ($erreurs, 'password') ?>
                        <p id="aide-mdp" class="form-text mb-0">
                            Au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="confirmation">Confirmation</label>
                        <input <?= attributs_champ($erreurs, 'confirmation') ?> type="password" id="confirmation" name="confirmation"
                                autocomplete="new-password" required>
                        <?= erreur_champ($erreurs, 'confirmation') ?>
                    </div>

                    <button class="btn btn-brique w-100" type="submit">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</section>