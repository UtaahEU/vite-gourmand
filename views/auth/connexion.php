<?php
/** @var string|null $erreur */
/** @var string $email */
/** @var string $retour */
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <h1 class="mb-4">Connexion</h1>

                <form class="carte h-auto" method="post" action="/connexion" novalidate>
                    <?=  csrf_champ() ?>
                    <input type="hidden" name="retour" value="<?= e($retour) ?>">

                    <?php if ($erreur): ?>
                        <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input class="form-control" type="email" id="email" name="email"
                                autocomplete="email" required value="<?= e($email) ?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input class="form-control" type="password" id="password" name="password"
                                autocomplete="current-password" required>
                    </div>

                    <p class="petit mb-4">
                        <a class="lien-brique" href="/mot-de-passe-oublie">Mot de passe oublié ?</a>
                    </p>

                    <button class="btn btn-brique w-100" type="submit">Se connecter</button>
                </form>

                <p class="mt-3">
                    Pas encore de compte ? <a class="lien-brique" href="/inscription"> Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</section>