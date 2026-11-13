-- =========================================================
-- Vite & Gourmand : jeu de données de test
-- =========================================================
SET NAMES utf8mb4;

INSERT INTO role (role_id, libelle) VALUES
(1,'utilisateur'),(2,'employe'),(3,'administrateur');

INSERT INTO theme (theme_id, libelle) VALUES
(1,'Noël'),(2,'Pâques'),(3,'Classique'),(4,'Événement');

INSERT INTO regime (regime_id, libelle) VALUES
(1,'Classique'),(2,'Végétarien'),(3,'Vegan'),(4,'Sans gluten');

-- Les 14 allergènes réglementaires (règlement UE INCO)
INSERT INTO allergene (allergene_id, libelle) VALUES
(1,'Gluten'),(2,'Crustacés'),(3,'Œufs'),(4,'Poissons'),(5,'Arachides'),
(6,'Soja'),(7,'Lait'),(8,'Fruits à coque'),(9,'Céleri'),(10,'Moutarde'),
(11,'Sésame'),(12,'Sulfites'),(13,'Lupin'),(14,'Mollusques');

INSERT INTO horaire (horaire_id, jour, heure_ouverture, heure_fermeture) VALUES
(1,'Lundi','09:00','18:00'),(2,'Mardi','09:00','18:00'),(3,'Mercredi','09:00','18:00'),
(4,'Jeudi','09:00','18:00'),(5,'Vendredi','09:00','19:00'),(6,'Samedi','09:00','13:00'),
(7,'Dimanche',NULL,NULL);

-- Comptes de test (José = administrateur, créé directement en base)
INSERT INTO utilisateur (utilisateur_id, nom, prenom, email, password, telephone, adresse_postale, code_postal, ville, role_id) VALUES
(1,'Garcia','José','jose@vite-gourmand.fr','HASH_ADMIN','$2y$10$3kLGuZJtPEZHdBZBBpchN.LwLuGQzTqphpQ7I/DK46yxwVALvwK5a','12 rue Sainte-Catherine','33000','Bordeaux',3),
(2,'Durand','Julie','julie@vite-gourmand.fr','HASH_EMPLOYE','$2y$10$1urkXYgShU4ktu95QRxrGOrzzsRQkWJyHRGOSsllF6.XKpjpJ8dia','12 rue Sainte-Catherine','33000','Bordeaux',2),
(3,'Martin','Claire','claire.martin@exemple.fr','HASH_UTILISATEUR','$2y$10$GO1a/KBYoFp6UyzzX.xVqObr60NurcAEf4W/PEr7vHIjzLs4au0CO','8 cours de l''Intendance','33000','Bordeaux',1),
(4,'Benali','Karim','karim.benali@exemple.fr','HASH_UTILISATEUR','$2y$10$GO1a/KBYoFp6UyzzX.xVqObr60NurcAEf4W/PEr7vHIjzLs4au0CO','25 avenue de la Marne','33700','Mérignac',1);

INSERT INTO menu (menu_id, titre, description, nombre_personne_minimum, prix_par_personne, conditions_menu, quantite_restante, theme_id, regime_id) VALUES
(1,'Noël tradition','Un repas de fête généreux autour du foie gras maison et du chapon farci aux marrons.',8,45.00,
 'À commander au moins 14 jours avant la prestation. À conserver au réfrigérateur entre 0 et 4 °C et à consommer sous 48 h.',10,1,1),
(2,'Noël végétarien','Toute la magie de Noël sans viande : velouté de saison, risotto aux cèpes et bûche maison.',6,38.00,
 'À commander au moins 10 jours avant la prestation. À conserver au réfrigérateur.',8,1,2),
(3,'Pâques en famille','Le gigot d''agneau aux herbes et ses douceurs chocolatées pour un déjeuner de Pâques réussi.',10,32.00,
 'À commander au moins 7 jours avant la prestation.',12,2,1),
(4,'Le Bordelais','Les classiques de la région : entrecôte bordelaise, salade de chèvre chaud et canelés.',4,29.00,
 'À commander au moins 3 jours avant la prestation.',20,3,1),
(5,'Buffet vert','Un buffet 100 % végétal, coloré et gourmand, idéal pour les grands événements.',20,25.00,
 'À commander au moins 21 jours avant la prestation. Vaisselle prêtée : à restituer sous 10 jours ouvrés.',5,4,3),
(6,'Cocktail dînatoire','Bouchées salées et sucrées pour vos inaugurations, anniversaires et soirées d''entreprise.',15,22.00,
 'À commander au moins 10 jours avant la prestation. Matériel de service prêté sur demande.',15,4,1);

INSERT INTO menu_image (menu_id, chemin, texte_alternatif, ordre) VALUES
(1,'assets/img/menus/noel-tradition-1.jpg','Volaille rôtie dorée et sa sauce, servie pour Noël',1),
(1,'assets/img/menus/noel-tradition-2.jpg','Bûche au chocolat praliné',2),
(2,'assets/img/menus/noel-vegetarien-1.jpg','Table de fête avec grenades, fruits secs, sucres d''orge et biscuits de Noël',1),
(3,'assets/img/menus/paques-1.jpg','Gigot d''agneau tranché et pommes de terre rôties aux herbes',1),
(4,'assets/img/menus/bordelais-1.jpg','Planche apéritive avec jambon, fromage fondu et frites',1),
(4,'assets/img/menus/bordelais-2.jpg','Assortiment de canelés bordelais',2),
(5,'assets/img/menus/buffet-vert-1.jpg','Buffet de crudités colorées : radis, tomates cerises et légumes',1),
(6,'assets/img/menus/cocktail-1.jpg','Blinis au saumon fumé et crème sur lit de salade',1);

INSERT INTO plat (plat_id, titre_plat, type) VALUES
(1,'Foie gras maison et chutney de figues','entree'),
(2,'Velouté de potimarron','entree'),
(3,'Salade de chèvre chaud au miel','entree'),
(4,'Houmous de pois chiches et crudités','entree'),
(5,'Chapon farci aux marrons','plat'),
(6,'Entrecôte bordelaise et pommes grenaille','plat'),
(7,'Gigot d''agneau aux herbes','plat'),
(8,'Risotto aux cèpes','plat'),
(9,'Curry de légumes au lait de coco','plat'),
(10,'Bûche chocolat praliné','dessert'),
(11,'Canelés bordelais','dessert'),
(12,'Nid de Pâques au chocolat','dessert'),
(13,'Salade de fruits frais à la menthe','dessert'),
(14,'Mousse au chocolat noir vegan','dessert');

INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1,1),(1,2),(1,5),(1,10),
(2,2),(2,3),(2,8),(2,10),
(3,3),(3,7),(3,11),(3,12),
(4,3),(4,6),(4,11),(4,13),
(5,4),(5,9),(5,13),(5,14),
(6,1),(6,4),(6,6),(6,11);

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(1,12),(2,7),(2,9),(3,1),(3,7),(4,11),(5,1),(5,3),(5,9),(6,7),(6,12),
(8,7),(8,12),(9,9),(10,1),(10,3),(10,7),(10,8),(11,1),(11,3),(11,7),
(12,3),(12,6),(12,7),(14,6);

-- Commandes de test : un statut différent pour chaque parcours
-- Règles : -10 % si personnes >= minimum + 5 ; livraison hors Bordeaux = 5 € + 0,59 €/km
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison,
  adresse_livraison, code_postal_livraison, ville_livraison, distance_km, nombre_personne,
  prix_menu, reduction, prix_livraison, prix_total, statut, pret_materiel, restitution_materiel) VALUES
(1,'VG-2026-0001',3,4,'2026-10-02 10:15:00','2026-10-12','12:00:00','8 cours de l''Intendance','33000','Bordeaux',0,10,
  290.00,29.00,0.00,261.00,'terminee',0,0),
(2,'VG-2026-0002',4,1,'2026-11-05 09:30:00','2026-12-24','19:00:00','25 avenue de la Marne','33700','Mérignac',8,8,
  360.00,0.00,9.72,369.72,'accepte',1,0),
(3,'VG-2026-0003',3,3,'2026-11-10 18:40:00','2027-03-28','12:30:00','8 cours de l''Intendance','33000','Bordeaux',0,12,
  384.00,0.00,0.00,384.00,'en_attente',0,0),
(4,'VG-2026-0004',4,6,'2026-10-20 11:00:00','2026-11-08','18:00:00','3 place de la Ve République','33600','Pessac',6,20,
  440.00,44.00,8.54,404.54,'attente_retour_materiel',1,0),
(5,'VG-2026-0005',4,4,'2026-09-15 14:20:00','2026-09-27','12:00:00','40 cours de la Libération','33400','Talence',5,4,
  116.00,0.00,7.95,123.95,'terminee',0,0);

INSERT INTO commande_suivi (commande_id, statut, date_modification) VALUES
(1,'en_attente','2026-10-02 10:15:00'),(1,'accepte','2026-10-02 14:00:00'),(1,'en_preparation','2026-10-12 08:00:00'),
(1,'en_livraison','2026-10-12 11:15:00'),(1,'livre','2026-10-12 11:55:00'),(1,'terminee','2026-10-12 18:00:00'),
(2,'en_attente','2026-11-05 09:30:00'),(2,'accepte','2026-11-05 15:20:00'),
(3,'en_attente','2026-11-10 18:40:00'),
(4,'en_attente','2026-10-20 11:00:00'),(4,'accepte','2026-10-20 16:45:00'),(4,'en_preparation','2026-11-08 09:00:00'),
(4,'en_livraison','2026-11-08 16:30:00'),(4,'livre','2026-11-08 17:50:00'),(4,'attente_retour_materiel','2026-11-09 10:00:00'),
(5,'en_attente','2026-09-15 14:20:00'),(5,'accepte','2026-09-15 17:00:00'),(5,'en_preparation','2026-09-27 08:30:00'),
(5,'en_livraison','2026-09-27 11:00:00'),(5,'livre','2026-09-27 11:45:00'),(5,'terminee','2026-09-27 15:00:00');

INSERT INTO avis (note, commentaire, statut, date_creation, utilisateur_id, commande_id) VALUES
(5,'Repas délicieux et livraison à l''heure. Nos invités ont adoré les canelés !','valide','2026-10-13 09:00:00',3,1),
(4,'Très bon dans l''ensemble, la viande était parfaitement cuite.','en_attente','2026-09-28 20:15:00',4,5);