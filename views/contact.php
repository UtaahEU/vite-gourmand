<?php
/** @var array $erreurs */
/** @var array $saisie */
// Attributs RGAA d'un champ en erreur : bordure rouge + lien vers le message d'erreur
$etat = fn(string $champ): string => isset($erreurs[$champ])
    ? 'is-invalid" aria-invalid="true" aria-describedby="erreur-' . $champ
    : '';
?>
<section class="section pt-4">
    <div class="container">
        <h1 class="mb-4">Nous contacter</h1>

        <div class="row g-4">
            <div class="col 7">
                <form class="carte" method="post" action="/contact" novalidate>
                    <?= csrf_champ() ?>
                    <p class="petit">Tous les champs sont obligatoires.</p>

                    <?php if (isset($erreurs['envoi'])): ?>
                        <div class="alert alert-danger" role="alert"><?= e($erreurs['envoi']) ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="titre">Objet de votre demande</label>
                        <input class="form-control <?= $etat('titre') ?>" type="text" id="titre" name="titre" maxlength="100"
                        required value="<?= e($saisie['titre'] ?? '') ?>">
                        <?php if (isset($erreurs['titre'])): ?>
                            <div id="erreur-titre" class="invalid-feedback"><?= e($erreurs['titre']) ?> </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Votre adresse e-mail</label>
                        <input class="form-control <?= $etat('email') ?>" type="email" id="email" name="email"
                        autocomplete="email" required value="<?= e($saisie['email'] ?? '') ?>">
                        <?php if (isset($erreurs['email'])): ?>
                            <div id="erreur-email" class="invalid-feedback"><?= e($erreurs['email']) ?> </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="description">Votre message</label>
                        <textarea class="form-control <?= $etat('description') ?>" id="description" name="description"
                        rows="6" maxlength="2000" required><?= e($saisie['description'] ?? '') ?></textarea>
                        <?php if (isset($erreurs['description'])): ?>
                            <div id="erreur-description" class="invalid-feedback"><?= e($erreurs['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button class="btn btn-brique" type="submit">Envoyer le message </button>
                </form>
            </div>

            <div class="col-lg-5">
                <div class="carte">
                    <h2 class="h3">Nos coordonnées</h2>
                    <address>
                        Vite &amp; Gourmand<br>
                        12 rue Sainte-Catherine<br>
                        33000 Bordeaux<br>
                        <a class="lien-brique" href="tel:+33556000000">05 56 00 00 00</a><br>
                        <a class="lien-brique" href="mailto:contact@vite-gourmand.fr">contact@vite-gourmand.fr</a>
                    </address>
                    <p class="mb-0">Julie et José vous répondent sous 48h ouvrées.</p>
                </div>
            </div>
        </div>
    </div>
</section>