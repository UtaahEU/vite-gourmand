# Vite & Gourmand – Documentation technique

## 1. Mes choix techniques

### PHP sans framework
J'ai choisi de coder en PHP « simple », sans framework comme Symfony, pour bien comprendre comment un site fonctionne de A à Z.

J'ai organisé le code en **MVC** (Modèle – Vue – Contrôleur), pour ne pas tout mélanger :
- **Modèles** (`src/Models`) : ils parlent à la base de données (lire, ajouter, modifier).
- **Vues** (`views/`) : le HTML des pages.
- **Contrôleurs** (`src/Controllers`) : ils font le lien. Ils vérifient qui est connecté et ce qui a été saisi, demandent les données au modèle, puis affichent la bonne vue.

Toutes les visites passent par un seul fichier, `public/index.php`. Il contient la liste des **routes** : chaque adresse (par exemple `/menus`) y est reliée au contrôleur qui s'en occupe.

### MySQL pour les données du site
Les menus, plats, allergènes, comptes, commandes et avis sont dans une base **MySQL**. Ces données sont liées entre elles (un menu contient des plats, une commande appartient à un client…). Une base relationnelle garde ces liens cohérents.

### MongoDB pour les statistiques
L'énoncé demande une base **NoSQL** pour les statistiques. À chaque commande, j'enregistre aussi un petit résumé dans **MongoDB Atlas** (menu, prix, statut, date). Je le mets à jour quand la commande change.

La page Statistiques de l'administrateur demande ensuite à MongoDB de compter les commandes et d'additionner le chiffre d'affaires par menu.

Si MongoDB ne répond pas, **la commande du client fonctionne quand même** : l'erreur est juste notée dans `logs/erreurs.log`.

### L'affichage
- **Bootstrap** pour que le site s'adapte au téléphone, à la tablette et à l'ordinateur, avec mes couleurs (kraft, papier, encre, brique) dans `style.css`.
- **JavaScript** pour filtrer les menus **sans recharger la page** (avec `fetch`).
- **Chart.js** pour le graphique des statistiques.

---

## 2. Mon environnement de travail

| Outil | À quoi il m'a servi |
|---|---|
| VS Code | Écrire le code |
| XAMPP | Faire tourner PHP et MySQL en local |
| phpMyAdmin | Voir et gérer la base de données |
| Composer | Installer les bibliothèques PHP (PHPMailer, phpdotenv, MongoDB) |
| Git et GitHub | Sauvegarder les versions du code |
| Figma | Charte graphique, wireframes et maquettes |
| Looping | Dessiner le schéma de la base de données (MCD) |
| PlantUML | Diagrammes de cas d'utilisation et de séquence |
| Trello | Organiser mes tâches |

Les mots de passe (base de données, MongoDB, boîte mail) sont dans un fichier **`.env`** qui n'est pas envoyé sur GitHub. Sur le site en ligne, ils sont rentrés dans les réglages de Heroku.

---

## 3. La base de données

Les schémas sont dans `docs/conception/` :
- `mcd.png` : le schéma de la base (MCD)
- `diagramme-classes.png` : le diagramme de classes
- `cas-utilisation.png` : qui peut faire quoi sur le site
- `sequence.png` : les étapes d'une commande

La base a **14 tables**. J'ai ajouté quelques tables au modèle de l'énoncé :
- `menu_image` : pour mettre plusieurs photos par menu ;
- `commande_suivi` : pour garder l'historique des statuts d'une commande, avec la date ;
- les 14 allergènes officiels sont déjà rentrés dans la base.

---

## 4. Les règles du site

| Règle | Comment je l'ai faite |
|---|---|
| Prix | Prix par personne × nombre de personnes. **−10 %** si on commande pour au moins 5 personnes de plus que le minimum |
| Livraison | Gratuite à Bordeaux. Ailleurs : **5 € + 0,59 € par km**. Je trouve la position de l'adresse grâce à l'API gratuite de l'IGN, puis je calcule la distance depuis Bordeaux |
| Stock | Le nombre de menus disponibles baisse quand on commande et remonte si la commande est annulée |
| Statuts | La commande avance étape par étape : en attente → acceptée → en préparation → en livraison → livrée → (retour du matériel) → terminée. L'employé ne peut choisir que l'étape suivante |
| Deux employés en même temps | Si deux employés changent le même statut au même moment, le deuxième reçoit un message au lieu d'écraser le choix du premier |
| Annuler ou modifier | Un employé peut annuler ou modifier une commande avant la livraison, après avoir contacté le client. Il doit dire comment il l'a contacté et pourquoi. Le client reçoit un e-mail |
| Avis | Un seul avis par commande terminée. Il n'apparaît sur l'accueil qu'après validation par un employé |
| Suppression | On ne peut pas supprimer un menu déjà commandé ni un plat utilisé dans un menu. Un employé qui part est **désactivé**, pas supprimé, pour garder l'historique |

---

## 5. La sécurité

| Danger | Ce que j'ai fait |
|---|---|
| Quelqu'un tape du code SQL dans un formulaire pour pirater la base (**injection SQL**) | Toutes mes requêtes sont des **requêtes préparées** : ce que tape l'utilisateur n'est jamais mélangé au SQL |
| Quelqu'un écrit du code JavaScript dans un avis ou un nom (**faille XSS**) | Tout ce qui est affiché passe par ma fonction `e()`, qui transforme les caractères spéciaux en texte |
| Un autre site envoie un formulaire à la place de l'utilisateur (**faille CSRF**) | Chaque formulaire contient un code secret (**jeton CSRF**) que je vérifie |
| Vol des mots de passe | Les mots de passe sont **hachés** (`password_hash`) : même moi je ne peux pas les lire. Ils doivent faire au moins 10 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial |
| Mot de passe oublié | Le lien envoyé par e-mail ne marche qu'**une fois** et pendant **1 heure** |
| Vol de session | Le cookie de connexion n'est pas lisible par JavaScript, et l'identifiant de session change à chaque connexion |
| Un client va sur une page d'employé | Chaque page vérifie le rôle de la personne connectée. Sinon : page **403 « Accès refusé »**. Un employé désactivé est déconnecté tout de suite |
| Saisies fausses | Je revérifie tout côté serveur, même si le formulaire HTML vérifie déjà |
| Mots de passe dans le code | Ils sont dans `.env`, jamais sur GitHub |

---

## 6. L'accessibilité

- Un lien « Aller au contenu » en haut de chaque page, pour les personnes qui naviguent au clavier.
- Chaque champ de formulaire a son étiquette (`label`), et les messages d'erreur sont reliés au champ pour les lecteurs d'écran.
- Les couleurs ont assez de contraste pour être lisibles.
- Le graphique des statistiques est accompagné d'un **tableau** avec les mêmes chiffres, car un lecteur d'écran ne peut pas lire un graphique.

---

## 7. La mise en ligne

### Ce que j'utilise
- **Heroku** pour héberger le site ;
- **JawsDB**, une base MySQL fournie par Heroku ;
- **MongoDB Atlas** (version gratuite) pour les statistiques ;
- une **adresse Gmail dédiée** pour envoyer les vrais e-mails.

### Les étapes
1. Créer l'application sur Heroku et ajouter JawsDB.
2. Importer `schema.sql` puis `data.sql` dans JawsDB.
3. Rentrer les mots de passe et adresses dans **Settings → Config Vars** de Heroku (comme le `.env`).
4. Relier Heroku à mon dépôt GitHub, puis cliquer sur **Deploy Branch** (branche `main`).

À chaque nouvelle fonctionnalité, je refais seulement : pull request `develop` → `main`, puis **Deploy Branch**.

### Les problèmes que j'ai rencontrés
- **Les fichiers ajoutés sur Heroku disparaissent** quand le serveur redémarre. Pour les photos des menus, l'employé les choisit donc parmi celles déjà présentes dans le projet, au lieu d'en envoyer de nouvelles. Plus tard, on pourrait les stocker sur un service externe (comme Amazon S3).
- **L'heure** : j'ai réglé PHP et MySQL sur l'heure de Paris. MongoDB enregistre les dates à l'heure universelle (UTC) : je convertis donc les dates des filtres des statistiques.
- **Le cache du navigateur** : après une modification du CSS, le navigateur gardait parfois l'ancienne version. Il fallait appuyer sur Ctrl+F5.

---

## 8. Ce que je pourrais ajouter plus tard

- Envoyer ses propres photos de menus (avec un stockage externe).
- Le paiement en ligne.
- Des tests automatiques du code.
