<!-- Page de la liste de toutes les unités d'enseignements -->
<?php 
    include "CONFIG/databases.php";
    // Redirection vers la page de formulaire de connexion si le membre n'est pas connecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tous les UEs</title>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/listing.css">
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu  -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('uniens') </script>
        <!-- Affichege de la liste de toutes les Unités d'enseignements -->
        <div class="big_container">
            <?php
                // On sélectionne toutes les unités d'enseignements
                $request = $pdo->prepare("SELECT * FROM UNIENS ORDER BY NOMUE ASC");
                $request->execute(array());
                // Dans le cas ou il y a absence d'unités d'enseignements
                if ($request->rowCount() == 0) {
                    ?>
                        <div class="not">
                            <h2>Aucun unité d'enseignement n'est ajouté dans la base.</h2>
                        </div>
                    <?php
                // Dans le cas contraire
                } else { ?>
            <div id="info_uniens">
                <h2>LES UEs</h2>
                <div id="listing">
                    <?php 
                        // On sélectionne tous les niveaux existant dans la base
                        $list_level = $pdo->prepare('SELECT * FROM LEVELING ORDER BY IDLEVEL ASC');
                        $list_level->execute(array());
                        // On parcours les différents niveau 
                        while ($ll = $list_level->fetch()) {
                            $sub_request = $pdo->prepare("SELECT * FROM UNIENS WHERE LEVELUE = ? ORDER BY IDUE DESC");
                            $sub_request->execute(array($ll['IDLEVEL']));
                            if ($sub_request->rowCount() >= 1) {
                                // Affichage du titre du niveau 
                                echo "<h3 class='item_level'> ". $ll['ITEMLEVEL'] ."</h3>";
                    ?>
                        <!-- Affichage de la liste en fonction du niveau -->
                        <ul class='sub_item_level'>
                            <?php 
                            while ($sr = $sub_request->fetch()) { ?>
                                <li>
                                    <?php   
                                        $url = 'info_uniens.php?IDUE=' . $sr['IDUE'] . '&NAME=' . $sr['NOMUE'];
                                        echo '<a href="' . $url . '">' . $sr['NOMUE'] . '</a>'; 
                                    } 
                                    ?> 
                                </li>
                        </ul>
                <?php }}} ?>
            </div>   
        </div>
    </body>
</html>