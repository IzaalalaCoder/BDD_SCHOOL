<?php 
    // Page de formulaire d'ajout de discipline
    include "CONFIG/databases.php";
    // Dans le cas ou le membre n'est pas connecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    // Dans le cas ou l'admin n'est pas un administrateur
    if ($_SESSION['admin'] == false) {
        header('Location: index.php');
        exit();
    }
    // Cette fonction supprime les accents et les remplace par les équivalents sans accent
    function removeAccent($chaine) {
        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
        return str_replace($search, $replace, $chaine);
    }
    // Gestion du formulaire d'ajout de discipline
    if (isset($_POST['valider'])) {
        if (isset($_POST["name_discipline"]) && !empty($_POST['name_discipline'])) {
            // Récupération des éléments des inputs
            $name = strtolower(htmlspecialchars(str_replace(' ', '_', preg_replace("/ +/", " ", trim(removeAccent(str_replace('\'', '', $_POST['name_discipline'])))))));
            // Si le name est vide
            if (strlen($name) == 0) {
                $error = "Le nom de la catégorie est incomplète";
            } else {
                // Dans le cas contraire
                $search = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE NOMDIS = ?");
                $search->execute(array($name));
                // Si le nom existe déjà
                if ($search->rowCount() > 0) {
                    $error = $name ." existe déjà";
                } else {
                    // On récupère toutes les disciplines
                    $sub_search = $pdo->prepare("SELECT * FROM DISCIPLINE");
                    $sub_search->execute(array());
                    // On récupère la sous chaine du nom de la nouvelle discipline saisie
                    $sub_chaine = substr($name, 0, strpos($name, '_'));
                    if (strlen($sub_chaine) == 0) {
                        $sub_chaine = $name;
                    }
                    $reel_chaine = "";
                    $err = 0;
                    // On parcours toutes les disciplines présentes dans la base
                    while ($sb = $sub_search->fetch()) {
                        // Si la sous chaine de l'entrée saisie correspond à une sous chaine de la base 
                        // Alors c'est qu'elle existe déjà
                        if (strncmp($sub_chaine, $sb['NOMDIS'], strlen($sub_chaine) - 1) === 0) {
                            $err += 1;
                            $reel_chaine = $sb['NOMDIS'];
                        }
                    } 
                    // Insertion dans le cas ou aucune erreur à été levée
                    if ($err == 0) {
                        $request = $pdo->prepare('INSERT INTO DISCIPLINE(NOMDIS) VALUES (?)');
                        $request->execute(array($name));
                        $success = $name ." a bien été ajouté";
                    } else {
                        $error = $reel_chaine ." existe déjà";
                    }             
                }
            }
        } else {
            $error = "Le nom de la catégorie n'est pas saisie";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ajouter une discipline</title>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/form.css"/>
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latérale -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('add_dis') </script>
        <!-- Le formulaire d'ajout de disciplines -->
        <div class="big_container">
            <div class="container-form">
                <div class="form">
                    <form action="add_discipline.php" method="POST">
                        <h2>Ajouter une discipline </h2>
                        <!-- Affichage des erreurs ou des succes -->
                        <?php 
                            if (isset($error)) {
                                echo '<div id="error">' . $error . "</div>";
                            } else if (isset($success)) {
                                echo '<div id="success">' . $success . "</div>";
                            } 
                        ?>
                        <!-- L'input du nom de la nouvelle discipline -->
                        <h3><label for="name_dis">Nom de la discipline</label></h3>
                        <input type="text" id="name_dis" autocomplete="off" name="name_discipline" placeholder="Nom de la discipline" required />
                        <!-- L'input du bouton de soumission -->
                        <input type="submit" class="submit_button" name="valider" value="Ajouter la discipline">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>