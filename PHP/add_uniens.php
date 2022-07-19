<?php
    include "CONFIG/databases.php";
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    if ($_SESSION['admin'] == false) {
        header('Location: index.php');
        exit();
    }
    define("PATH", "../FILES/");
    function removeAccent($chaine) {
        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
        return str_replace($search, $replace, $chaine);
    }
    // Gestion du formulaire d'ajout de l'unité d'enseignement
    if (isset($_POST['valider'])) {
        if (isset($_POST['ue']) && !empty($_POST['ue'])) {
            if (isset($_POST['prof']) && !empty($_POST['prof'])) {
                if (isset($_POST['discipline']) && !empty($_POST['discipline'])) {
                    if (isset($_POST['item']) && !empty($_POST['item'])) {
                        // Vérification des disciplines sélectionnés
                        foreach ($_POST["discipline"] as $discipline) {
                            $search = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE NOMDIS = ?");
                            $search->execute(array($discipline));
                            if ($search->rowCount() == 0) {
                                $error = $discipline . " n'est pas dans la base";
                            }
                        }
                        // Sélectionner le niveau saisi
                        $request_lvl = $pdo->prepare('SELECT * FROM LEVELING WHERE ITEMLEVEL = ?');
                        $request_lvl->execute(array($_POST['item']));
                        if ($request_lvl->rowCount() != 1) {
                            $error = "Erreur de niveau";
                        } 
                        // Si aucune erreur de niveau et de discipline
                        if (!isset($error)) {
                            $ue = removeAccent(ucwords(strtolower(trim(($_POST['ue'])))));
                            $p = ucwords(strtolower(trim(($_POST['prof']))));
                            // Si mauvaise saisie des inputs
                            if (strlen($ue) == 0 || strlen($p) == 0) {
                                $error = "La saisie du nom de la matière ou du professeur est incomplète";
                            } else {
                                // Vérification de la non présence dans la base de données
                                $search = $pdo->prepare("SELECT * FROM UNIENS WHERE NOMUE = ? AND NOMPROFUE = ?");
                                $search->execute(array($ue, $p));
                                if ($search->rowCount() == 0) {
                                    $lvl = $request_lvl->fetch();
                                    // Insertion de l'unité d'enseignement
                                    $request = $pdo->prepare("INSERT INTO UNIENS(NOMUE, NOMPROFUE, LEVELUE) VALUES (?, ?, ?)");
                                    $request->execute(array($ue, $p, $lvl['IDLEVEL']));
                                    // On sélectionne l'identifiant de l'unité d'enseignement
                                    $uniens = $pdo->prepare("SELECT IDUE FROM UNIENS WHERE NOMUE = ? AND NOMPROFUE = ?");
                                    $uniens->execute(array($ue, $p));
                                    $result_ue = $uniens->fetch();
                                    // On récupère toutes les disciplines sélectionnées
                                    $result_dis = array();
                                    foreach ($_POST["discipline"] as $discipline){ 
                                        $dis = $pdo->prepare("SELECT IDDIS FROM DISCIPLINE WHERE NOMDIS = ?");
                                        $dis->execute(array($discipline));
                                        $result = $dis->fetch();
                                        array_push($result_dis, $result['IDDIS']);
                                    }
                                    // Insertion des associations des disciplines avec les unités d'enseignement
                                    foreach ($result_dis as $dis) {
                                        $request = $pdo->prepare("INSERT INTO FONCTION(IDUEFON, IDDISFON) VALUES (?,?)");
                                        $request->execute(array($result_ue['IDUE'], $dis));
                                    }
                                    // Création du dossier
                                    $dir = PATH . $ue;
                                    if (!file_exists($dir)){
                                        if (mkdir($dir)) {
                                            $success = $ue . " est bien ajouté.";
                                        } else {
                                            $error =  "Le répertoire n'a pas pu être créé.";
                                        }
                                    } else{
                                        $error = $ue . " existe déjà";
                                    }
                                    $success = $ue . " est bien ajouté.";
                                } else {
                                    $error = $ue . " existe déjà.";
                                }
                            }
                        }
                    } else {
                        $error = "Veuillez sélectionner un niveau";
                    }
                } else {
                    $error = "Veuillez sélectionner une ou plusieurs catégories";
                }
            } else {    
                $error = "Le nom du professeur n'est pas saisie.";
            }
        } else {
            $error = "Le nom de l'UE n'est pas saisie.";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ajouter une UE</title>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/form.css"/>
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latéral -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('add_uniens') </script>
        <!-- Affichage du formulaire d'ajout de l'unité d'enseignement -->
        <div class="big_container">
            <?php
                $request = $pdo->prepare("SELECT * FROM DISCIPLINE");
                $request->execute(array()); 
                if ($request->rowCount() != 0) { ?>
                    <div class="container-form"> 
                        <div class="form">
                            <form action="add_uniens.php" method="POST">
                                <h2>Ajouter une UE </h2>
                                <!-- Affichage des erreurs et des succès -->
                                <?php 
                                    if (isset($error)) {
                                        echo '<div id="error">' . $error . "</div>";
                                    } else if (isset($success)) {
                                        echo '<div id="success">' . $success . "</div>";
                                    }
                                ?>
                                <!-- L'input pour saisir le nom de l'unité d'enseignement -->
                                <h3><label for="info_ue">Le nom de L'UE</label></h3>
                                <input type="text" id="info_ue" name="ue" placeholder="Nom de la matière" autocomplete="off">
                                <!-- L'input pour saisir le nom du gérant de l'unité d'enseignement -->
                                <h3><label for="info_prof">Le nom du gérant de l'UE </label></h3>
                                <input type="text" id="info_prof" name="prof" placeholder="Nom du professeur" autocomplete="off">
                                <!-- L'input pour y saisir toutes les disciplines de l'unité d'enseignement -->
                                <h3><label for="info_dis">Les disciplines de l'UE</label></h3>
                                <select id="info_dis" name="discipline[]" id="doc-select" size="10" multiple>
                                    <option disabled value="invalid">--Choisir la catégorie--</option> 
                                    <?php while ($r = $request->fetch()) {
                                            $value = strtolower(str_replace(' ', '_', $r['NOMDIS']));
                                            echo '<option value="'. $value .'">'. $r['NOMDIS'] .'</option>';
                                        } ?>
                                </select>
                                <!-- L'input pour y saisir l'unique niveau de l'unité d'enseignement -->
                                <h3><label for="info_lvl">Le niveau de l'UE</label></h3>
                                <select id="info_lvl" name="item" id="doc-select" size="10">
                                    <option disabled value="invalid">--Choisir le niveau--</option> 
                                    <?php 
                                        $level_request = $pdo->prepare('SELECT * FROM LEVELING');
                                        $level_request->execute(array());
                                        while ($lv = $level_request->fetch()) {
                                            $value = $lv['ITEMLEVEL'];
                                            echo '<option value="'. $value .'">'. $value .'</option>';
                                        } ?>
                                </select>
                                <!-- Le bouton de soumission du formulaire d'ajout de l'unité d'enseignement -->
                                <input type="submit" class="submit_button" name="valider" value="Ajouter l'UE">
                            </form>
                        </div>
                    </div>
            <?php } else { ?>
                <!-- Affichage du bloc d'erreur -->
                <div class="container-error"> 
                    <div class="not">
                        <h2>Impossible d'ajouter des unités d'enseignements. Ajoutez des disciplines pour pouvoir ajouter des fournisseurs.</h2>
                    </div>
                </div>
            <?php } ?>
        </div>
    </body>
</html>