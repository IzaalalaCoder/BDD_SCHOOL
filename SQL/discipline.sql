-- La table discipline qui contiendra toutes les disciplines 
-- Elle contient les deux éléments suivants 
-- IDDIS qui est la clé primaire de la discipline
-- NOMDIS qui est le nom de la discipline
CREATE TABLE DISCIPLINE (
    IDDIS INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    NOMDIS VARCHAR(100) NOT NULL
)ENGINE = InnoDB;