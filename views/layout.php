<?php $chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre ?? 'Accueil') ?> – Vite &amp; Gourmand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<a class="lien-evitement" href="#contenu">Aller au contenu</a>

<header class="entete">
    <nav class="navbar navbar-expand-md" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand marque" href="/">
                <img src="/assets/img/logo.svg" alt="" width="40" height="40">
                Vite &amp; Gourmand
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="menuPrincipal">
                <ul class="navbar-nav align-items-md-center gap-md-3">
                    <li class="nav-item">
                        <a class="nav-link" href="/" <?= $chemin === '/' ? 'aria-current="page"' : '' ?>>Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/menus" <?= str_starts_with($chemin, '/menu') ? 'aria-current="page"' : '' ?>>Menus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact" <?= $chemin === '/contact' ? 'aria-current="page"' : '' ?>>Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-brique" href="/connexion">Connexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="contenu">
    <?= $contenu ?>
</main>

<footer class="pied">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h2>Nos horaires</h2>
                <!-- Les horaires viendront de la base au point 3 -->
            </div>
            <div class="col-md-6 text-md-end">
                <h2>Vite &amp; Gourmand</h2>
                <p>Traiteur à Bordeaux depuis 25 ans.</p>
                <ul class="list-unstyled">
                    <li><a href="/mentions-legales">Mentions légales</a></li>
                    <li><a href="/cgv">Conditions générales de vente</a></li>
                    <li><a href="/contact">Nous contacter</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>