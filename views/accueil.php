<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <p class="surtitre">Traiteur · Bordeaux · Depuis 25 ans</p>
                <h1>Le fait maison, livré chez vous.</h1>
                <p>Julie et José cuisinent vos repas de fêtes, vos événements et vos repas de famille avec des produits frais et de saison.</p>
                <a class="btn btn-brique" href="/menus">Découvrir nos menus</a>
            </div>
            <div class="col-md-6">
                <img class="photo" src="/assets/img/hero.jpg" alt="Buffet de salades composées servies dans des saladiers" width="588" height="400">
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <img class="photo" src="/assets/img/cuisine.jpg" alt="Un cuisinier dresse des assiettes en cuisine" width="486" height="340">
            </div>
            <div class="col-md-6">
                <h2>Qui sommes-nous ?</h2>
                <p>Depuis 25 ans, Vite &amp; Gourmand régale les Bordelais. Noël, Pâques, anniversaires, mariages ou repas d'entreprise : nous concevons des menus qui évoluent au fil des saisons.</p>
                <p>Tout est préparé dans notre cuisine à Bordeaux, puis livré à l'heure qui vous convient.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="text-center mb-4">Une équipe de professionnels</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="carte">
                    <div class="pastille" aria-hidden="true"></div>
                    <h3>25 ans d'expérience</h3>
                    <p class="mb-0">Un savoir-faire reconnu à Bordeaux et dans toute la Gironde.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte">
                    <div class="pastille" aria-hidden="true"></div>
                    <h3>Produits frais et locaux</h3>
                    <p class="mb-0">Nous travaillons avec des producteurs de la région.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte">
                    <div class="pastille" aria-hidden="true"></div>
                    <h3>Livraison soignée</h3>
                    <p class="mb-0">Notre équipe livre à l'heure et installe le matériel de service.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($avis): ?>
    <section class="section">
        <div class="container">
            <h2 class="text-center mb-4">Ils nous ont fait confiance</h2>
            <div class="row g-4 justify-content-center">
                <?php foreach ($avis as $unAvis): ?>
                    <div class="col-md-4">
                        <figure class="carte mb-0">
                            <p class="etoiles">
                                <span class="visually-hidden">Note : <?= (int) $unAvis['note'] ?> sur 5</span>
                                <span aria-hidden="true"><?= str_repeat('★', (int) $unAvis['note']) . str_repeat('☆', 5 - (int) $unAvis['note']) ?></span>
                            </p>
                            <blockquote>
                                <p>« <?= e($unAvis['commentaire']) ?> »</p>
                            </blockquote>
                            <figcaption class="signature">
                                — <?= e($unAvis['prenom']) ?> <?= e(mb_substr($unAvis['nom'] ?? '', 0, 1)) ?>
                            </figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<section class="section">
    <div class="container">
        <div class="bandeau text-center">
            <h2>Envie de régaler vos invités ?</h2>
            <p>Parlez-nous de votre projet, nous vous répondons sous 48 h.</p>
            <a class="btn btn-papier" href="/contact">Nous contacter</a>
        </div>
    </div>
</section>