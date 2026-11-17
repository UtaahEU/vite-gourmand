<?php
/** @var int|null $id */
/** @var array $valeurs */
/** @var array $erreurs */
/** @var array $themes */
/** @var array $regimes */
/** @var array $plats */
/** @var array $photos */
$types = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
// Les <select> utilisent la classe form-select de Bootstrap au lieu de form-control
$attributsSelect = fn(string $champ): string => str_replace('form-control', 'form-select', attributs_champ($erreurs, $champ));
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item"><a href="/employe/menus">Menus</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $id ? 'Modifier' : 'Nouveau' ?></li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <h1 class="mb-4"><?= $id ? 'Modifier le menu' : 'Nouveau menu' ?></h1>

                <form class="carte h-auto" method="post" action="/employe/menu" novalidate>
                    <?= csrf_champ() ?>
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>

                    <h2 class="h3">Présentation</h2>
                    <div class="mb-3">
                        <label class="form-label" for="titre">Titre</label>
                        <input <?= attributs_champ($erreurs, 'titre') ?> type="text" id="titre" name="titre"
                               maxlength="100" required value="<?= e($valeurs['titre']) ?>">
                        <?= erreur_champ($erreurs, 'titre') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea <?= attributs_champ($erreurs, 'description') ?> id="description" name="description"
                                  rows="4" required><?= e($valeurs['description']) ?></textarea>
                        <?= erreur_champ($erreurs, 'description') ?>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="theme_id">Thème</label>
                            <select <?= $attributsSelect('theme_id') ?> id="theme_id" name="theme_id" required>
                                <option value="">Choisir…</option>
                                <?php foreach ($themes as $t): ?>
                                    <option value="<?= (int) $t['theme_id'] ?>" <?= (int) $valeurs['theme_id'] === (int) $t['theme_id'] ? 'selected' : '' ?>>
                                        <?= e($t['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= erreur_champ($erreurs, 'theme_id') ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="regime_id">Régime</label>
                            <select <?= $attributsSelect('regime_id') ?> id="regime_id" name="regime_id" required>
                                <option value="">Choisir…</option>
                                <?php foreach ($regimes as $r): ?>
                                    <option value="<?= (int) $r['regime_id'] ?>" <?= (int) $valeurs['regime_id'] === (int) $r['regime_id'] ? 'selected' : '' ?>>
                                        <?= e($r['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= erreur_champ($erreurs, 'regime_id') ?>
                        </div>
                    </div>

                    <h2 class="h3">Prix et quantités</h2>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="prix_par_personne">Prix par personne (€)</label>
                            <input <?= attributs_champ($erreurs, 'prix_par_personne') ?> type="text" id="prix_par_personne"
                                   name="prix_par_personne" inputmode="decimal" required value="<?= e((string) $valeurs['prix_par_personne']) ?>">
                            <?= erreur_champ($erreurs, 'prix_par_personne') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="nombre_personne_minimum">Personnes minimum</label>
                            <input <?= attributs_champ($erreurs, 'nombre_personne_minimum') ?> type="number" id="nombre_personne_minimum"
                                   name="nombre_personne_minimum" min="1" required value="<?= e((string) $valeurs['nombre_personne_minimum']) ?>">
                            <?= erreur_champ($erreurs, 'nombre_personne_minimum') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="quantite_restante">Stock (commandes possibles)</label>
                            <input <?= attributs_champ($erreurs, 'quantite_restante') ?> type="number" id="quantite_restante"
                                   name="quantite_restante" min="0" required value="<?= e((string) $valeurs['quantite_restante']) ?>">
                            <?= erreur_champ($erreurs, 'quantite_restante') ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="conditions_menu">Conditions (facultatif)</label>
                        <textarea <?= attributs_champ($erreurs, 'conditions_menu', 'aide-conditions') ?> id="conditions_menu"
                                  name="conditions_menu" rows="3"><?= e((string) $valeurs['conditions_menu']) ?></textarea>
                        <p id="aide-conditions" class="form-text">Par exemple : délai de commande, conservation, matériel prêté.</p>
                    </div>

                    <fieldset class="mb-4" <?= isset($erreurs['plats']) ? 'aria-describedby="erreur-plats"' : '' ?>>
                        <legend class="h3">Plats du menu</legend>
                        <?php if (isset($erreurs['plats'])): ?>
                            <p id="erreur-plats" class="invalid-feedback d-block"><?= e($erreurs['plats']) ?></p>
                        <?php endif; ?>
                        <div class="row g-3">
                            <?php foreach ($types as $type => $libelle): ?>
                                <div class="col-md-4">
                                    <p class="fw-bold mb-2"><?= $libelle ?></p>
                                    <?php foreach ($plats as $p): ?>
                                        <?php if ($p['type'] === $type): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="plats[]"
                                                       id="plat-<?= (int) $p['plat_id'] ?>" value="<?= (int) $p['plat_id'] ?>"
                                                       <?= in_array((int) $p['plat_id'], $valeurs['plats'], true) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="plat-<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <h2 class="h3">Photos</h2>
                    <?php foreach ([1, 2] as $n): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label" for="image<?= $n ?>"><?= $n === 1 ? 'Photo principale' : 'Deuxième photo (facultatif)' ?></label>
                                <select <?= $attributsSelect("image$n") ?> id="image<?= $n ?>" name="image<?= $n ?>">
                                    <option value=""><?= $n === 1 ? 'Choisir…' : 'Aucune' ?></option>
                                    <?php foreach ($photos as $photo): ?>
                                        <option value="<?= e($photo) ?>" <?= $valeurs["image$n"] === $photo ? 'selected' : '' ?>><?= e(basename($photo)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?= erreur_champ($erreurs, "image$n") ?>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="alt<?= $n ?>">Description de la photo</label>
                                <input <?= attributs_champ($erreurs, "alt$n") ?> type="text" id="alt<?= $n ?>" name="alt<?= $n ?>"
                                       maxlength="255" value="<?= e($valeurs["alt$n"]) ?>">
                                <?= erreur_champ($erreurs, "alt$n") ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-brique" type="submit"><?= $id ? 'Enregistrer les modifications' : 'Créer le menu' ?></button>
                        <a class="btn btn-contour" href="/employe/menus">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>