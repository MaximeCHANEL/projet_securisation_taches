CREATE TABLE IF NOT EXISTS `utilisateurs` (
    `id_utilisateurs` INT NOT NULL AUTO_INCREMENT,
    `mail` VARCHAR(255) NOT NULL,
    `mot_de_passe` VARCHAR(255) NOT NULL,

    PRIMARY KEY (`id_utilisateurs`),
    UNIQUE KEY `mail` (`mail`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `sessions` (
    `id_sessions` INT NOT NULL AUTO_INCREMENT,
    `token_hash` CHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `id_utilisateurs` INT NOT NULL,

    PRIMARY KEY (`id_sessions`),
    UNIQUE KEY `token_hash` (`token_hash`),
    KEY `id_utilisateurs` (`id_utilisateurs`),

    CONSTRAINT `fk_sessions_utilisateur`
        FOREIGN KEY (`id_utilisateurs`)
        REFERENCES `utilisateurs` (`id_utilisateurs`)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `taches` (
    `id_taches` INT NOT NULL AUTO_INCREMENT,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `statut` ENUM('a_faire', 'en_cours', 'terminee')
        NOT NULL DEFAULT 'a_faire',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `id_utilisateurs` INT NOT NULL,

    PRIMARY KEY (`id_taches`),
    KEY `id_utilisateurs` (`id_utilisateurs`),

    CONSTRAINT `fk_taches_utilisateur`
        FOREIGN KEY (`id_utilisateurs`)
        REFERENCES `utilisateurs` (`id_utilisateurs`)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;