<?php
/** @var array $erreurs */
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <h1 class="mb-4">Mot de passe oublié</h1>
                <p class="mb-4">
                    Indiquez l'adresse e-mail de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.
                </p>
                <form class="carte h-auto" method="post" action="/mot-de-passe-oublie" novalidate>
                    <?= csrf_champ() ?>
                    <div class="mb-4">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input <?= attributs_champ($erreurs, 'email') ?> type="email" id="email" name="email"
                                autocomplete="email" required>
                        <?= erreur_champ($erreurs, 'email') ?>
                    </div>
                    <button class="btn btn-brique w-100" type="submit">Recevoir le lien</button>
                </form>

                <p class="mt-3"><a class="lien-brique" href="/connexion">Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</section>