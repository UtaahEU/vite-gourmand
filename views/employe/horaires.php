<?php
/** @var array $jours */
/** @var array $erreurs */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Horaires</li>
            </ol>
        </nav>

        <h1 class="mb-2">Horaires</h1>
        <p class="mb-4">Ces horaires sont affichés en bas de toutes les pages du site.</p>

        <form method="post" action="/employe/horaires" class="carte h-auto table-responsive" novalidate>
            <?= csrf_champ() ?>
            <table class="table align-middle mb-3">
                <caption class="visually-hidden">Horaires d'ouverture, jour par jour</caption>
                <thead>
                    <tr>
                        <th scope="col">Jour</th>
                        <th scope="col">Fermé</th>
                        <th scope="col">Ouverture</th>
                        <th scope="col">Fermeture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jours as $j): ?>
                        <?php
                        $id = (int) $j['horaire_id'];
                        $ferme = $j['heure_ouverture'] === null;
                        $erreur = $erreurs[$id] ?? null;
                        ?>
                        <tr>
                            <th scope="row"><?= e($j['jour']) ?></th>
                            <td>
                                <input class="form-check-input" type="checkbox" name="horaires[<?= $id ?>][ferme]" id="ferme-<?= $id ?>" <?= $ferme ? 'checked' : '' ?>>
                                <label class="visually-hidden" for="ferme-<?= $id ?>">Fermé le <?= e(mb_strtolower($j['jour'])) ?></label>
                            </td>
                            <td>
                                <label class="visually-hidden" for="ouverture-<?= $id ?>">Ouverture le <?= e(mb_strtolower($j['jour'])) ?></label>
                                <input class="form-control <?= $erreur ? 'is-invalid' : '' ?>" type="time" name="horaires[<?= $id ?>][ouverture]" id="ouverture-<?= $id ?>"
                                       value="<?= e(substr((string) $j['heure_ouverture'], 0, 5)) ?>" <?= $erreur ? 'aria-describedby="erreur-' . $id . '"' : '' ?>>
                            </td>
                            <td>
                                <label class="visually-hidden" for="fermeture-<?= $id ?>">Fermeture le <?= e(mb_strtolower($j['jour'])) ?></label>
                                <input class="form-control <?= $erreur ? 'is-invalid' : '' ?>" type="time" name="horaires[<?= $id ?>][fermeture]" id="fermeture-<?= $id ?>"
                                       value="<?= e(substr((string) $j['heure_fermeture'], 0, 5)) ?>" <?= $erreur ? 'aria-describedby="erreur-' . $id . '"' : '' ?>>
                            </td>
                        </tr>
                        <?php if ($erreur): ?>
                            <tr>
                                <td colspan="4" class="border-0 pt-0">
                                    <div id="erreur-<?= $id ?>" class="invalid-feedback d-block"><?= e($j['jour']) ?> : <?= e($erreur) ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="petit">Quand « Fermé » est coché, les heures du jour sont ignorées.</p>
            <button class="btn btn-brique" type="submit">Enregistrer les horaires</button>
        </form>
    </div>
</section>