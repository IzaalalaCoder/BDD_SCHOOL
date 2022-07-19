-- La table fonction relie les unités d'enseignements avec les disciplines 
-- associés; Cela nous aide à les filtrer rapidement.
-- IDUEFON qui est la clé étrangère sur la clé primaire de l'unité d'enseignement
-- IDDISFON qui est la clé étrangère sur la clé primaire de la discipline
-- Le couple U, D veut dire que l'unité d'enseignement U présente une discipline D dans ces documents
CREATE TABLE FONCTION (
    IDUEFON INT NOT NULL,
    CONSTRAINT fk_fonction_ue
    FOREIGN KEY (IDUEFON)
    REFERENCES UNIENS(IDUE)
    ON DELETE CASCADE,
    IDDISFON INT NOT NULL,
    CONSTRAINT fk_fonction_discipline
    FOREIGN KEY (IDDISFON)
    REFERENCES DISCIPLINE(IDDIS)
    ON DELETE CASCADE,
    PRIMARY KEY (IDUEFON, IDDISFON)
)Engine = InnoDB;