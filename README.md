# Vite & Gourmand

Site web du traiteur bordelais **Vite & Gourmand**, réalisé pour mon ECF (Graduate Développeur IA).

Sur le site :
- les **visiteurs** voient les menus, les filtrent (prix, thème, régime…) et peuvent nous contacter ;
- les **clients** commandent un menu, suivent leur commande et laissent un avis ;
- les **employés** gèrent les menus, les plats, les commandes, les avis et les horaires ;
- l'**administrateur** (José) gère en plus les comptes des employés et voit les statistiques.

**Liens utiles**
- Le site en ligne : https://vite-gourmand-ecfec-e4c53de92eba.herokuapp.com
- Le code sur GitHub : https://github.com/UtaahEU/vite-gourmand
- Mon tableau Trello : https://trello.com/b/mX0QE7IB/ecf-vite-gourmand

---

## Ce que j'ai utilisé

| Pour… | J'ai utilisé |
|---|---|
| L'affichage des pages | HTML, CSS, Bootstrap |
| Les filtres sans recharger la page et le graphique | JavaScript, Chart.js |
| Le fonctionnement du site | PHP 8.2 (sans framework) |
| Les données (menus, commandes, comptes…) | MySQL |
| Les statistiques | MongoDB Atlas |
| L'envoi des e-mails | PHPMailer |
| Installer les bibliothèques PHP | Composer |
| Mettre le site en ligne | Heroku |

---

## Installer le projet sur son ordinateur

### Ce qu'il faut avoir
- **XAMPP** (il contient PHP 8.2, MySQL et phpMyAdmin)
- **Composer**
- **Git**

### Étape 1 : récupérer le code
Dans un terminal :
```bash
git clone https://github.com/UtaahEU/vite-gourmand.git
cd vite-gourmand
composer install --ignore-platform-req=ext-mongodb
```
> L'option `--ignore-platform-req=ext-mongodb` permet d'installer le projet même si MongoDB n'est pas installé sur l'ordinateur. Le site marche quand même ; seule la page « Statistiques » affiche un message d'indisponibilité.

### Étape 2 : créer la base de données
1. Ouvrir **phpMyAdmin** (http://localhost/phpmyadmin).
2. Créer une base nommée `vite_gourmand`.
3. Onglet **Importer** : importer d'abord `database/schema.sql` (il crée les tables), puis `database/data.sql` (il ajoute les données de test).

### Étape 3 : remplir le fichier de configuration
1. Copier le fichier `.env.example` et renommer la copie en `.env`.
2. Le remplir :

| Ligne | À quoi elle sert |
|---|---|
| `APP_URL` | L'adresse du site (pour les liens dans les e-mails) |
| `CONTACT_EMAIL` | L'adresse qui reçoit les messages du formulaire de contact |
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | La connexion à la base MySQL |
| `MONGODB_URI`, `MONGODB_DB` | La connexion à MongoDB (statistiques) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM` | Le serveur qui envoie les e-mails |

> **Astuce** : si on laisse `MAIL_HOST` vide, les e-mails ne sont pas envoyés mais écrits dans le fichier `logs/mails.log`. C'est pratique pour tester sans vraie boîte mail.

Le fichier `.env` contient des mots de passe : il n'est **jamais** envoyé sur GitHub (il est listé dans `.gitignore`).

### Étape 4 : lancer le site
```bash
php -S localhost:3000 -t public
```
Puis ouvrir http://localhost:3000 dans le navigateur.

---

## Comptes de test

| Rôle | E-mail |
|---|---|
| Administrateur (José) | jose@vite-gourmand.fr |
| Employée (Julie) | julie@vite-gourmand.fr |
| Client (Claire) | claire.martin@exemple.fr |
| Client (Karim) | karim.benali@exemple.fr |

Les mots de passe sont dans le **manuel d'utilisation** : `docs/manuel-utilisation.pdf`.
Le compte administrateur est créé directement dans la base : on ne peut pas créer d'administrateur depuis le site.

---

## Organisation des dossiers

```
vite-gourmand/
├── public/        Le seul dossier visible par les visiteurs : index.php, CSS, JavaScript, images
├── src/
│   ├── Core/          Les outils de base (connexion à la base, envoi d'e-mails, routes…)
│   ├── Controllers/   Ce qui se passe quand on visite une page ou envoie un formulaire
│   └── Models/        Les requêtes vers la base de données
├── views/         Le HTML des pages
├── database/      Les fichiers SQL (tables + données de test)
├── docs/          Charte graphique, maquettes, schémas de la base, documentation
└── logs/          Les e-mails et erreurs en local (pas envoyés sur GitHub)
```

---

## Comment j'ai utilisé Git

- `main` : la version en ligne
- `develop` : la version en cours, testée
- `feature/...` : une branche pour chaque fonctionnalité

Pour chaque fonctionnalité : je crée une branche `feature/...`, je code et je teste, puis je la fusionne dans `develop` avec une pull request. Quand c'est bon, je fusionne `develop` dans `main` et je mets en ligne.

---

## Documentation

Dans le dossier `docs/` :
- `documentation-technique.md` : mes choix techniques, la sécurité et la mise en ligne
- `charte/` : charte graphique, wireframes et maquettes
- `conception/` : schéma de la base de données et diagrammes
