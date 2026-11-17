<?php
/** @var array $employes */
/** @var array $erreurs */
/** @var array $valeurs */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Comptes employés</li>
            </ol>
        </nav>

        <h1 class="mb-4">Comptes employés</h1>

        <div class="row g-4">
            <div class="col-lg-5">
                <form method="post" action="/admin/employes" class="carte h-auto" novalidate>
                    <h2 class="h3">Créer un compte employé</h2>
                    <p class="petit">L'employé reçoit un e-mail avec son identifiant. Le mot de passe n'y figure pas : transmettez-le-lui en main propre.</p>
                    <?= csrf_champ() ?>

                    <div class="mb-3">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input <?= attributs_champ($erreurs, 'prenom') ?> type="text" id="prenom" name="prenom" maxlength="50"
                               value="<?= e($valeurs['prenom']) ?>" autocomplete="off" required>
                        <?= erreur_champ($erreurs, 'prenom') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nom">Nom</label>
                        <input <?= attributs_champ($erreurs, 'nom') ?> type="text" id="nom" name="nom" maxlength="50"
                               value="<?= e($valeurs['nom']) ?>" autocomplete="off" required>
                        <?= erreur_champ($erreurs, 'nom') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail (identifiant)</label>
                        <input <?= attributs_champ($erreurs, 'email') ?> type="email" id="email" name="email"
                               value="<?= e($valeurs['email']) ?>" autocomplete="off" required>
                        <?= erreur_champ($erreurs, 'email') ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input <?= attributs_champ($erreurs, 'password', 'aide-mdp') ?> type="password" id="password" name="password"
                               autocomplete="new-password" required>
                        <?= erreur_champ($erreurs, 'password') ?>
                        <p id="aide-mdp" class="form-text mb-0">
                            Au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.
                        </p>
                    </div>
                    <button class="btn btn-brique" type="submit">Créer le compte</button>
                </form>
            </div>

            <div class="col-lg-7">
                <div class="carte h-auto table-responsive">
                    <table class="table align-middle mb-0">
                        <caption class="visually-hidden">Comptes employés</caption>
                        <thead>
                            <tr>
                                <th scope="col">Employé</th>
                                <th scope="col">Statut</th>
                                <th scope="col"><span class="visually-hidden">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($employes === []): ?>
                                <tr><td colspan="3" class="text-center">Aucun compte employé.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($employes as $emp): ?>
                                <tr>
                                    <td>
                                        <?= e($emp['prenom'] . ' ' . $emp['nom']) ?><br>
                                        <span class="petit"><?= e($emp['email']) ?></span>
                                    </td>
                                    <td><?= $emp['actif'] ? 'Actif' : '<strong>Désactivé</strong>' ?></td>
                                    <td class="text-end">
                                        <form method="post" action="/admin/employe/actif">
                                            <?= csrf_champ() ?>
                                            <input type="hidden" name="id" value="<?= (int) $emp['utilisateur_id'] ?>">
                                            <input type="hidden" name="actif" value="<?= $emp['actif'] ? '0' : '1' ?>">
                                            <button class="btn btn-sm <?= $emp['actif'] ? 'btn-contour' : 'btn-brique' ?>" type="submit">
                                                <?= $emp['actif'] ? 'Désactiver' : 'Réactiver' ?>
                                                <span class="visually-hidden"> le compte de <?= e($emp['prenom'] . ' ' . $emp['nom']) ?></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>