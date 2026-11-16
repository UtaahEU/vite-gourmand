<?php
/** @var array $commande */
/** @var array $menu */
/** @var array $client */
$p = $commande['prix'];
?>
<section class="section pt-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <h1 class="mb-4"> Récapitulatif</h1>

                <div class="carte h-auto">
                    <h2 class="h3"><?= e($menu['titre']) ?> </h2>
                    <p>
                        Pour <?= (int) $commande['personnes'] ?> personnes, 
                        le <?= e(date('d/m/Y', strtotime($commande['date']))) ?> à <?=  e($commande['heure']) ?><br>
                        Livraison à <?= e($commande['adresse']) ?>, <?= e($commande['code_postal']) ?> <?= e($commande['ville']) ?><br>
                        Client : <?= e($client['prenom']) . ' ' . e($client['nom']) ?> – <?= e($client['email']) ?>
                    </p>

                    <table class="table mb-4">
                        <caption class="visually-hidden">Détail du prix</caption>
                        <tbody>
                            <tr>
                                <th scope="row">Menu (<?= (int) $commande['personnes'] ?> x <?= prix($menu['prix_par_personne']) ?> €)</th>
                                <td class="text-end text-nowrap"><?= prix($p['menu']) ?> €</td>
                            </tr>
                            <?php if ($p['reduction'] > 0) : ?>
                                <tr>
                                    <th scope="row">Réduction de 10 %</th>
                                    <td class="text-end text-nowrap">- <?= prix($p['reduction']) ?> €</td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th scope="row">
                                    Livraison
                                    <?= $p['livraison'] > 0 ? '(' . prix($commande['km']) . ' km depuis Bordeaux)' : '(Bordeaux, offerte)' ?>
                                </th>
                                <td class="text-end text-nowrap"><?= prix($p['livraison']) ?> €</td>
                            </tr>
                            <tr>
                                <th scope="row" class="align-middle">Total</th>
                                <td class="text-end text-nowrap prix-grand"> <?= prix($p['total']) ?> €</td>
                            </tr>
                        </tbody>
                    </table>

                    <form method="post" action="/commande/confirmer">
                        <?= csrf_champ() ?>
                        <button class="btn btn-brique w-100" type=submit> Confirmer la commade</button>
                    </form>
                    <p class="mt-3 mb-0">
                        <a class="lien-brique" href="/commande?.menu=<?= (int) $menu['menu_id'] ?>">Modifier</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>