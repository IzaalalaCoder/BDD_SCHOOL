-- La table DOCUMENT 
-- stockera tous les documents 
-- IDDOC qui est la clé primaire du document
-- DESCDOC qui contiendra la description du document
-- FONCDOC qui est la fonction du document (codes_sources, td, tp, examen ou autres) il est unique
-- NOMDOC qui est le nom du document 
-- TYPEDOC qui est l'extension du document
-- TAILLEDOC qui stockera la taille du document
-- URLDOC qui contiendra le lien du document
-- IDUEDOC qui est la clé étrangère de l'unité d'enseignement associé au document
CREATE TABLE DOCUMENT (
    IDDOC INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    DESCDOC VARCHAR(500) NOT NULL,
    FONCDOC VARCHAR(100) NOT NULL,
    NOMDOC VARCHAR(255) NOT NULL,
    TYPEDOC VARCHAR(100) NOT NULL,
    TAILLEDOC INT NOT NULL,
    URLDOC VARCHAR(255) NOT NULL,
    IDUEDOC INT NOT NULL,
    CONSTRAINT fk_document_ue
    FOREIGN KEY (IDUEDOC)
    REFERENCES UNIENS(IDUE)
    ON DELETE CASCADE
)Engine = InnoDB;