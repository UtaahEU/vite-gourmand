# Vite & Gourmand – Gestion de projet

## 1. Mon outil : Trello

J'ai organisé tout le projet sur un tableau **Trello** public :
https://trello.com/b/mX0QE7IB/ecf-vite-gourmand

J'ai choisi Trello parce qu'il est simple et visuel : on voit d'un coup d'œil ce qui reste à faire, ce qui est en cours et ce qui est fini. C'est la méthode **Kanban**.

### Les colonnes

| Colonne | Ce que j'y mets |
|---|---|
| 📋 Backlog | Toutes les tâches du projet, pas encore planifiées |
| 🎯 À faire | Les tâches prévues pour les prochains jours |
| 🔨 En cours | La tâche sur laquelle je travaille (une seule à la fois si possible) |
| 🧪 En test | Le code est écrit, je le teste |
| ✅ Terminé | Testé, fusionné dans `develop` et mis en ligne |
| 📚 Documentation | Les documents utiles (énoncé, liens) |

### Les cartes
J'ai découpé l'énoncé en **44 cartes** :
- **31 « US »** (*User Stories*, ou récits utilisateur) : une fonctionnalité vue par l'utilisateur. Par exemple, « US30 – Voir les statistiques de commandes ».
- **13 « TT »** (tâches techniques) : ce qui n'est pas une fonctionnalité mais doit être fait. Par exemple, « TT3 – Base de données » ou « TT8 – Déployer l'application ».

Chaque carte a :
- des **étiquettes** de couleur pour savoir **qui** est concerné (Visiteur, Utilisateur, Employé, Administrateur, Technique) et **l'importance** (Indispensable, Important) ;
- une **checklist** avec les critères à respecter. Une carte n'est terminée que quand toutes les cases sont cochées. Par exemple, pour US27 « Créer un compte employé » : impossible de créer un administrateur, e-mail envoyé sans le mot de passe, etc.

---

## 2. Comment j'ai avancé

### Les étapes
1. **Analyse** : lecture de l'énoncé, découpage en cartes Trello, choix des technologies.
2. **Conception** : schéma de la base de données (MCD), diagramme de classes, cas d'utilisation et diagramme de séquence.
3. **Maquettes** : charte graphique, wireframes puis mockups sur Figma (3 pages × PC et mobile).
4. **Code** : fonctionnalité par fonctionnalité, en 18 étapes, du plus simple (connexion à la base, page d'accueil) au plus complexe (commandes, espace employé, statistiques).
5. **Mise en ligne** sur Heroku dès que la commande fonctionnait, puis après chaque nouvelle étape.
6. **Documentation** : README, documentation technique, manuel d'utilisation.

### Mon planning

| Date | Ce que j'ai fait |
|---|---|
| 11 novembre | Lecture de l'énoncé, choix de la charte graphique et du logo, création du Trello |
| 12 novembre | Installation de l'environnement (XAMPP, Composer), création du dépôt GitHub, base de données |
| 13 novembre | Conception (MCD, diagrammes), maquettes Figma, début du code : accueil, menus et filtres, détail d'un menu |
| 14 – 15 novembre | Pages contact, mentions légales et CGV, inscription |
| 16 novembre | Connexion, mot de passe oublié, commande avec calcul du prix, e-mails, MongoDB, suivi des commandes |
| 17 novembre | Avis et profil, **première mise en ligne**, espace employé (menus, plats, commandes, avis, horaires) |
| 18 novembre | Espace administrateur (comptes employés, statistiques), documentation, rendu |

### Une fonctionnalité de A à Z
Pour chaque carte, je suivais toujours les mêmes étapes :
1. Je déplace la carte dans **En cours**.
2. Je crée une branche Git `feature/...` à partir de `develop`.
3. Je code.
4. Je teste chaque cas (cas normal, erreurs, personne non autorisée) et je corrige.
5. Je fais un commit et une pull request vers `develop`.
6. Je coche la checklist et je déplace la carte dans **Terminé**.
7. Régulièrement, je fusionne `develop` dans `main` et je mets en ligne.

---

## 3. Git et GitHub

Dépôt public : https://github.com/UtaahEU/vite-gourmand

| Branche | Rôle |
|---|---|
| `main` | La version en ligne. Je n'y code jamais directement |
| `develop` | La version de travail, testée |
| `feature/...` | Une branche par fonctionnalité (par exemple `feature/commande` ou `feature/statistiques`) |
| `docs/...` | Une branche pour la documentation et la conception |

Les messages de commit sont en français et commencent par un mot qui dit ce qu'ils font : `ajout:`, `correction:`, `bdd:` (base de données) ou `docs:` (documentation).

Grâce aux **pull requests**, on peut voir sur GitHub ce qui a changé pour chaque fonctionnalité.

---

## 4. Ce que j'en retiens

- **Découper en petites tâches** m'a beaucoup aidé : chaque carte était faisable en une ou deux heures, et je voyais ma progression.
- **Tester chaque fonctionnalité avant de passer à la suivante** m'a évité d'accumuler les bugs.
- **Mettre en ligne tôt** m'a permis de découvrir les problèmes propres au serveur (fichiers effacés au redémarrage, fuseau horaire, envoi des e-mails) avant la fin, et pas la veille du rendu.
- **Difficulté** : le temps. J'ai dû prioriser les fonctionnalités « Indispensables » et garder les améliorations (paiement, téléversement de photos) pour plus tard.
