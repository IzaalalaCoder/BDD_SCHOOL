<?php
include "databases.php";
// Il s'agit d'une page de modification des données en fonction du formulaire de modification
// Dans le cas ou l'on bien cliquer sur le bouton de soumission et à l'identifiant de l'utilisateur
if (isset($_GET['change_user']) && $_GET['change_user'] == true && isset($_GET['IDUSER']) && !empty($_GET['IDUSER'])) {
    // On vérifie toutes les entrées des formulaires
    if (isset($_POST['name']) && !empty($_POST['name'])) {
        if (isset($_POST['family']) && !empty($_POST['family'])) { 
            if (isset($_POST['email']) && !empty($_POST['email'])) {
                // On récupère les informations de cet utilisateur
                $request = $pdo->prepare('SELECT * FROM MEMBER WHERE IDUSER = ?');
                $request->execute(array($_GET['IDUSER']));
                // Vérification de l'existence de cet utilisateur
                if ($request->rowCount() != 1) {
                    header('Location: ../setting.php');
                    exit();
                } else {
                    $number_modif = 0;
                    $r = $request->fetch();
                    // Changement du prénom
                    if (strcmp($r['NAMEUSER'], trim($_POST['name'])) !== 0) {
                        $name = htmlspecialchars(ucwords(strtolower(trim($_POST['name']))));
                        $insert_name = $pdo->prepare("UPDATE MEMBER SET NAMEUSER = ? WHERE IDUSER = ?");
                        $insert_name->execute(array($name, $_GET['IDUSER']));
                        $number_modif += 1;
                    }
                    // Changement du nom de famille
                    if (strcmp($r['FAMILYUSER'], trim($_POST['family'])) !== 0) {
                        $family = htmlspecialchars(strtoupper(trim($_POST['family'])));
                        $insert_family = $pdo->prepare("UPDATE MEMBER SET FAMILYUSER = ? WHERE IDUSER = ?");
                        $insert_family->execute(array($family, $_GET['IDUSER']));
                        $number_modif += 1;
                    }
                    // Changement de l'adresse mail
                    if (strcmp($r['EMAILUSER'], trim($_POST['email'])) !== 0) {
                        if (preg_match("#^[a-z]\.[a-z]@gmail\.com$#", $_POST['email']) == 0 && substr_count($_POST['email'], "@gmail.com") == 1) {
                            $email = trim($_POST['email']);
                            $insert_mail = $pdo->prepare("UPDATE MEMBER SET EMAILUSER = ? WHERE IDUSER = ?");
                            $insert_mail->execute(array($email, $_GET['IDUSER']));
                            $number_modif += 1;
                        }
                    }
                    // Changement du mot de passe
                    if (isset($_POST['add_pass']) && isset($_POST['rem_pass']) && isset($_POST['confirm_add_pass'])) {
                        if (!empty($_POST['add_pass']) && !empty($_POST['rem_pass']) && !empty($_POST['confirm_add_pass'])) {
                            if (preg_match("#^[[:alnum:]]{10,}$#", $_POST['rem_pass'])) {
                                $search = $pdo->prepare("SELECT * FROM MEMBER WHERE IDUSER = ? AND PASSUSER = ?");
                                $search->execute(array($_GET['IDUSER'], $_POST['rem_pass']));
                                if ($search->rowCount() == 1) {
                                    if (preg_match("#^[[:alnum:]]{10,}$#", $_POST['add_pass'])) {
                                        if (preg_match("#^[[:alnum:]]{10,}$#", $_POST['confirm_add_pass'])) {
                                            if ($_POST['add_pass'] != $_POST['rem_pass']) {
                                                if ($_POST['add_pass'] == $_POST['confirm_add_pass']) {
                                                    $update_member_only = $pdo->prepare('UPDATE MEMBER SET PASSUSER = ? WHERE IDUSER = ?');
                                                    $update_member_only->execute(array($_POST['add_pass'], $_GET['IDUSER']));
                                                    $number_modif += 1;
                                                } else {
                                                    $error = "Le nouveau mot de passe et la confirmation ne sont pas identiques.";
                                                    header('Location: ../setting.php?msgserr='. $error);
                                                    exit();
                                                }
                                            } else {
                                                $error = "L'ancien mot de passe et le nouveau mot de passe sont identiques.";
                                                header('Location: ../setting.php?msgserr='. $error);
                                                exit();
                                            }
                                        } else {
                                            $error = "Le mot de passe de reconfirmation ne respecte pas les conditions";
                                            header('Location: ../setting.php?msgserr='. $error);
                                            exit();
                                        }
                                    } else {
                                        $error = "Le nouveau mot de passe ne respecte pas les conditions";
                                        header('Location: ../setting.php?msgserr='. $error);
                                        exit();
                                    }
                                } else {
                                    $error = "L'ancien mot de passe est incorrect. Veuillez contacter un administrateur";
                                    header('Location: ../setting.php?msgserr='. $error);
                                    exit();
                                }
                            } else {
                                $error = "L'ancien mot de passe ne respecte pas les conditions";
                                header('Location: ../setting.php?msgserr='. $error);
                                exit();
                            }
                        }
                    }
                    if ($number_modif > 0) {
                        header('Location: ../setting.php?msgsucc=Des modifications ont éte faite');
                        exit();
                    } else {
                        header("Location: ../setting.php?msginf=Aucune modification n'a été faite");
                        exit();
                    }
                }
            } else {
                $error = "La nouvelle adresse mail n'est pas saisi";
                header('Location: ../setting.php?msgserr='. $error);
                exit();
            }
        } else {
            $error = "Le nouveau nom de famille n'est pas saisi";
            header('Location: ../setting.php?msgserr='. $error);
            exit();
        }
    } else {
        $error = "Le nouveau prénom n'est pas saisi";
        header('Location: ../setting.php?msgserr='. $error);
        exit();
    } 
}