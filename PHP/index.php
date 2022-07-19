<?php 
    // Page d'accueil
    include "CONFIG/databases.php";
    // Si le membre n'est pas connectée
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>BDD SCHOOL</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/listing.css"> 
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Insertion du menu latérale -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('new') </script>
        <!-- Affichage de la page d'accueil -->
        <div class="big_container">
            <!-- Affichage des derniers unités d'enseignement ajoutés -->
            <div>
                <?php 
                $search_ue = $pdo->prepare("SELECT * FROM UNIENS ORDER BY IDUE DESC LIMIT 10");
                $search_ue->execute(array());
                if ($search_ue->rowCount() >= 1) {
                    echo '<div id="info_uniens">';
                        echo '<h1>Les dernier UEs ajoutés.</h1>';
                        echo  '<ul>';
                        while ($su = $search_ue->fetch()) { ?>
                            <?php
                            $search_lvl = $pdo->prepare('SELECT * FROM LEVELING WHERE IDLEVEL = ?');
                            $search_lvl->execute(array($su['LEVELUE']));
                            if ($search_lvl->rowCount() == 1) { 
                                $slvl = $search_lvl->fetch();
                                echo '<li>';
                                    echo '<section>';
                                        echo '<h3>'. $su['NOMUE'] .' | '. $su['NOMPROFUE'] . ' | '. $slvl['ITEMLEVEL'] .'</h3>';
                                    $request = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE IDDIS IN (SELECT IDDISFON FROM FONCTION WHERE IDUEFON = ?)");
                                    $request->execute(array($su['IDUE']));
                                    if ($request->rowCount() > 0) { ?>
                                        <aside id="filter">
                                            <?php
                                                while ($r = $request->fetch()) {
                                                    // bleu, orange, vert, rose
                                                    $colors = array('03FDDC', 'EE584B', '75EC70', 'E317EF');
                                                    $randomcolor = rand(0, count($colors) - 1);
                                                    echo '<span class="tag" style="background-color: #'. $colors[$randomcolor] .';">'. $r['NOMDIS'] .'</span>';
                                                }
                                            ?>
                                        </aside>
                                <?php }
                                    echo '</section>';
                                echo '</li>';
                        } }?>
                        </ul>
                    </div>
                <?php } else { ?>
                    <div class="container-error">
                        <div class="not">
                            <h2>Aucun unité d'enseignement n'est ajouté dans la base.</h2>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- Affichage des derniers documents ajoutés -->
            <div class="doc">
                <?php
                    $search_doc = $pdo->prepare("SELECT * FROM DOCUMENT ORDER BY IDDOC DESC LIMIT 10");
                    $search_doc->execute(array());
                    if ($search_doc->rowCount() >= 1) {
                        echo '<div id="info_doc">';
                            echo '<h1>Les dernier documents ajoutés.</h1>';
                            echo '<ul>';
                        while ($sd = $search_doc->fetch()) { ?>
                                    <?php
                                    $search_ue_with_doc = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                    $search_ue_with_doc->execute(array($sd['IDUEDOC']));
                                    $swd = $search_ue_with_doc->fetch();
                                    echo '<li>';
                                        echo '<section>';
                                            echo '<h3>'. ((strlen($sd['NOMDOC']) >= 57) ? substr($sd['NOMDOC'], 0, strlen($sd['NOMDOC']) - strlen($sd['FONCDOC'])) : $sd['NOMDOC']) .'  |   <i>'. $sd['FONCDOC'] .'</i> - '. $swd['NOMUE'] .'</h3>';
                                            echo '<h4>'. $sd['DESCDOC'] .'</h4>'; 
                                        echo '</section>';
                                        echo '<div id="link">';
                                            echo '<a href="'. $sd['URLDOC'] .'"><span><ion-icon name="eye-outline"></ion-icon></span></a>';
                                        echo '</div>';
                                    echo '</li>';
                        } ?>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <div class="container-error">
                            <div class="not">
                                <h2>Aucun document n'est ajouté dans la base.</h2>
                            </div>
                        </div>
                <?php } ?>
            </div>
        </div>
    </body>
</html>