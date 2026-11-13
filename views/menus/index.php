<?php
/** @var array $menus */
/** @var array $themes */
/** @var array $regimes */
/** @var array $filtres */
$nb = count($menus);
?>
<section class="section pt-4">
    <div class="container">
        <h1 class="mb-4">Nos menus</h1>

        <div class="row g-4">
            <!-- Filtres -->
            <div class="col-md-4 col-lg-3">
                <button class="btn btn-contour w-100 d-md-none mb-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#form-filtres" aria-expanded="false" aria-controls="form-filtres">
                    Filtres ▾
                </button>

                <form id="form-filtres" class="carte h-auto collapse d-md-block" method="get" action="/menus" aria-labelledby="titre-filtres">
                        <h2 id="titre-filtres" class="h3">Filtres</h2>

                        <div class="mb-3">
                            <label class="form-label" for="prix_max">Prix maximum (€ / pers.)</label>
                            <input class="form-control" type="number" min="0" id="prix_max" name="prix_max" placeholder="Ex: 40"
                                    value="<?= e((string) ($filtres['prix_max'] ?? '')) ?>">
                        </div>

                        <fieldset class="mb-3">
                            <legend class="form-label fs-6">Fourchette de prix (€ / pers.)</legend>
                            <div class="d-flex gap-2">
                                <label class="visually-hidden" for="fourchette_min">Prix minimum</label>
                                <input class="form-control" type="number" min="0" id="fourchette_min" name="fourchette_min" placeholder="Min"
                                        value="<?= e((string) ($filtres['fourchette_min'] ?? '')) ?>">
                                <label class="visually-hidden" for="fourchette_max">Prix maximum de la fourchette</label>
                                <input class="form-control" type="number" min="0" id="fourchette_max" name="fourchette_max" placeholder="Max"
                                        value="<?= e((string) ($filtres['fourchette_max'] ?? '')) ?>">
                            </div>
                        </fieldset>

                        <div class="mb-3">
                            <label class="form-label" for="theme">Thème</label>
                            <select class="form-select" id="theme" name="theme">
                                <option value="">Tous les thèmes</option>
                                <?php foreach ($themes as $theme): ?>
                                    <option value="<?= (int) $theme['theme_id'] ?>" <?= $filtres['theme'] === (int) $theme['theme_id'] ? 'selected' : '' ?>>
                                        <?=  e($theme['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="regime">Régime</label>
                            <select class="form-select" id="regime" name="regime">
                                <option value=""> Tous les régimes</option>
                                <?php foreach ($regimes as $regime): ?>
                                    <option value="<?= (int) $regime['regime_id'] ?>" <?= $filtres['regime'] === (int) $regime['regime_id'] ? 'selected' : '' ?>>
                                        <?= e($regime['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="personnes">Nombre de personnes</label>
                            <input class="form-control" type="number" min="1" id="personnes" name="personnes" placeholder="Ex: 10"
                                    aria-describedby="aide-personnes" value="<?= e((string) ($filtres['personnes'] ?? '')) ?>">
                                    <div id="aide-personnes" class="form-text">Affiche les menus possibles pour ce nombre de convives.</div>
                        </div>

                        <button type="submit" class="btn btn-brique w-100 mb-2 js-masquer">Filtrer</button>
                        <a id="reinitialiser" class="lien-brique" href="/menus">Réinitialiser</a>
                </form>
            </div>

            <!-- Résultats -->
            <div class="col-md-8 col-lg-9">
                <p id="nombre-menus" class="petit" role="status">
                    <?= $nb ?> menu<?= $nb > 1 ? 's' : '' ?> trouvé<?= $nb > 1 ? 's' : '' ?>
                </p>
                <div id="resultats" class="row g-4">
                    <?php require __DIR__ . '/_cartes.php'; ?>
                </div>                     
            </div>
        </div>
    </div>
</section>
<script src="/assets/js/menus.js" defer></script>