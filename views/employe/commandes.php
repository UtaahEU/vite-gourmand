<?php
/** @var array $commandes */
/** @var ?string $statut */
/** @var ?string $client */
?>
<section class="section pt-4">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb fil-ariane" style="--bs-breadcrumb-divider: '>';">
                <li class="breadcrumb-item"><a href="/employe">Espace employé</a></li>
                <li class="breadcrumb-item active" aria-current="page">Commandes</li>
            </ol>
        </nav>

        <h1 class="mb-4">Commandes</h1>

        <form method="get" action="/employe/commandes" class="carte h-auto mb-4" role="search" aria-label="Filtrer les commandes">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="statut">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <?php foreach (\App\Models\Commande::STATUTS as $s): ?>
                            <option value="<?= $s ?>" <?= $s === $statut ? 'selected' : '' ?>><?= e(statut_commande($s)) ?></option>
                            <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="client">Client (nom, prénom ou e-mail)</label>
                    <input class="form-control" type="search" id="client" name="client" maxlength="100" value="<?= e($client) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-brique flex-grow-1" type="submit">Filtrer</button>
                    <a class="btn btn-contour" href="/employe/commandes">Effacer</a>
                </div>
            </div>
        </form>

        <p class="petit" aria-live="polite"><?= count($commandes) ?> commande(s)</p>

        <div class="carte h-auto table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Liste des commandes</caption>
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Client</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Prestation</th>
                        <th scope="col" class="text-end">Total</th>
                        <th scope="col">Statut</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($commandes === []): ?>
                        <tr><td colspan="7" class="text-center">Aucune commande ne correspond à ces filtres.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td class="text-nowrap"><?= e($c['numero_commande']) ?></td>
                            <td><?= e($c['prenom'] . ' ' . $c['nom']) ?></td>
                            <td><?= e($c['titre']) ?></td>
                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($c['date_prestation'])) ?></td>
                            <td class="text-end text-nowrap"><?= prix($c['prix_total']) ?> €</td>
                            <td><?= e(statut_commande($c['statut'])) ?></td>
                            <td class="text-end">
                                <a class="btn btn-contour btn-sm" href="/employe/commande?id=<?= (int) $c['commande_id'] ?>">
                                    Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>