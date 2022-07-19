-- Ici on a la table UNIENS 
-- qui contient les données suivantes 
-- IDUE qui est la clé primaire de l'unité d'enseignement
-- NOMUE qui est le nom de l'unité d'enseignement
-- NOMPROFUE qui est le nom du professeur
-- LEVELUE qui correspondra au niveau de la matière
CREATE TABLE UNIENS (
    IDUE INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    NOMUE VARCHAR(50) NOT NULL,
    NOMPROFUE VARCHAR(50) NOT NULL,
    LEVELUE INT NOT NULL,
    CONSTRAINT fk_uniens_level
    FOREIGN KEY (LEVELUE)
    REFERENCES LEVELING(IDLEVEL)
    ON DELETE CASCADE
)Engine = InnoDB;