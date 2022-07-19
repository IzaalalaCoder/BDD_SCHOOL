-- La table LEVELING contient toutes les informations des niveaux en faculté
-- IDLEVEL le numéro unique du niveau 
-- ITEMLEVEL le nom du niveau 
CREATE TABLE LEVELING (
    IDLEVEL INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ITEMLEVEL VARCHAR(20) NOT NULL
)Engine = InnoDB;

-- Insertions uniques (Aucune insertion dans cette table lors de l'utilisation du site)
-- Le site ne fera que l'utiliser
INSERT INTO LEVELING(ITEMLEVEL) VALUES 
("L1"),
("L2"), 
("L3"),
("M1"),
("M2"),
("INC");