<?php 
    // Page du formulaire d'ajout des documents
    include "CONFIG/databases.php";
    // Si le membre n'est pas connecté est redigirée vers la page de formulaire de connexion
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    // Si le membre n'est pas un administrateur
    if ($_SESSION['admin'] == false) {
        header('Location: index.php');
        exit();
    }
    define("PATH",  "../FILES/");
    // Gestion du formulaire d'ajout d'un document
    if (isset($_POST['add_doc_valid'])) {
        if (isset($_FILES['fichier']) && !empty($_FILES['fichier'])) {
            if (isset($_POST['description']) && !empty($_POST['description'])) {
                if (isset($_POST['ue']) && !empty($_POST['ue'])) {
                    if (isset($_POST['type']) && !empty($_POST["type"])) {
                        // Recherche des informations reliés à l'unité d'enseignement associé au document
                        $search_uniens = $pdo->prepare('SELECT IDUE FROM UNIENS WHERE NOMUE = ?');
                        $search_uniens->execute(array($_POST['ue']));
                        // Si l'unité d'enseignement n'existe pas dans la base
                        if ($search_uniens->rowCount() == 0 || $search_uniens->rowCount() > 1) {
                            $error = "Erreur d'unités d'enseignement, veuillez retenter plus tard";
                        } else {
                            // Dans le cas contraire, on récupère toutes les informations du formulaire
                            $description = $_POST['description'];
                            $fonction = $_POST['type'];
                            $size = $_FILES['fichier']['size'];
                            $name = $_FILES['fichier']['name'];
                            $tmp_name = $_FILES['fichier']['tmp_name'];
                            $extension = strrchr($_FILES['fichier']['name'], ".");
                            $url = PATH . $_POST['ue'] . '/';
                            $uniens = $search_uniens->fetch();
                            // Insertion dans la base de données seulement dans le cas ou ce document n'existe pas
                            $exists = $pdo->prepare("SELECT * FROM DOCUMENT WHERE NOMDOC = ? AND TYPEDOC = ? AND TAILLEDOC = ? AND URLDOC = ? AND IDUEDOC = ?");
                            $exists->execute(array($name, $extension, $size, $url . $name, $uniens['IDUE']));
                            // Dans le cas ou le document n'existe pas, on l'ajoute dans la base et dans nos dossiers
                            if ($exists->rowCount() == 0) {
                                // Déplacement du document dans le bon dossier 
                                if (move_uploaded_file($tmp_name, $url . $name)) {
                                    $success = "Le document ". $name ." a bien été ajoutée.";
                                    $request = $pdo->prepare("INSERT INTO DOCUMENT(DESCDOC, FONCDOC, NOMDOC, TYPEDOC, TAILLEDOC, URLDOC, IDUEDOC) VALUES (?,?,?,?,?,?,?)");
                                    $request->execute(array($description, $fonction, $name, $extension, $size, $url . $name, $uniens['IDUE']));
                                } else {
                                    $error = "Une erreur est survenue lors de l'envoi du fichier.";
                                }                                
                            } else {
                                $error = "Le document ". $name ." existe déjà.";
                            }
                        }
                    } else {
                        $error = "Absence du type de document";
                    }
                } else {
                    $error = "Absence de l'UE";
                }
            } else {
                $error = "La description est vide";
            }
        } else {
            $error = "Erreur de document";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ajouter un document</title>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/form.css"/>
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latérale -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('add_doc') </script>
        <!-- Affichage du formulaire d'ajout d'un document -->
        <div class="big_container">
            <?php 
                $request = $pdo->prepare("SELECT * FROM UNIENS ORDER BY IDUE DESC");
                $request->execute(array()); 
                if ($request->rowCount() != 0) { ?>
                        <div class="container-form">
                            <div class="form">
                                <form action="add_document.php" method="POST" enctype="multipart/form-data">
                                    <h2>Ajouter un document </h2>
                                    <!-- Affichage des erreurs et des succès -->
                                    <?php 
                                        if (isset($error)) {
                                            echo '<div id="error">' . $error . "</div>";
                                        } else if (isset($success)) {
                                            echo '<div id="success">' . $success . "</div>";
                                        }
                                    ?>
                                    <!-- L'input de fichier -->
                                    <input type="hidden" name="MAX_FILE_SIZE" value="100000000000" />
                                    <h3><label for="info_file">Fichier</label></h3>
                                    <input type="file" id="info_file" autocomplete="off" id="file" name="fichier" value="Saisir le fichier" required>
                                    <!-- L'input pour la description du document -->
                                    <h3><label for="info_desc">La description du document</label></h3>
                                    <textarea name="description" id="info_desc" autocomplete="off" placeholder="Description du fichier" cols="30" rows="10" required></textarea>
                                    <!-- L'input pour choisir l'unité d'enseignement qui est titulaire du document -->
                                    <h3><label for="info_file_ue">L'unité d'enseignement associé au document</label></h3>
                                    <select name="ue" id="info_file_ue" size="10" required>
                                        <option disabled value="invalid">--Choisir l'unité d'enseignement--</option> 
                                        <?php
                                            while ($r = $request->fetch()) {
                                                echo '<option value="'. $r['NOMUE'] .'">'. $r['NOMUE'] .'</option>';
                                            } 
                                        ?>
                                    </select>
                                    <!-- L'input pour choisir la fonctionnalité principale du document -->
                                    <h3><label for="info_file_type">Le type de document</label></h3>
                                    <select name="type" id="info_file_type" size="10" required>
                                        <option disabled value="invalid">--Choisir le type du document--</option> 
                                        <option value="cours_magistraux">Cours Magistraux</option>
                                        <option value="travaux_pratique">Travaux pratiques</option>
                                        <option value="travaux_diriges">Travaux dirigées</option>
                                        <option value="projet">Projet</option>
                                        <option value="codes_sources">Codes sources</option>
                                        <option value="examen">Examen</option>
                                        <option value="exe">Executable</option>
                                    </select>
                                    <!-- L'input du bouton de soumission -->
                                    <input type="submit" class="submit_button" name="add_doc_valid" value="Ajouter le document"> 
                                </form>
                            </div>
                    </div>
                    <!-- Sinon affichage d'un bloc d'erreur -->
            <?php } else { ?>
                <div class="container-error">
                    <div class="not">
                        <h2>Impossible d'ajouter des documents. Ajoutez des unités d'enseignement pour pouvoir ajouter des documents.</h2>
                    </div>
                </div>
            <?php } ?>
        </div>
    </body>
</html>