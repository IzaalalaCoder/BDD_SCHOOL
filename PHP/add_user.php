<?php 
    // Page de formulaire d'ajout d'un utilisateur
    include "CONFIG/databases.php";
    // Si le membre n'est pas connecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    // Si le membre n'est pas un administrateur
    if ($_SESSION['admin'] == false) {
        header('Location: index.php');
        exit();
    }
    // Il s'agit d'une fonction qui génère un mot de passe
    function getPassAlea() {
        $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); 
        $combLen = strlen($comb) - 1; 
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $combLen);
            array_push($pass, $comb[$n]);
        }
        return implode($pass); 
    }
    // Gestion du formulaire d'ajout d'un utilisateur
    if (isset($_POST['valider'])) {
        if (isset($_POST['mail']) && !empty($_POST['mail'])) {
            if (isset($_POST['name']) && !empty($_POST['name'])) {
                if (isset($_POST['family']) && !empty($_POST['family'])) {
                    if (isset($_POST['administrator']) && !empty($_POST['administrator']) && ($_POST['administrator'] == "off" || $_POST['administrator'] == "on")) {
                        // Gestion de l'adresse mail de l'utilisateur
                        if (preg_match("#^[a-z]\.[a-z]@gmail\.com$#", $_POST['mail']) == 0) {
                            if (substr_count($_POST['mail'], "@gmail.com") == 1) {
                                $email = trim($_POST['mail']);
                                // Vérifions si l'adresse mail existe déjà dans la base de données
                                $insert_test_mail = $pdo->prepare("SELECT * FROM MEMBER WHERE EMAILUSER = ?");
                                $insert_test_mail->execute(array($email));
                                // Dans le cas ou la réponse est négative
                                if ($insert_test_mail->rowCount() == 0) {
                                    $name = htmlspecialchars(ucwords(strtolower(trim($_POST['name']))));
                                    $family = htmlspecialchars(strtoupper(trim($_POST['family'])));
                                    $admin = ($_POST['administrator'] == "off" ? 0 : 1);
                                    // Vérifions si le prénom et le nom de famille existe dans la base de données
                                    $insert_test = $pdo->prepare("SELECT * FROM MEMBER WHERE NAMEUSER = ? AND FAMILYUSER = ?");
                                    $insert_test->execute(array($name, $family));
                                    // Dans le cas ou le couple (prénom, le nom de famille) est inexistant dans la base de données
                                    if ($insert_test->rowCount() == 0) {
                                        $real_pass = getPassAlea();
                                        // On insère donc le nouvel utilisateur dans notre base
                                        $insert = $pdo->prepare("INSERT INTO MEMBER(NAMEUSER, FAMILYUSER, EMAILUSER, PASSUSER, ADMINUSER) VALUES (?,?,?,?,?)");
                                        $insert->execute(array($name, $family, $email, $real_pass, $admin));
                                        $exist =  $pdo->prepare("SELECT * FROM MEMBER WHERE NAMEUSER = ? AND FAMILYUSER = ? AND EMAILUSER = ? AND PASSUSER = ? AND ADMINUSER  = ?");
                                        $exist->execute(array($name, $family, $email, $real_pass, $admin));
                                        // Gestion de la réponse de notre page
                                        if ($exist->rowCount() == 1) {
                                            $success = "Insertion du membre réussi";
                                        } else {
                                            $error = "Insertion echouée du membre";
                                        }
                                    } else {
                                        $error = "Nom et prénom existe déjà dans la base";
                                    }
                                } else {
                                    $error = "L'adresse mail existe déjà";
                                }
                            } else {
                                $error = "L'adresse mail est incorrecte";
                            }
                        } else {
                            $error = "L'adresse mailddddd est incorrecte";
                        }
                    } else {
                        $error = "L'administrateur n'est pas saisi";
                    }
                    
                } else {
                    $error = "Erreur sur le nom de famille";
                }
            } else  {
                $error = "Erreur sur le prénom";
            }
        } else {
            $error = "Erreur sur l'adresse mail";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ajouter un utilisateur</title>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/form.css"/>
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latérale -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('add_user') </script>
        <!-- Affichage du formulaire d'ajout d'un utilisateur -->
        <div class="big_container">
            <div class="container-form">
                <div class="form">
                    <form action="add_user.php" method="POST">
                        <h2>Ajouter un utilisateur </h2>
                        <!-- Affichage des erreurs et des succés -->
                        <?php 
                            if (isset($error)) {
                                echo '<div id="error">' . $error . "</div>";
                            } else if (isset($success)) {
                                echo '<div id="success">' . $success . "</div>";
                            }
                        ?>
                        <!-- L'input pour l'adresse mail de l'utilisateur -->
                        <h3><label for="info_usermail">L'adresse mail de l'utilisateur</label></h3>
                        <input type="text" id="info_usermail" autocomplete="off" name="mail" placeholder="adresse.mail@gmail.com" pattern="^[[a-z][\.]]{1,}[a-z]{3,}@gmail\.com$" placeholder="Adresse mail" required />
                        <!-- L'input pour le prénom de l'utilisateur -->
                        <h3><label for="info_username">Le prénom de l'utilisateur</label></h3>
                        <input type="text" id="info_username" autocomplete="off" name="name" placeholder="Prénom" required />
                        <!-- L'input pour le nom de famille de l'utilisateur -->
                        <h3><label for="info_userfamily">Le nom de famille de l'utilisateur</label></h3>
                        <input type="text" id="info_userfamily" autocomplete="off" name="family" placeholder="Nom de famille" required />
                        <!-- Les boutons radio pour différencier un utiliseur simple ou un administrateur -->
                        <div class="admin">
                            <h4>Utilisateur administrateur ?</h4>
                            <div class="off">
                                <label for="off">UTILISATEUR</label>
                                <input type="radio" autocomplete="off" id="off" name="administrator" value="off">
                            </div>
                            <div class="on">
                                <label for="on">ADMINISTRATEUR</label>
                                <input type="radio" autocomplete="off" id="on" name="administrator" value="on"> 
                            </div>
                        </div>
                        <!-- L'input d'un bouton de soumission -->
                        <input type="submit" class="submit_button" name="valider" value="Ajouter le membre">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>