<section class="section pt-4">
    <div class="container">
        <h1 class="mb-2">Espace employé</h1>
        <p class="mb-4">
            Bonjour <?= e(utilisateur()['prenom']) ?>,
            vous êtes connecté en tant que <strong><?= utilisateur()['role'] === 'administrateur' ? 'administrateur' : 'employé' ?></strong>
        </p>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="carte">
                    <h2 class="h3">Menus</h2>
                    <p>Créer, modifier et supprimer les menus proposés.</p>
                    <a class="btn btn-brique" href="/employe/menus">Gérer les menus</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
            <div class="carte">
                <h2 class="h3">Plats</h2>
                <p>Gérer les plats et leurs allergènes, réutilisables dans plusieurs menus.</p>
                <a class="btn btn-brique" href="/employe/plats">Gérer les plats</a>
            </div>
        </div>
    </div>
</section>