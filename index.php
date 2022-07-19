<?php 
    // Page du formulaire de connexion
    include "PHP/CONFIG/databases.php";
    // Si le membre est connecté alors le redirection vers le site à lieu
    if (isset($_SESSION['id'])) {
        header("Location: PHP/");
    }
    // Vérification des entrées du formulaire de connexion
    if (isset($_POST['login'])) {
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            if (isset($_POST['identifiant']) && !empty($_POST['identifiant'])) {
                // Vérification de l'adresse mail entrée
                if (preg_match("#^[a-z]\.[a-z]@gmail\.com$#", $_POST['identifiant']) == 0) {
                    $id = $_POST["identifiant"];
                    $pass = $_POST["password"];
                    // Récupération des données correspondant à l'adresse mail et au mot de passe saisis
                    $search = $pdo->prepare("SELECT * FROM MEMBER WHERE EMAILUSER = ? AND PASSUSER = ?");
                    $search->execute(array($id, $pass));
                    // Problème de connexion 
                    if ($search->rowCount() != 1) {
                        $error = "Veuillez vous inscrire.";
                    } else {
                        // Dans le cas contraire on récupère les données du membre avec les valeurs SESSION
                        $s = $search->fetch();
                        if ($_POST['password'] == $s['PASSUSER']) {
                            $_SESSION['id'] = $s['EMAILUSER'];
                            $_SESSION['name'] = $s['NAMEUSER'];
                            $_SESSION['family'] = $s['FAMILYUSER'];
                            $_SESSION['user'] = intval($s['IDUSER']);
                            $_SESSION['admin'] = ($s['ADMINUSER'] == 0) ? false : true;
                            header('Location: PHP/index.php');
                            exit();
                        } else {   
                            $error = "Erreur de mot de passe";
                        }
                    }
                } else {
                    $error = "L'adresse mail est incorrecte";
                }
            } else {
                $error = "L'adresse mail n'est pas saisi";
            }
        } else {
            $error = "Le mot de passe n'est pas saisi";
        }
    }
?>
<!DOCTYPE html>
<head>
    <title>Page de connexion</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="CSS/form.css"/>
    <link rel="stylesheet" href="CSS/login.css"/>
</head>
<body>
    <!-- Formulaire de connexion -->
    <div id="login">
        <form action="index.php" method="POST">
            <h2>Se connecter sur BDD SCHOOL</h2>
            <!-- Affichage des erreurs -->
            <?php 
                if (isset($error)) {
                    echo '<div id="error">' . $error . "</div>";
                }
            ?>
            <!-- L'input pour l'adresse mail -->
            <h3><label for="id">Adresse mail</label></h3>
            <input type="text" id="id" name="identifiant" placeholder="adresse.mail@gmail.com" pattern="^[[a-z][\.]]{1,}[a-z]{3,}@gmail\.com$" required>
            <!-- L'input pour le mot de passe -->
            <h3><label for="pass">Mot de passe</label></h3>
            <input type="password" id="pass" name="password" title="Au moins 10 caractères composant de caractères majuscules, minuscules et numériques" pattern="[a-zA-Z0-9]{10,}" required>
            <!-- Le bouton de soumission -->
            <input type="submit" class="submit_button" name="login" value="valider">
        </form>
    </div>
</body>