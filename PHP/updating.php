<?php
    // Page de modification des données 
    include "CONFIG/databases.php";
    // Si non connecté et si l'URL ne présente pas les identifiant de
    // discipline, unités d'enseignement, document, membres
    if (!isset($_SESSION['id']) || 
        ((!(isset($_GET['IDDIS'])) || empty($_GET['IDDIS'])) 
        && (!(isset($_GET['IDUE'])) || empty($_GET['IDUE'])) 
        && (!(isset($_GET['IDDOC'])) || empty($_GET['IDDOC'])) 
        && (!(isset($_GET['IDUSER'])) || empty($_GET['IDUSER'])))
        || isset($_GET['change_user']))  {
        header('Location: setting.php');
        exit();
    }
    define("PATH",  "../FILES/");
    // Fonction supprimant les accents et les remplacent par leur équivalent sans accents
    function removeAccent($chaine) {
        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
        return str_replace($search, $replace, $chaine);
    }
    // Si le membre est un administrateur
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
        // Gestion du formulaire de modification de la discipline
        if (isset($_GET['IDDIS']) && isset($_POST['modif_discipline'])) { 
            if (isset($_POST['name_discipline']) && !empty($_POST['name_discipline'])) {
                $name = strtolower(htmlspecialchars(str_replace(' ', '_', preg_replace("/ +/", " ", trim(removeAccent(str_replace('\'', '', $_POST['name_discipline'])))))));
                if (strlen($name) == 0) {
                    $error = "Le nouveau nom de la discipline est incomplet";
                } else {
                    $request = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE IDDIS = ?");
                    $request->execute(array($_GET['IDDIS']));
                    if ($request->rowCount() == 0 || $request->rowCount() > 1) {
                        $error = "Erreur de modification, veuillez essayer à nouveau";
                    } else {
                        $r = $request->fetch();
                        if (strcmp($r['NOMDIS'], $name) !== 0) {
                            $update = $pdo->prepare("UPDATE DISCIPLINE SET NOMDIS = ? WHERE IDDIS = ?");
                            $update->execute(array($name, $_GET['IDDIS']));
                            $success = "La discipline a été modifié";
                        } else {
                            $info = "Aucune modification n'a été faite";
                        }
                    }
                }
            } else {
                $error = "Le nom de la catégorie n'est pas saisie";
            }
        // Gestion du formulaire de modification de l'unité enseignement
        } else if (isset($_GET['IDUE']) && isset($_POST['modif_ues'])) { 
            if (isset($_POST['name_uniens']) && !empty($_POST['name_uniens'])) {
                if (isset($_POST['name_prof']) && !empty($_POST['name_prof'])) {
                    if (isset($_POST['on']) && !empty($_POST['on'])) {
                        if (isset($_POST['lvl']) && !empty($_POST['lvl'])) {
                            $search_lvl = $pdo->prepare("SELECT * FROM LEVELING WHERE ITEMLEVEL = ?");
                            $search_lvl->execute(array($_POST['lvl']));
                            if ($search_lvl->rowCount() == 1) {
                                $s_lvl = $search_lvl->fetch();
                                foreach ($_POST["on"] as $on) {
                                    $search = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE NOMDIS = ?");
                                    $search->execute(array($on));
                                    if ($search->rowCount() == 0) {
                                        $error = $on . " n'est pas dans la base";
                                    }
                                }
                                if (!isset($error)) {
                                    $u = ucwords(strtolower(trim(($_POST['name_uniens']))));
                                    $p = ucwords(strtolower(trim(($_POST['name_prof']))));
                                    if (strlen($u) == 0 || strlen($p) == 0) {
                                        $error = "La saisie du nouveau nom de l'UE ou du nouveau professeur est incomplète";
                                    } else {
                                        $search = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                        $search->execute(array($_GET['IDUE']));
                                        if ($search->rowCount() == 0 || $search->rowCount() > 1) {
                                            $error = "Erreur de modification, veuillez retenter plus tard";
                                        } else {
                                            $s = $search->fetch();
                                            $number_modif = 0;
                                            if (strcmp($s['NOMUE'], $u) !== 0) {
                                                rename(PATH . $s['NOMUE'], PATH . $u);
                                                $update = $pdo->prepare("UPDATE UNIENS SET NOMUE = ? WHERE IDUE = ?");
                                                $update->execute(array($u, $_GET['IDUE']));
                                                $number_modif += 1;
                                            }
                                            if (strcmp($s['NOMPROFUE'], $p) !== 0) {
                                                $update = $pdo->prepare("UPDATE UNIENS SET NOMPROFUE = ? WHERE IDUE = ?");
                                                $update->execute(array($p, $_GET['IDUE']));
                                                $number_modif += 1;
                                            }
                                            if (intval($s_lvl['IDLEVEL']) != intval($s['LEVELUE'])) {
                                                $update = $pdo->prepare('UPDATE UNIENS SET LEVELUE = ? WHERE IDUE = ?');
                                                $update->execute(array($s_lvl['IDLEVEL'], $_GET['IDUE']));
                                                $number_modif += 1;
                                            }
                                            $request = $pdo->prepare('SELECT NOMDIS FROM DISCIPLINE');
                                            $request->execute(array());
                                            $categorys_all = array();
                                            while ($r = $request->fetch()) {
                                                array_push($categorys_all, $r['NOMDIS']);
                                            }
                                            foreach($categorys_all as $cat) {
                                                $request_cat = $pdo->prepare("SELECT IDDIS FROM DISCIPLINE WHERE NOMDIS = ?");
                                                $request_cat->execute(array($cat));
                                                $r_cat = $request_cat->fetch();
                                                $here = $pdo->prepare('SELECT * FROM FONCTION WHERE IDUEFON = ? AND IDDISFON = ?');
                                                $here->execute(array($_GET['IDUE'], $r_cat['IDDIS']));
                                                if (in_array($cat, $_POST['on'])) {
                                                    if ($here->rowCount() == 0) {
                                                        $update = $pdo->prepare("INSERT INTO FONCTION(IDUEFON, IDDISFON) VALUES (?,?)");
                                                        $update->execute(array($_GET['IDUE'], $r_cat['IDDIS']));
                                                        $number_modif += 1;
                                                    } 
                                                } else {
                                                    if ($here->rowCount() == 1) {
                                                        $delete = $pdo->prepare("DELETE FROM FONCTION WHERE IDUEFON = ? AND IDDISFON = ?");
                                                        $delete->execute(array($_GET['IDUE'], $r_cat['IDDIS']));
                                                        $number_modif += 1;
                                                    } 
                                                }
                                            }
                                            if ($number_modif > 0) {
                                                $success = "Des modifications ont éte faite";
                                            } else {
                                                $info = "Aucune modification n'a été faite";
                                            }
                                        }
                                    }
                                }
                            } else {
                                $error = "Le niveau est inconnu de la base";
                            }
                        } else {
                            $error = "Le niveau n'est pas saisie";
                        }
                    } else {
                        $error = "Les nouvelles disciplines ne sont pas saisies.";
                    }
                } else {
                    $error = "Le nouveau nom du professeur n'est pas saisie.";
                }
            } else {
                $error = "Le nouveau nom de l'UE n'est pas saisie";
            }
        // Gestion du formulaire de modification du document
        } else if (isset($_GET['IDDOC']) && isset($_POST['modif_doc'])) {
            if (isset($_POST['description']) && !empty($_POST['description'])) {
                if (isset($_POST['uniens_doc']) && !empty($_POST['uniens_doc'])) {
                    if (isset($_POST['type']) && !empty($_POST['type'])) {
                        $recup = $pdo->prepare('SELECT * FROM DOCUMENT WHERE IDDOC = ?');
                        $recup->execute(array($_GET['IDDOC']));
                        if ($recup->rowCount() == 0 || $recup->rowCount() > 1) {
                            $error = "Erreur de document veuillez retenter la modification du fichier à l'avenir";
                        }
                        if (!isset($error)) {
                            $rp = $recup->fetch();
                            $number_modif = 0;
                            $number_error = 0;
                            if (isset($_FILES['fichier']) && !empty($_FILES['fichier'])) {
                                $extension = strrchr($_FILES['fichier']['name'], ".");
                                $tmp_name = $_FILES['fichier']['tmp_name'];
                                $size = $_FILES['fichier']['size'];
                                $name = $_FILES['fichier']['name'];
                                $search_id = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                $search_id->execute(array($_POST['uniens_doc']));
                                if ($search_id->rowCount() == 1) {
                                    $si = $search_id->fetch();
                                    $url = PATH . $si["NOMUE"] . '/';
                                    if ($size != 0) {
                                        if ($extension != $rp['TYPEDOC'] || $size != $rp['TAILLEDOC'] || strcmp($rp['NOMDOC'], $name) != 0) {
                                            if (file_exists($rp['URLDOC'])) {
                                                unlink($rp['URLDOC']);
                                                if (move_uploaded_file($tmp_name, $url . $name)) {    
                                                    $update_doc = $pdo->prepare('UPDATE DOCUMENT SET NOMDOC = ? AND TYPEDOC = ? AND TAILLEDOC = ? AND URLDOC = ? WHERE IDDOC = ?');
                                                    $update_doc->execute(array($name, $extension, $size, $url . $name, $_GET['IDDOC']));
                                                    $number_modif += 1;
                                                } else {
                                                    $number_error = 1;
                                                }
                                            } else {
                                                $number_error = 1;
                                            }
                                        }
                                    }
                                } else {
                                    $number_error = 2;
                                }
                            }
                            if ($_POST['uniens_doc'] != $rp['IDUEDOC']) {
                                if (file_exists($rp['URLDOC'])) {
                                    $name_uniens = $pdo->prepare('SELECT NOMUE FROM UNIENS WHERE IDUE = ?');
                                    $name_uniens->execute(array($_POST['uniens_doc']));
                                    $nu_name = $name_uniens->fetch();
                                    $update_ue_of_doc = $pdo->prepare("UPDATE DOCUMENT SET URLDOC = ? WHERE IDDOC = ?");
                                    $update_ue_of_doc->execute(array(PATH . $nu_name['NOMUE'] . '/' . $rp['NOMDOC'], $_GET['IDDOC']));
                                    rename($rp['URLDOC'], PATH . $nu_name['NOMUE'] . '/' . $rp['NOMDOC']);
                                    $update_id_ue_of_doc = $pdo->prepare("UPDATE DOCUMENT SET IDUEDOC = ? WHERE IDDOC = ?");
                                    $update_id_ue_of_doc->execute(array(intval($_POST['uniens_doc']), $_GET['IDDOC']));
                                    $number_modif += 1;
                                } else {
                                    $number_error = 1;
                                }
                            } 
                            $type = strval($_POST['type']);
                            if (strcmp($type, $rp['FONCDOC']) !== 0) {
                                $update_type = $pdo->prepare("UPDATE DOCUMENT SET FONCDOC = ? WHERE IDDOC = ?");
                                $update_type->execute(array($type, $_GET['IDDOC']));
                                $number_modif += 1;
                            }  
                            if (strcmp($rp['DESCDOC'], $_POST['description']) != 0) {
                                $update_type = $pdo->prepare("UPDATE DOCUMENT SET DESCDOC = ? WHERE IDDOC = ?");
                                $update_type->execute(array($_POST['description'], $_GET['IDDOC']));
                                $number_modif += 1;
                            } 
                            if ($number_modif > 0) {
                                $success = "Des modifications ont éte faite";
                            } else {
                                $info = "Aucune modification n'a été faite";
                            } 
                        }
                    } else {
                        $error = "Le nouveau type de document n'est pas sélectionné";
                    }
                } else {
                    $error = "Le nouveau UNIENS n'est pas sélectionné";
                }
            } else {
                $error = "La nouvelle description n'est pas saisi";
            }
        // Gestion du formulaire de modification du membre
        } else if (isset($_GET['IDUSER']) && isset($_POST['modif_user'])) {
            if (isset($_POST['family']) && !empty($_POST['family'])) { 
                if (isset($_POST['nameu']) && !empty($_POST['nameu'])) {
                    if (isset($_POST['email']) && !empty($_POST['email'])) {
                        $request = $pdo->prepare('SELECT * FROM MEMBER WHERE IDUSER = ?');
                        $request->execute(array($_GET['IDUSER']));
                        if ($request->rowCount() != 1) {
                            header('Location: setting.php');
                            exit();
                        } else {
                            $number_modif = 0;
                            $r = $request->fetch();
                            if (strcmp($r['NAMEUSER'], trim($_POST['nameu'])) !== 0) {
                                $name = htmlspecialchars(ucwords(strtolower(trim($_POST['nameu']))));
                                $insert_name = $pdo->prepare("UPDATE MEMBER SET NAMEUSER = ? WHERE IDUSER = ?");
                                $insert_name->execute(array($name, $_GET['IDUSER']));
                                $number_modif += 1;
                            }
                            if (strcmp($r['FAMILYUSER'], trim($_POST['family'])) !== 0) {
                                $family = htmlspecialchars(strtoupper(trim($_POST['family'])));
                                $insert_family = $pdo->prepare("UPDATE MEMBER SET FAMILYUSER = ? WHERE IDUSER = ?");
                                $insert_family->execute(array($family, $_GET['IDUSER']));
                                $number_modif += 1;
                            }
                            if (strcmp($r['EMAILUSER'], trim($_POST['email'])) !== 0) {
                                if (preg_match("#^[a-z]\.[a-z]@gmail\.com$#", $_POST['email']) == 0 && substr_count($_POST['email'], "@gmail.com") == 1) {
                                    $email = trim($_POST['email']);
                                    $insert_mail = $pdo->prepare("UPDATE MEMBER SET EMAILUSER = ? WHERE IDUSER = ?");
                                    $insert_mail->execute(array($email, $_GET['IDUSER']));
                                    $number_modif += 1;
                                }
                            }
                            if (isset($_POST['administrator']) && !empty($_POST['administrator'])) {
                                $administrator_value = ($_POST['administrator'] == 'on' ? 1 : 0);  
                                if ($r['ADMINUSER'] != $administrator_value) {
                                    if ($r['ADMINUSER'] == 1) {
                                        $verif = $pdo->prepare("SELECT * FROM MEMBER WHERE ADMINUSER = 1");
                                        $verif->execute(array());
                                        if ($verif->rowCount() > 1) {
                                            $insert_admin = $pdo->prepare("UPDATE MEMBER SET ADMINUSER = ? WHERE IDUSER = ?");
                                            $insert_admin->execute(array($administrator_value, $_GET['IDUSER']));
                                            $number_modif += 1;
                                        }
                                    } else {
                                        $insert_admin = $pdo->prepare("UPDATE MEMBER SET ADMINUSER = ? WHERE IDUSER = ?");
                                        $insert_admin->execute(array($administrator_value, $_GET['IDUSER']));
                                        $number_modif += 1;
                                    }
                                }
                            }
                            if ($number_modif > 0) {
                                $success = "Des modifications ont éte faite";
                            } else {
                                $info = "Aucune modification n'a été faite";
                            } 
                        }
                    } else {
                        $error = "La nouvelle adresse mail n'est pas saisi";
                    }
                } else {
                    $error = "Le nouveau prénom n'est pas saisi";
                }
            } else {
                $error = "Le nouveau nom de famille n'est pas saisi";
            }
        } 
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Paramètres</title> 
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/form.css">
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latéral -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('setting') </script>
        <?php 
        // Si le membre est un administrateur
        if (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
            // Formulaire de modification de la discipline
            if (isset($_GET['IDDIS'])) {
                $request = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE IDDIS = ?");
                $request->execute(array($_GET['IDDIS']));
                if ($request->rowCount() == 0 || $request->rowCount() > 1) {
                    header('Location: setting.php');
                    exit();
                } else { 
                    $r = $request->fetch(); ?> 
                    <div class="big_container">
                        <div class="param">
                            <div class="form">
                                <form action="updating.php?IDDIS=<?php echo $_GET['IDDIS'];?>" method="POST">
                                    <h2> MODIFIER <?php echo $r['NOMDIS']; ?></h2>
                                    <!-- Affichage des erreurs, et des succès -->
                                    <?php 
                                        if (isset($error)) {
                                            echo '<div id="error">' . $error . "</div>";
                                        } else if (isset($success)) {
                                            echo '<div id="success">' . $success . "</div>";
                                        } else if (isset($info)) {
                                            echo '<div id="info">' . $info . "</div>";
                                        }
                                    ?>
                                    <!-- L'input du nom de la discipline -->
                                    <h3><label for="sett_cat_name">Nom de la discipline</label></h3>
                                    <input required autocomplete="off" id="sett_cat_name" type="text" name="name_discipline" value="<?php echo $r["NOMDIS"]; ?>" />
                                    <!-- Le bouton de soumission pour le formulaire de modification de discipline -->
                                    <input type="submit" class="submit_button" value="Modifier" name="modif_discipline" />
                                </form>
                            </div>
                        </div>
                    </div>
                <?php }
            // Formulaire de modification de l'unité d'enseignement
            } else if (isset($_GET['IDUE'])) {
                $request = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                $request->execute(array($_GET['IDUE']));
                if ($request->rowCount() == 0 || $request->rowCount() > 1) {
                    header('Location: setting.php');
                    exit();
                } else { 
                    $r = $request->fetch(); ?> 
                    <div class="big_container">
                        <div class="param">
                            <div class="form">
                                <form action="updating.php?IDUE=<?php echo $_GET['IDUE'];?>" method="POST">
                                    <h2> MODIFIER <?php echo $r['NOMUE']; ?></h2>
                                    <!-- Affichage des erreurs et des succès -->
                                    <?php 
                                        if (isset($error)) {
                                            echo '<div id="error">' . $error . "</div>";
                                        } else if (isset($success)) {
                                            echo '<div id="success">' . $success . "</div>";
                                        } else if (isset($info)) {
                                            echo '<div id="info">' . $info . "</div>";
                                        }
                                    ?>
                                    <h3><label for="sett_ue_name">Nom de l'unité d'enseignement</label></h3>
                                    <input required autocomplete="off" id="sett_ue_name" type="text" name="name_uniens" value="<?php echo $r["NOMUE"]; ?>" />
                                    <!-- L'input du nom du gérant du professeur -->
                                    <h3><label for="sett_ue_prof_name">Nom du professeur</label></h3>
                                    <input required autocomplete="off" id="sett_ue_prof_name" type="text" name="name_prof" value="<?php echo $r["NOMPROFUE"]; ?>" />
                                    <div>
                                        <!-- L'input checkbox pour les choix des disciplines -->
                                        <h3>Ses différentes disciplines </h3>
                                        <div class="categorys">
                                            <?php 
                                                $request = $pdo->prepare('SELECT NOMDIS FROM DISCIPLINE ORDER BY NOMDIS ASC');
                                                $request->execute(array());
                                                if ($request->rowCount() == 0) {
                                                    echo '<p class="empty"> Aucune catégorie. </p>';
                                                } else {
                                                    $categorys_all = array();
                                                    while ($r = $request->fetch()) {
                                                        array_push($categorys_all, $r['NOMDIS']);
                                                    }
                                                    $sub = array();
                                                    $sub_request = $pdo->prepare("SELECT NOMDIS FROM DISCIPLINE WHERE IDDIS IN (SELECT IDDISFON FROM FONCTION WHERE IDUEFON = ?)");
                                                    $sub_request->execute(array($_GET['IDUE']));
                                                    while ($s = $sub_request->fetch()) {
                                                        array_push($sub, $s['NOMDIS']);
                                                    }
                                                    foreach ($categorys_all as $cat_name) {
                                                        if (in_array($cat_name, $sub)) {
                                                            echo '<div class="cat-item check"><input type="checkbox" checked name="on[]" value="'. $cat_name .'"id="'. $cat_name .'">';
                                                            echo '<label for="'. $cat_name .'">'. $cat_name .'</label></div>';
                                                        } else {
                                                            echo '<div class="cat-item uncheck"><input type="checkbox" name="on[]" value="'. $cat_name .'"id="'. $cat_name .'">';
                                                            echo '<label for="'. $cat_name .'">'. $cat_name .'</label></div>';
                                                        }
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <div>
                                        <h3>Son niveau </h3>
                                        <div class="levels wrap">
                                            <!-- L'input de boutons radio pour le choix du niveau -->
                                        <?php 
                                            $request = $pdo->prepare('SELECT ITEMLEVEL FROM LEVELING ORDER BY IDLEVEL ASC');
                                            $request->execute(array());
                                            if ($request->rowCount() == 0) {
                                                echo '<p class="empty"> Aucun niveau. </p>';
                                            } else {
                                                $level_all = array();
                                                while ($r = $request->fetch()) {
                                                    array_push($level_all, $r['ITEMLEVEL']);
                                                }
                                                for ($i = 0; $i < count($level_all); $i++) {
                                                    $search_in_lvl = $pdo->prepare('SELECT * FROM UNIENS WHERE LEVELUE = ? AND IDUE = ?');
                                                    $search_in_lvl->execute(array($i + 1, $_GET['IDUE']));
                                                    if ($search_in_lvl->rowCount() > 0) {
                                                        echo '<div class="lvl-item"><input type="radio" class="radiolvl" checked name="lvl" value="'. $level_all[$i] .'"id="'. $level_all[$i] .'">';
                                                        echo '<label class="lvls" for="'. $level_all[$i] .'">'. $level_all[$i] .'</label></div>';
                                                    } else {
                                                        echo '<div class="lvl-item"><input type="radio" class="radiolvl" name="lvl" value="'. $level_all[$i] .'"id="'. $level_all[$i] .'">';
                                                        echo '<label class="lvls" for="'. $level_all[$i] .'">'. $level_all[$i] .'</label></div>';
                                                    }
                                                }
                                            }
                                        ?>
                                        </div>
                                    </div>
                                    <!-- Le bouton de soumission pour le formulaire de modification de l'unité d'enseignement -->
                                    <input type="submit" class="submit_button" value="Modifier" name="modif_ues" />
                                </form>
                            </div>
                        </div>
                    </div>
                <?php }
            // Formulaire de modification du document
            } else if (isset($_GET['IDDOC'])) { 
                $request = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDDOC = ?");
                $request->execute(array($_GET['IDDOC']));
                if ($request->rowCount() == 0 || $request->rowCount() > 1) {
                    header('Location: setting.php');
                    exit();
                } else { 
                    $r = $request->fetch();?>
                    <div class="big_container">
                        <div class="param">
                            <div class="form">
                                <form action="updating.php?IDDOC=<?php echo $_GET['IDDOC'];?>" method="POST" enctype="multipart/form-data">
                                    <h2> MODIFIER <?php echo $r['NOMDOC']; ?></h2>
                                    <!-- Affichage des erreurs, succès, et des informations -->
                                    <?php 
                                        if (isset($error)) {
                                            echo '<div id="error">' . $error . "</div>";
                                        } else if (isset($success)) {
                                            echo '<div id="success">' . $success . "</div>";
                                        } else if (isset($info)) {
                                            echo '<div id="info">' . $info . "</div>";
                                        }
                                        if (isset($number_error)) {
                                            if ($number_error == 1) { ?>
                                                <script>alert('Une erreur est survenue lors de l\'envoi du fichier')</script>
                                        <?php }
                                        if ($number_error == 2) { ?>
                                            <script>alert('Erreur de UNIENS, veuillez retenter')</script>
                                        <?php } } 
                                    ?>
                                    <!-- L'input de fichier  -->
                                    <input type="hidden" name="MAX_FILE_SIZE" value="100000000000" />
                                    <h3><label for="file">Le fichier</label></h3>
                                    <input type="file" autocomplete="off" id="file" name="fichier"> 
                                    <!-- L'input pour la description du document -->
                                    <h3><label for="desc">La description du document</label></h3>
                                    <textarea required name="description" id="desc" autocomplete="off" placeholder="Description du fichier" cols="30" rows="10"><?php echo $r['DESCDOC']; ?></textarea>
                                    <!-- L'input pour sélectionner l'unité d'enseignement -->
                                    <h3><label for="ue_doc">L'unité d'enseignement du document</label></h3>
                                    <select required name="uniens_doc" id="ue_doc" size="15">
                                        <?php
                                            $uniens_list = $pdo->prepare("SELECT * FROM UNIENS");
                                            $uniens_list->execute(array());
                                            while ($u = $uniens_list->fetch()) {
                                                if ($r['IDUEDOC'] == $u['IDUE']) {
                                                    echo '<option selected value="'. $u['IDUE'] .'">'. $u['NOMUE'] .'</option>';
                                                } else {
                                                    echo '<option value="'. $u['IDUE'] .'">'. $u['NOMUE'] .'</option>';
                                                }
                                            } 
                                        ?>
                                    </select>
                                    <!-- L'input pour sélectionner la fonctionnalité principale du document -->
                                    <h3><label for="file_type">Le type de document</label></h3>
                                    <select required name="type" id="file_type" size="15">
                                        <option <?php if (strcmp($r['FONCDOC'], "cours_magistraux") == 0) { echo "selected"; } ?> value="cours_magistraux">Cours Magistraux</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "travaux_pratique") == 0) { echo "selected"; } ?> value="travaux_pratique">Travaux pratiques</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "travaux_diriges") == 0) { echo "selected"; } ?> value="travaux_diriges">Travaux dirigées</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "projet") == 0) { echo "selected"; } ?> value="projet">Projet</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "codes_sources") == 0) { echo "selected"; } ?> value="codes_sources">Codes sources</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "examen") == 0) { echo "selected"; } ?> value="examen">Examen</option>
                                        <option <?php if (strcmp($r['FONCDOC'], "exe") == 0) { echo "selected"; } ?> value="exe">Executable</option>
                                    </select>
                                    <!-- Le bouton du soumission du formulaire de la modification du document -->
                                    <input type="submit" class="submit_button" value="Modifier" name="modif_doc" /> 
                                </form>
                            </div>
                        </div>
                    </div>
            <?php }
            // Formulaire de modification du membre
            } else if ($_GET['IDUSER']) {
                $request = $pdo->prepare("SELECT * FROM MEMBER WHERE IDUSER = ?");
                $request->execute(array($_GET['IDUSER']));
                if ($request->rowCount() == 0 || $request->rowCount() > 1) {
                    header('Location: setting.php');
                    exit();
                } else { 
                    $r = $request->fetch(); ?> 
                    <div class="big_container">
                        <div class="param">
                            <div class="form">
                                <form action="updating.php?IDUSER=<?php echo $_GET['IDUSER'];?>" method="POST">
                                    <h2> MODIFIER <?php echo $r['NAMEUSER'] . ' ' . $r['FAMILYUSER']; ?></h2> 
                                    <!-- Affichage des informations d'erreurs, de succès et d'informations -->
                                    <?php 
                                        if (isset($error)) {
                                            echo '<div id="error">' . $error . "</div>";
                                        } else if (isset($success)) {
                                            echo '<div id="success">' . $success . "</div>";
                                        } else if (isset($info)) {
                                            echo '<div id="info">' . $info . "</div>";
                                        }
                                    ?>
                                    <!-- L'input pour le prénom de l'utilisateur -->
                                    <h3><label for="user_n">Prénom de l'utilisateur</label></h3>
                                    <input required autocomplete="off" id="user_n" type="text" name="nameu" value="<?= $r["NAMEUSER"]; ?>" />
                                    <!-- L'input pour le nom de famille de l'utilisateur -->
                                    <h3><label for="user_f">Nom de l'utilisateur</label></h3>
                                    <input required autocomplete="off" id="user_f" type="text" name="family" value="<?= $r["FAMILYUSER"]; ?>" />
                                    <!-- L'input pour y saisir l'adresse mail de l'utilisateur -->
                                    <h3><label for="user_e">Adresse mail de l'utilisateur</label></h3>
                                    <input required autocomplete="off" id="user_e" type="text" name="email" value="<?= $r["EMAILUSER"]; ?>" />
                                    <!-- Le changement du mot de passe de façon aléatoire -->
                                    <div class="pass">
                                        <h3>Mot de passe : <span><?php echo $r['PASSUSER']; ?></span><a id="pass" href="CONFIG/change.php?change=on&userid=<?php echo $_GET['IDUSER'];?>">Générer un nouveau mot de passe</a></h3>
                                    </div>
                                    <!-- L'input pour le choix du type de membre -->
                                    <div class="admin">
                                        <h4>Utilisateur administrateur ?</h4>
                                        <div class="off">
                                            <label for="off">UTILISATEUR</label>
                                            <input <?php if ($r['ADMINUSER'] == 0) { echo "checked"; } ?> type="radio" autocomplete="off" id="off" name="administrator" value="off">
                                        </div>
                                        <div class="on">
                                            <label for="on">ADMINISTRATEUR</label>
                                            <input <?php if ($r['ADMINUSER'] == 1) { echo "checked"; } ?> type="radio" autocomplete="off" id="on" name="administrator" value="on"> 
                                        </div>
                                    </div>
                                    <!-- Le bouton de soumission pour le formulaire de modification de l'utilisateur -->
                                    <input type="submit" class="submit_button" value="Modifier" name="modif_user" />
                                </form>
                            </div>
                        </div>
                    </div>
                <?php }
            }
        } ?>
    </body>
</html>