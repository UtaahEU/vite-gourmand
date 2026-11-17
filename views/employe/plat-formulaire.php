<?php
/** @var int|null $id */
/** @var array $valeurs */
/** @var array $erreurs */
/** @var array $allergenes */
use App\Models\Plat;
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item"><a href="/employe/plats">Plats</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $id ? 'Modifier' : 'Nouveau' ?></li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4"><?= $id ? 'Modifier le plat' : 'Nouveau plat' ?></h1>

                <form class="carte h-auto" method="post" action="/employe/plat" novalidate>
                    <?= csrf_champ() ?>
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label" for="titre_plat">Nom du plat</label>
                            <input <?= attributs_champ($erreurs, 'titre_plat') ?> type="text" id="titre_plat" name="titre_plat"
                                   maxlength="100" required value="<?= e($valeurs['titre_plat']) ?>">
                            <?= erreur_champ($erreurs, 'titre_plat') ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="type">Type</label>
                            <select <?= str_replace('form-control', 'form-select', attributs_champ($erreurs, 'type')) ?> id="type" name="type" required>
                                <option value="">Choisir…</option>
                                <?php foreach (Plat::TYPES as $valeur => $libelle): ?>
                                    <option value="<?= $valeur ?>" <?= $valeurs['type'] === $valeur ? 'selected' : '' ?>><?= $libelle ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= erreur_champ($erreurs, 'type') ?>
                        </div>
                    </div>

                    <fieldset class="mb-4" aria-describedby="aide-allergenes">
                        <legend class="h3">Allergènes</legend>
                        <p id="aide-allergenes" class="form-text">
                            Cochez les allergènes présents dans le plat (14 allergènes majeurs de la réglementation européenne).
                            Aucune case cochée = plat sans allergène.
                        </p>
                        <div class="row">
                            <?php foreach ($allergenes as $a): ?>
                                <?php $aid = (int) $a['allergene_id']; ?>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allergenes[]"
                                               id="allergene-<?= $aid ?>" value="<?= $aid ?>"
                                               <?= in_array($aid, $valeurs['allergenes'], true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allergene-<?= $aid ?>"><?= e($a['libelle']) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button class="btn btn-brique" type="submit"><?= $id ? 'Enregistrer les modifications' : 'Créer le plat' ?></button>
                        <a class="btn btn-contour" href="/employe/plats">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>