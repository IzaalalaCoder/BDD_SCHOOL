<?php 
    // Page qui liste toutes les informations de l'unité d'enseignement
    include "CONFIG/databases.php";
    // Si le membre n'est pas connecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    // Si l'URL ne contient pas l'identifiant et le nom de l'unité d'enseignement
    if (!isset($_GET['IDUE']) || empty($_GET['IDUE']) || !isset($_GET['NAME']) || empty($_GET['NAME'])) { 
        header('Location: uniens.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Document de <?php echo $_GET['NAME']; ?></title>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/listing.css">
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" /> 
    </head>
    <body>
        <!-- Insertion du menu latéral -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('uniens') </script>
        <!-- Listing des informations de l'unité d'enseignement -->
        <div class="big_container">
            <?php 
                if (isset($_GET['IDUE']) && !empty($_GET['IDUE']) && isset($_GET['NAME']) && !empty($_GET['NAME'])) {
                    // Selectionner tout les document associé à l'unité d'enseignement
                    $request = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDUEDOC = ? ORDER BY IDDOC DESC");
                    $request->execute(array($_GET['IDUE']));
                    // Dans le cas ou aucun document n'est associé à l'unité d'enseignement
                    if ($request->rowCount() == 0) {
                        ?>
                            <div class="not">
                                <h2>Aucun document n'est associé à l'unité d'enseignement <?php echo $_GET['NAME']; ?>.</h2>
                            </div>
                        <?php
                    // Dans le cas ou quelques documents est associés à l'unité d'enseignement
                    } else { ?>
                        <div id="info_doc">
                            <?php
                                $search_level = $pdo->prepare("SELECT * FROM LEVELING WHERE IDLEVEL IN (SELECT LEVELUE FROM UNIENS WHERE NOMUE = ?)");
                                $search_level->execute(array($_GET['NAME']));
                                $slvl = $search_level->fetch();
                            ?>
                            <h2>Les documents de <?php echo $_GET['NAME']; ?> ~ <?php echo $slvl['ITEMLEVEL']; ?></h2>
                            <!-- Informations techniques sur les différents documents -->
                            <details>
                                <summary class="button">Informations de <?php echo $_GET['NAME']; ?> </summary>
                                <div>
                                    <h2>Les différents documents</h2>
                                    <p>Total de documents : <?php echo $request->rowCount(); ?></p>
                                    <?php 
                                        $arr_type = array(
                                            "cours_magistraux" =>  " les cours magistraux",
                                            "travaux_pratique" => " les travaux pratiques", 
                                            "travaux_diriges" => " les travaux dirigés", 
                                            "projet"  => " les projets", 
                                            "codes_sources" => " les codes sources", 
                                            "examen" => " les examens", 
                                            "exe" => " les executables");
                                        foreach ($arr_type as $cle => $element) {
                                            $req_type_doc = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDUEDOC = ? AND FONCDOC = ?");
                                            $req_type_doc->execute(array($_GET['IDUE'], $cle));
                                            echo '<p>'. $req_type_doc->rowCount() .' documents concernant'. $element .'.</p>';
                                        }
                                    ?>
                                </div>
                            </details>
                            <!-- L'affichage des tags associés à l'unité d'enseignement -->
                            <?php $cat_request = $pdo->prepare("SELECT * FROM DISCIPLINE WHERE IDDIS IN (SELECT IDDISFON FROM FONCTION WHERE IDUEFON = ?)");
                                $cat_request->execute(array($_GET['IDUE']));
                                if ($cat_request->rowCount() > 0) { ?>
                                    <aside id="filter">
                                        <?php
                                            while ($c = $cat_request->fetch()) {
                                                // bleu, orange, vert, rose
                                                $colors = array('03FDDC', 'EE584B', '75EC70', 'E317EF');
                                                $randomcolor = rand(0, count($colors) - 1);
                                                echo '<span class="tag" style="background-color: #'. $colors[$randomcolor] .';">'. $c['NOMDIS'] .'</span>';
                                            }
                                        ?>
                                    </aside>
                            <?php } ?>
                            <!-- L'affichage de tous les documents associé à l'unité d'enseignement -->
                            <ul>
                                <?php while ($r = $request->fetch()) {
                                    echo '<li>';
                                        echo '<section>';
                                            echo '<h3>'. ((strlen($r['NOMDOC']) >= 57) ? substr($r['NOMDOC'], 0, strlen($r['NOMDOC']) - strlen($r['FONCDOC'])) : $r['NOMDOC']) .'  |   <i>'.$r['FONCDOC'].'</i></h3>';
                                            echo '<h4>'. $r['DESCDOC'] .'</h4>';
                                        echo '</section>';
                                        echo '<div id="link">';
                                            echo '<a href="'. $r['URLDOC'] .'"><span><ion-icon name="eye-outline"></ion-icon></span></a>';
                                        echo '</div>';
                                    echo '</li>';
                                } ?>
                            </ul>
                        </div>
                    <?php }
                }
            ?>
        </div>
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    </body>
</html>