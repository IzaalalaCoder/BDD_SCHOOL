<?php 
    // Page de recherche des documents 
    include "CONFIG/databases.php";
    // Si le membre n'est pas connecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Recherche</title> 
        <link rel="stylesheet" href="../CSS/search.css"/>
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/listing.css">
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
        <!-- STYLISATION EN FONCTION DES COMPORTEMENTS -->
        <?php
            // Si une recherche s'est faite avec des tag
            if (isset($_GET['TAG']) && !empty($_GET['TAG'])) {
                $filter = $pdo->prepare('SELECT NOMDOC, URLDOC, FONCDOC, DESCDOC FROM DISCIPLINE, FONCTION, DOCUMENT WHERE IDDIS = IDDISFON AND IDUEDOC = IDUEFON AND NOMDIS = ?');
                $filter->execute(array($_GET['TAG']));
                if ($filter->rowCount() == 0) { ?>
                    <style>
                        body {
                            overflow-y: hidden;
                        }
                    </style> 
                <?php } else { ?>  
                    <style>
                        body {
                            overflow-y: scroll;
                        }
                    </style> 
                <?php }} ?>
        <?php // Si une recherche par la barre de recherche s'est faite
            if (isset($_GET['searching']) && isset($_POST['search_button'])) {
                if (isset($_POST['search']) && !empty($_POST['search'])) {
                    $search = htmlspecialchars($_POST['search']);
                    $request = " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search ."%' ";
                    $search_array = explode(' ', $search);
                    if (count($search_array) > 1) {
                        $request .= " UNION ";
                        for ($i = 0; $i < count($search_array); $i++) {
                            if ($i == count($search_array) - 1) {
                                $request .= " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search_array[$i] . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search_array[$i] ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search_array[$i] ."%' ";
                            } else {
                                $request .= " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search_array[$i] . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search_array[$i] ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search_array[$i] ."%' UNION ";
                            }
                        }
                    }
                    $search_in = $pdo->prepare($request);
                    $search_in->execute(array());
                    if ($search_in->rowCount() == 0) { ?>
                    <style>
                        body {
                            overflow-y: hidden;
                        }
                    </style> 
                <?php } else { ?>  
                    <style>
                        body {
                            overflow-y: scroll;
                        }
                    </style> 
                <?php }}} 
                // Si la recherche s'est faite par les niveaux
                else if (isset($_GET['LEVEL']) && !empty($_GET['LEVEL'])) {
                    $level_num = $pdo->prepare("SELECT IDLEVEL FROM LEVELING WHERE ITEMLEVEL = ?");
                    $level_num->execute(array($_GET['LEVEL']));
                    if ($level_num->rowCount() != 1) {
                        echo '<div class="not"><h2>Erreur pour le niveau '. $_GET["LEVEL"] .' </h2></div>';
                    }
                    $ln = $level_num->fetch();
                    $search_lvl = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDUEDOC IN (SELECT IDUE FROM UNIENS WHERE LEVELUE = ?)");
                    $search_lvl->execute(array($ln['IDLEVEL']));
                    if ($search_lvl->rowCount() == 0) { ?>
                        <style>
                            body {
                                overflow-y: hidden;
                            }
                        </style> 
                    <?php  } else { ?>
                        <style>
                            body {
                                overflow-y: scroll;
                            }
                        </style> 
                <?php }} ?>
    </head>
    <body>
        <!-- Inclusion du menu latérale -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('search') </script>
        <div class="big_container">
            <div class="container">
                <!-- Entête qui contient la barre de recherche -->
                <header>
                    <form action="search.php?searching=on" method="POST">
                        <!-- L'input qui récupère la recherche -->
                        <input type="text" autocomplete="off" name="search" placeholder="Effectuez une recherche">
                        <button name="search_button" class="submit_button" type="submit"><ion-icon name="search-outline"></ion-icon></button>
                    </form>
                </header>
            </div>
            <!-- Affichage des différents tags -->
            <div id="search_help">
                <?php
                    $request = $pdo->prepare("SELECT * FROM DISCIPLINE");
                    $request->execute(array());
                    if ($request->rowCount() > 0) { ?>
                        <section id="filter">
                            <h3>Les différentes disciplines</h3>
                            <?php
                                while ($r = $request->fetch()) {
                                    // bleu, orange, vert, rose
                                    $colors = array('03FDDC', 'EE584B', '75EC70', 'E317EF');
                                    $randomcolor = rand(0, count($colors) - 1);
                                    echo '<a class="tag" style="background-color: #'. $colors[$randomcolor] .';" href="search.php?TAG='. $r['NOMDIS'] . '">'. $r['NOMDIS'] .'</a>';
                                }
                            ?>
                        </section>
                        <?php } $request_l = $pdo->prepare("SELECT * FROM LEVELING");
                        $request_l->execute(array());
                        if ($request_l->rowCount() > 0) { ?>
                        <section id="level">
                            <h3>Les différents niveaux</h3>
                            <?php
                                while ($rl = $request_l->fetch()) {
                                    // bleu, orange, vert, rose
                                    $colors = array('03FDDC', 'EE584B', '75EC70', 'E317EF');
                                    $randomcolor = rand(0, count($colors) - 1);
                                    echo '<a class="tag" style="background-color: #'. $colors[$randomcolor] .';" href="search.php?LEVEL='. $rl['ITEMLEVEL'] . '">'. $rl['ITEMLEVEL'] .'</a>';
                                }
                            ?>
                        </section>
                <?php } ?>
            </div>
            <!-- Réponse de la page en fonction des demandes de l'utilisateur -->
                <?php
                    // Réponse en fonction d'un tag
                    if (isset($_GET['TAG']) && !empty($_GET['TAG'])) {
                        $filter = $pdo->prepare('SELECT NOMDOC, URLDOC, FONCDOC, DESCDOC, IDUEDOC FROM DISCIPLINE, FONCTION, DOCUMENT WHERE IDDIS = IDDISFON AND IDUEDOC = IDUEFON AND NOMDIS = ?');
                        $filter->execute(array($_GET['TAG']));
                        if ($filter->rowCount() == 0) {
                            echo '<div class="not"><h2>Aucun résultat pour le filtre '. $_GET['TAG'] .' </h2></div>';
                        } else { ?>
                            <div id="info_doc">
                                <h2>Les documents avec le tag : <?php echo $_GET['TAG']; ?></h2>
                                <ul>
                                <?php while ($f = $filter->fetch()) {
                                    $search_ue_with_doc = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                    $search_ue_with_doc->execute(array($f['IDUEDOC']));
                                    $swd = $search_ue_with_doc->fetch();
                                    echo '<li>';
                                        echo '<section>';
                                            echo '<h3>'. ((strlen($f['NOMDOC']) >= 57) ? substr($f['NOMDOC'], 0, strlen($f['NOMDOC']) - strlen($f['FONCDOC'])) : $f['NOMDOC']) .'  |   <i>'.$f['FONCDOC'].'</i> - '. $swd['NOMUE'] .'</h3>';
                                            echo '<h4>'. $f['DESCDOC'] .'</h4>'; 
                                        echo '</section>';
                                        echo '<div id="link">';
                                            echo '<a href="'. $f['URLDOC'] .'"><span><ion-icon name="eye-outline"></ion-icon></span></a>';
                                        echo '</div>';
                                    echo '</li>';
                                    } ?>
                                </ul>
                            </div>
                        <?php }
                    ?>
                <?php
                    // Réponse de la barre de recherche
                    } else if (isset($_GET['searching']) && isset($_POST['search_button'])) {
                        if (isset($_POST['search']) && !empty($_POST['search'])) {
                            $search = htmlspecialchars($_POST['search']);
                            $request = " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search ."%' ";
                            $search_array = explode(' ', $search);
                            if (count($search_array) > 1) {
                                $request .= " UNION ";
                                for ($i = 0; $i < count($search_array); $i++) {
                                    if ($i == count($search_array) - 1) {
                                        $request .= " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search_array[$i] . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search_array[$i] ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search_array[$i] ."%' ";
                                    } else {
                                        $request .= " SELECT * FROM DOCUMENT WHERE DESCDOC LIKE '%". $search_array[$i] . "%' UNION SELECT * FROM DOCUMENT WHERE FONCDOC LIKE '%". $search_array[$i] ."%' UNION SELECT * FROM DOCUMENT WHERE NOMDOC LIKE '%". $search_array[$i] ."%' UNION ";
                                    }
                                }
                            }
                            $search_in = $pdo->prepare($request);
                            $search_in->execute(array());
                            if ($search_in->rowCount() == 0) {
                                echo '<div class="not"><h2>Aucun résultat pour la recherche '. $search .' </h2></div>';
                            } else { ?>
                                <div id="info_doc">
                                    <h2>Les documents avec la recherche : <?php echo $search; ?></h2>
                                    <ul>
                                    <?php while ($s = $search_in->fetch()) {
                                        $search_ue_with_doc = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                        $search_ue_with_doc->execute(array($s['IDUEDOC']));
                                        $swd = $search_ue_with_doc->fetch();
                                        echo '<li>';
                                            echo '<section>';
                                                echo '<h3>'. ((strlen($s['NOMDOC']) >= 57) ? substr($s['NOMDOC'], 0, strlen($s['NOMDOC']) - strlen($s['FONCDOC'])) : $s['NOMDOC']) .'  | <i>'.$s['FONCDOC'].'</i> - '. $swd['NOMUE'] .'</h3>';
                                                echo '<h4>'. $s['DESCDOC'] .'</h4>';
                                            echo '</section>';
                                            echo '<div id="link">';
                                                echo '<a href="'. $s['URLDOC'] .'"><span><ion-icon name="eye-outline"></ion-icon></span></a>';
                                            echo '</div>';
                                        echo '</li>';
                                        } ?>
                                    </ul>
                                </div>
                            <?php }
                        } 
                    // Recherche en fonction du niveau 
                    } else if (isset($_GET['LEVEL']) && !empty($_GET['LEVEL'])) {
                        $level_num = $pdo->prepare("SELECT IDLEVEL FROM LEVELING WHERE ITEMLEVEL = ?");
                        $level_num->execute(array($_GET['LEVEL']));
                        if ($level_num->rowCount() != 1) {
                            echo '<div class="not"><h2>Erreur pour le niveau '. $_GET["LEVEL"] .' </h2></div>';
                        }
                        $ln = $level_num->fetch();
                        $search_lvl = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDUEDOC IN (SELECT IDUE FROM UNIENS WHERE LEVELUE = ?)");
                        $search_lvl->execute(array($ln['IDLEVEL']));
                        if ($search_lvl->rowCount() == 0) {
                            echo '<div class="not"><h2>Aucun résultat pour le niveau '. $_GET["LEVEL"] .' </h2></div>';
                        } else { ?>
                            <div id="info_doc">
                                <h2>Les documents avec la recherche : <?php echo $_GET['LEVEL']; ?></h2>
                                <ul>
                                    <?php while ($sl = $search_lvl->fetch()) {
                                        $search_ue_with_doc = $pdo->prepare("SELECT * FROM UNIENS WHERE IDUE = ?");
                                        $search_ue_with_doc->execute(array($sl['IDUEDOC']));
                                        $swd = $search_ue_with_doc->fetch();
                                        echo '<li>';
                                            echo '<section>';
                                                echo '<h3>'. ((strlen($sl['NOMDOC']) >= 57) ? substr($sl['NOMDOC'], 0, strlen($sl['NOMDOC']) - strlen($sl['FONCDOC'])) : $sl['NOMDOC']) .'  |   <i>'.$sl['FONCDOC'].'</i> - '. $swd['NOMUE'] .'</h3>';
                                                echo '<h4>'. $sl['DESCDOC'] .'</h4>';
                                            echo '</section>';
                                            echo '<div id="link">';
                                                echo '<a href="'. $sl['URLDOC'] .'"><span><ion-icon name="eye-outline"></ion-icon></span></a>';
                                            echo '</div>';
                                        echo '</li>';
                                    } ?>
                                </ul>
                            </div>
                        <?php }
                    }
                ?>
        </div>
        <!-- Insertion des modules JS -->
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    </body>
</html>