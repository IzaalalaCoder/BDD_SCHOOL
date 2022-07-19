-- Ici la table MEMBER 
-- qui nous sert a contenir les informations de chaque utilisateur de la BDD UNIENS
-- la table contient 
-- IDUSER qui est la clé primaire du membre
-- NAMEUSER qui est le nom du membre
-- FAMILYUSER qui contient le nom de famille de l'utilisateur
-- EMAILUSER stockera ici l'adresse mail de l'utilisateur
-- PASSUSER qui stockera le mot de passe
-- ADMINUSER une valeur entière qui dit si l'utilisateur est un administrateur ou pas
CREATE TABLE MEMBER (
    IDUSER INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    NAMEUSER VARCHAR(100) NOT NULL,
    FAMILYUSER VARCHAR(100) NOT NULL,
    EMAILUSER VARCHAR(100) NOT NULL,
    PASSUSER VARCHAR(255) NOT NULL,
    ADMINUSER INT NOT NULL DEFAULT 0
)Engine = InnoDB;


-- J'insère la première ligne pour pouvoir accès à la partie administrateur du site
INSERT INTO MEMBER(NAMEUSER, FAMILYUSER, EMAILUSER, PASSUSER, ADMINUSER) 
VALUES ("Rouen", "Universite", "univrouen@univ-rouen.com", "UNIVREN76130", 1);