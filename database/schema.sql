-- =========================================================
-- Vite & Gourmand : création de la base de données
-- SGBD : MySQL 8 / MariaDB 10.4+
-- =========================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS avis, commande_suivi, commande, plat_allergene, menu_plat,
  menu_image, plat, menu, allergene, regime, theme, horaire, utilisateur, role;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Utilisateurs et rôles ----------
CREATE TABLE role (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE utilisateur (
  utilisateur_id   INT AUTO_INCREMENT PRIMARY KEY,
  nom              VARCHAR(50)  NULL,
  prenom           VARCHAR(50)  NULL,
  email            VARCHAR(255) NOT NULL UNIQUE,
  password         VARCHAR(255) NOT NULL,
  telephone        VARCHAR(20)  NULL,
  adresse_postale  VARCHAR(255) NULL,
  code_postal      VARCHAR(10)  NULL,
  ville            VARCHAR(100) NULL,
  pays             VARCHAR(50)  NOT NULL DEFAULT 'France',
  actif            TINYINT(1)   NOT NULL DEFAULT 1,
  reset_token_hash CHAR(64)     NULL,
  reset_expiration DATETIME     NULL,
  date_creation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  role_id          INT          NOT NULL,
  CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Référentiels ----------
CREATE TABLE theme (
  theme_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle  VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE regime (
  regime_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle   VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE allergene (
  allergene_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle      VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE horaire (
  horaire_id      INT AUTO_INCREMENT PRIMARY KEY,
  jour            VARCHAR(10) NOT NULL UNIQUE,
  heure_ouverture TIME NULL,   -- NULL = fermé
  heure_fermeture TIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Menus et plats ----------
CREATE TABLE menu (
  menu_id                 INT AUTO_INCREMENT PRIMARY KEY,
  titre                   VARCHAR(100)  NOT NULL,
  description             TEXT          NOT NULL,
  nombre_personne_minimum INT           NOT NULL,
  prix_par_personne       DECIMAL(8,2)  NOT NULL,
  conditions_menu         TEXT          NULL,
  quantite_restante       INT           NOT NULL DEFAULT 0,
  theme_id                INT           NOT NULL,
  regime_id               INT           NOT NULL,
  CONSTRAINT chk_menu_min   CHECK (nombre_personne_minimum > 0),
  CONSTRAINT chk_menu_prix  CHECK (prix_par_personne >= 0),
  CONSTRAINT chk_menu_stock CHECK (quantite_restante >= 0),
  CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme(theme_id),
  CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_image (
  image_id          INT AUTO_INCREMENT PRIMARY KEY,
  menu_id           INT          NOT NULL,
  chemin            VARCHAR(255) NOT NULL,
  texte_alternatif  VARCHAR(255) NOT NULL,   -- RGAA : alt obligatoire
  ordre             INT          NOT NULL DEFAULT 1,
  CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plat (
  plat_id    INT AUTO_INCREMENT PRIMARY KEY,
  titre_plat VARCHAR(100) NOT NULL,
  type       ENUM('entree','plat','dessert') NOT NULL,
  photo      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Association "propose" : un menu propose plusieurs plats, un plat peut être dans plusieurs menus
CREATE TABLE menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
  CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Association "contient" : un plat contient 0 à n allergènes
CREATE TABLE plat_allergene (
  plat_id      INT NOT NULL,
  allergene_id INT NOT NULL,
  PRIMARY KEY (plat_id, allergene_id),
  CONSTRAINT fk_pa_plat      FOREIGN KEY (plat_id)      REFERENCES plat(plat_id) ON DELETE CASCADE,
  CONSTRAINT fk_pa_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Commandes ----------
CREATE TABLE commande (
  commande_id           INT AUTO_INCREMENT PRIMARY KEY,
  numero_commande       VARCHAR(20)   NOT NULL UNIQUE,
  utilisateur_id        INT           NOT NULL,
  menu_id               INT           NOT NULL,
  date_commande         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_prestation       DATE          NOT NULL,
  heure_livraison       TIME          NOT NULL,
  adresse_livraison     VARCHAR(255)  NOT NULL,
  code_postal_livraison VARCHAR(10)   NOT NULL,
  ville_livraison       VARCHAR(100)  NOT NULL,
  distance_km           DECIMAL(6,2)  NOT NULL DEFAULT 0,
  nombre_personne       INT           NOT NULL,
  prix_menu             DECIMAL(10,2) NOT NULL,
  reduction             DECIMAL(10,2) NOT NULL DEFAULT 0,
  prix_livraison        DECIMAL(10,2) NOT NULL DEFAULT 0,
  prix_total            DECIMAL(10,2) NOT NULL,
  statut ENUM('en_attente','accepte','en_preparation','en_livraison','livre',
              'attente_retour_materiel','terminee','annulee') NOT NULL DEFAULT 'en_attente',
  pret_materiel         TINYINT(1)    NOT NULL DEFAULT 0,
  restitution_materiel  TINYINT(1)    NOT NULL DEFAULT 0,
  mode_contact          ENUM('gsm','mail') NULL,
  motif_modification    TEXT          NULL,
  CONSTRAINT chk_commande_personnes CHECK (nombre_personne > 0),
  CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
  CONSTRAINT fk_commande_menu        FOREIGN KEY (menu_id)        REFERENCES menu(menu_id),
  INDEX idx_commande_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique horodaté des statuts (suivi de commande)
CREATE TABLE commande_suivi (
  suivi_id          INT AUTO_INCREMENT PRIMARY KEY,
  commande_id       INT NOT NULL,
  statut ENUM('en_attente','accepte','en_preparation','en_livraison','livre',
              'attente_retour_materiel','terminee','annulee') NOT NULL,
  date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_suivi_commande FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Avis ----------
CREATE TABLE avis (
  avis_id        INT AUTO_INCREMENT PRIMARY KEY,
  note           TINYINT  NOT NULL,
  commentaire    TEXT     NOT NULL,
  statut         ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
  date_creation  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  utilisateur_id INT      NOT NULL,
  commande_id    INT      NOT NULL UNIQUE,   -- un seul avis par commande
  CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5),
  CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
  CONSTRAINT fk_avis_commande    FOREIGN KEY (commande_id)    REFERENCES commande(commande_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;