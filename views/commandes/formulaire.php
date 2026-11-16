<?php
/** @var array $menu */
/** @var array $client */
/** @var array $valeurs */
/** @var array $erreurs */
$id = (int) $menu['menu_id'];
$minimum = (int) $menu['nombre_personne_minimum'];
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/menus">Menus</a></li>
                <li class="breadcrumb-item"><a href="/menu?id=<?= $id ?>"><?= e($menu['titre']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Commander</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-2">Commander : <?= e($menu['titre']) ?></h1>
                <p class="mb-4">
                    <?= prix($menu['prix_par_personne']) ?> € / personne · <?= $minimum ?> personnes minimum ·
                    <strong>-10 % dès <?= $minimum + 5 ?> personnes</strong>
                </p>

                <form class="carte h-auto" method="post" action="/commande" novalidate>
                    <?= csrf_champ() ?>
                    <input type="hidden" name="menu" value="<?= $id ?>">

                    <h2 class="h3">Vos coordonnées</h2>
                    <p class="mb-4">
                        <?= e($client['prenom'] . ' ' . $client['nom']) ?><br>
                        <?= e($client['email']) ?><br>
                        <?= e($client['telephone'] ?? '') ?>
                    </p>

                    <h2 class="h3">Prestation</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="personnes">Nombre de personnes</label>
                            <input <?= attributs_champ($erreurs, 'personnes') ?> type="number" id="personnes" name="personnes"
                                min="<?= $minimum ?>" value="<?= e((string) $valeurs['personnes']) ?>" required>
                            <?= erreur_champ($erreurs, 'personnes') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="date">Date</label>
                            <input <?= attributs_champ($erreurs, 'date') ?> type="date" id="date" name="date"
                                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($valeurs['date']) ?>" required>
                            <?= erreur_champ($erreurs, 'date') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="heure">Heure de livraison</label>
                            <input <?= attributs_champ($erreurs, 'heure') ?> type="time" id="heure" name="heure"
                                value="<?= e($valeurs['heure']) ?>" required>
                            <?= erreur_champ($erreurs, 'heure') ?>
                        </div>
                    </div>

                    <h2 class="h3"> Adresse de livraison</h2>
                    <p class="form-text"> Livraison offerte à Bordeaux, sinon 5 € + 0,59 € par kilomètre.</p>
                    <div class="mb-3">
                        <label class="form-label" for="adresse">Adresse</label>
                        <input <?= attributs_champ($erreurs, 'adresse') ?> type="text" id="adresse" name="adresse"
                            autocomplete="street-address" value="<?= e($valeurs['adresse']) ?>" required>
                        <?= erreur_champ($erreurs, 'adresse') ?>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="code_postal">Code Postal</label>
                            <input <?= attributs_champ($erreurs, 'code_postal') ?> type="text" id="code_postal" name="code_postal"
                                inputmode="numeric" maxlenght="5" autocomplete="postal-code"
                                value="<?= e($valeurs['code_postal']) ?>" required>
                            <?= erreur_champ($erreurs, 'code_postal') ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="ville">Ville</label>
                            <input <?= attributs_champ($erreurs, 'ville') ?> type="text" id="ville" name="ville"
                                autocomplete="address-level2" value="<?= e($valeurs['ville']) ?>" required>
                            <?= erreur_champ($erreurs, 'ville') ?>
                        </div>
                    </div>

                    <button class="btn btn-brique w-100" type="submit">Voir le récapitulatif et le prix</button>
                </form>
            </div>
        </div>
    </div>
</section>