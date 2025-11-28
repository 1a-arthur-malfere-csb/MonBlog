/* Testé sous MySQL 5.x */
/* Schema de la base de données pour le blog 'monblog' */

CREATE DATABASE IF NOT EXISTS monblog CHARACTER SET utf8 COLLATE utf8_general_ci;
USE monblog;

-- Suppression des tables dans l'ordre inverse des dépendances pour éviter les erreurs de clé étrangère.
DROP TABLE IF EXISTS T_COMMENTAIRE;
DROP TABLE IF EXISTS T_UTILISATEUR;
DROP TABLE IF EXISTS T_BILLET;
DROP TABLE IF EXISTS T_VOTE;

-- Création de la table pour les billets (articles)
CREATE TABLE T_BILLET (
  BIL_ID INT PRIMARY KEY AUTO_INCREMENT,
  BIL_DATE DATETIME NOT NULL,
  BIL_TITRE VARCHAR(100) NOT NULL,
  BIL_CONTENU VARCHAR(400) NOT NULL
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Création de la table pour les utilisateurs
CREATE TABLE T_UTILISATEUR (
  UTI_ID INT PRIMARY KEY AUTO_INCREMENT,
  UTI_EMAIL VARCHAR(100) NOT NULL UNIQUE,
  UTI_PASSWORD VARCHAR(255) NOT NULL
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Création de la table pour les commentaires
CREATE TABLE T_COMMENTAIRE (
  COM_ID INT PRIMARY KEY AUTO_INCREMENT,
  COM_DATE DATETIME NOT NULL,
  COM_CONTENU VARCHAR(200) NOT NULL,
  BIL_ID INT NOT NULL,
  UTI_ID INT NOT NULL,
  COM_MODIFIED BOOLEAN DEFAULT FALSE,
  COM_MODIFIED_DATE DATETIME DEFAULT NULL,
  CONSTRAINT fk_com_bil FOREIGN KEY(BIL_ID) REFERENCES T_BILLET(BIL_ID),
  CONSTRAINT fk_com_uti FOREIGN KEY(UTI_ID) REFERENCES T_UTILISATEUR(UTI_ID)
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Création de la table pour les votes sur les commentaires
CREATE TABLE T_VOTE (
  VOT_ID INT PRIMARY KEY AUTO_INCREMENT,
  COM_ID INT NOT NULL,
  UTI_ID INT NOT NULL,
  VOT_TYPE ENUM('up', 'down') NOT NULL,
  VOT_DATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vote_com FOREIGN KEY(COM_ID) REFERENCES T_COMMENTAIRE(COM_ID) ON DELETE CASCADE,
  CONSTRAINT fk_vote_uti FOREIGN KEY(UTI_ID) REFERENCES T_UTILISATEUR(UTI_ID) ON DELETE CASCADE,
  UNIQUE KEY unique_user_comment_vote (COM_ID, UTI_ID)
) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Ajout d'index pour optimiser les performances
CREATE INDEX idx_vote_comment ON T_VOTE(COM_ID);
CREATE INDEX idx_vote_user ON T_VOTE(UTI_ID);

-- Insertion de quelques billets de démonstration
-- INSERT INTO T_BILLET(BIL_DATE, BIL_TITRE, BIL_CONTENU) VALUES
-- (NOW(), 'Premier billet', 'Bonjour monde ! Ceci est le premier billet sur mon blog.');
-- INSERT INTO T_BILLET(BIL_DATE, BIL_TITRE, BIL_CONTENU) VALUES
-- (NOW(), 'Au travail', 'Il faut enrichir ce blog dès maintenant.');
-- INSERT INTO T_BILLET(BIL_DATE, BIL_TITRE, BIL_CONTENU) VALUES
-- (NOW(), 'Troisième billet', 'Encore un billet pour tester le blog.');
