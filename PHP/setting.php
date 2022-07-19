<?php 
    // Page des paramètres
    include "CONFIG/databases.php";
    // Redirection vers la page de connexion si le membre est déconnecté
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Paramètres</title> 
        <link rel="stylesheet" href="../CSS/style.css"/>
        <link rel="stylesheet" href="../CSS/menu.css">
        <link rel="stylesheet" href="../CSS/navbar.css">
        <link rel="stylesheet" href="../CSS/form.css">
        <link rel="shortcut icon" type="image/x-icon" href="CONFIG/lapin-mignon.ico" />
    </head>
    <body>
        <!-- Inclusion du menu latéral -->
        <?php include "CONFIG/menu.php"; ?>
        <script> clickedItem('setting') </script>
        <!-- Affichage des accès aux paramètres -->
        <div class="big_container">
            <!-- Affichage pour le membre connecté et administrateur  -->
            <?php if (isset($_SESSION['id']) && $_SESSION['admin'] == true) { ?>
                <!-- Barre de navigation pour les différentes paramètres gérable par un administrateur -->
                <nav id="navbar">
                    <ul>
                        <li <?php if (isset($_GET['ITEM']) && $_GET['ITEM'] == "dis") { echo "id='clicked'";} ?>><a href="setting.php?ITEM=dis">Discipline</a></li>
                        <li <?php if (isset($_GET['ITEM']) && $_GET['ITEM'] == "ues") { echo "id='clicked'";} ?>><a href="setting.php?ITEM=ues">Unités</a></li>
                        <li <?php if (isset($_GET['ITEM']) && $_GET['ITEM'] == "doc") { echo "id='clicked'";} ?>><a href="setting.php?ITEM=doc">Document</a></li>
                        <li <?php if (isset($_GET['ITEM']) && $_GET['ITEM'] == "mem") { echo "id='clicked'";} ?>><a href="setting.php?ITEM=mem">Membres</a></li> 
                    </ul>
                </nav>
            <?php }
                // Tableau d'accès des paramètres des différentes items
                if (isset($_GET['ITEM'])) {
                    // Si le membre est connecté et est un administrateur
                    if (isset($_SESSION['id']) && $_SESSION['admin'] == true) { 
                        if ($_GET['ITEM'] == 'dis') { ?>
                        <div class="param">
                            <!-- Tableau des disciplines -->
                            <div>
                                <h2>DISCIPLINE</h2>
                                <?php 
                                    $request = $pdo->prepare("SELECT * FROM DISCIPLINE ORDER BY NOMDIS ASC");
                                    $request->execute(array());
                                    if ($request->rowCount() == 0) {
                                        echo '<div class="empty">Aucune discipline n\'est présente dans notre base</div>';
                                    } else { 
                                        echo '<table>';
                                        echo '<thead><tr><td>NOM</td><td>MODIFIER</td><td>SUPPRIMER</td></tr></thead>';
                                        while ($r = $request->fetch()) {
                                            echo '<tr>';
                                            echo "<td>". $r['NOMDIS'] ."</td>";
                                            echo "<td><a href='updating.php?IDDIS=". $r['IDDIS'] ."'>modifer <span><ion-icon name='create-outline'></ion-icon></span></a></td>";
                                            echo "<td><a href='removing.php?IDDIS=". $r['IDDIS'] ."'>supprimer <span><ion-icon name='trash-outline'></ion-icon></span></a></td></tr>";
                                        }
                                        echo '</table>';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php } else if ($_GET['ITEM'] == 'ues') { ?>
                        <div class="param">
                            <!-- Tableau des unités d'enseignements -->
                            <div>
                                <h2>UEs</h2>
                                <?php 
                                    $request = $pdo->prepare("SELECT * FROM UNIENS ORDER BY IDUE DESC");
                                    $request->execute(array());
                                    if ($request->rowCount() == 0) {
                                        echo '<div class="empty">Aucun unités d\'enseigement n\'est présente dans notre base</div>';
                                    } else { 
                                        echo '<table>';
                                        echo '<thead><tr><td>NOM</td><td>NIVEAU</td><td>MODIFIER</td><td>SUPPRIMER</td></tr></thead>';
                                        while ($r = $request->fetch()) {
                                            echo '<tr>';
                                            echo "<td>". $r['NOMUE'] ."</td>";
                                            $search_lvl = $pdo->prepare('SELECT * FROM LEVELING WHERE IDLEVEL = ?');
                                            $search_lvl->execute(array($r['LEVELUE']));
                                            $sl = $search_lvl->fetch();
                                            echo "<td>". $sl['ITEMLEVEL'] ."</td>";
                                            echo "<td><a href='updating.php?IDUE=". $r['IDUE'] ."'>modifer <span><ion-icon name='create-outline'></ion-icon></span></a></td>";
                                            echo "<td><a href='removing.php?IDUE=". $r['IDUE'] ."'>supprimer <span><ion-icon name='trash-outline'></ion-icon></span></a></td></tr>";
                                        }
                                        echo '</table>';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php } else if ($_GET['ITEM'] == 'doc') { ?>
                        <div class="param">
                            <!-- Tableau des documents -->
                            <div>
                                <h2>DOCUMENTS</h2>
                                <?php 
                                    $request = $pdo->prepare("SELECT * FROM DOCUMENT ORDER BY IDDOC DESC");
                                    $request->execute(array());
                                    if ($request->rowCount() == 0) {
                                        echo '<div class="empty">Aucun document n\'est présent dans notre base</div>';
                                    } else { 
                                        echo '<table>';
                                        echo '<thead><tr><td>NOM</td><td>UNITES</td><td>MODIFIER</td><td>SUPPRIMER</td></tr></thead>';
                                        while ($r = $request->fetch()) {
                                            echo '<tr>';
                                            echo "<td>". $r['NOMDOC'] ."</td>";
                                            $name_uniens = $pdo->prepare("SELECT NOMUE FROM UNIENS WHERE IDUE = ?");
                                            $name_uniens->execute(array($r['IDUEDOC']));
                                            $n = $name_uniens->fetch();
                                            echo "<td>". $n['NOMUE'] ."</td>";
                                            echo "<td><a href='updating.php?IDDOC=". $r['IDDOC'] ."'>modifer <span><ion-icon name='create-outline'></ion-icon></span></a></td>";
                                            echo "<td><a href='removing.php?IDDOC=". $r['IDDOC'] ."'>supprimer <span><ion-icon name='trash-outline'></ion-icon></span></a></td></tr>";
                                        }
                                        echo '</table>';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php } else if ($_GET['ITEM'] == 'mem') { ?>
                        <div class="param">
                            <!-- Tableau des différents membres -->
                            <div>
                                <h2>MEMBRES</h2>
                                <?php 
                                    $request = $pdo->prepare("SELECT * FROM MEMBER");
                                    $request->execute(array());
                                    if ($request->rowCount() == 0) {
                                        echo '<div class="empty">Aucun membre n\'est présent dans notre base</div>';
                                    } else { 
                                        echo '<table>';
                                        echo '<thead><tr><td>NOM</td><td>PRENOM</td><td>MOT DE PASSE</td><td>ADMINISTRATEUR</td><td>MODIFIER</td><td>SUPPRIMER</td></tr></thead>';
                                        while ($r = $request->fetch()) {
                                            echo '<tr>';
                                            echo "<td>". $r['NAMEUSER'] ."</td>";
                                            echo "<td>". $r['FAMILYUSER'] ."</td>";
                                            echo "<td>". $r['PASSUSER'] ."</td>";
                                            echo "<td>". (($r['ADMINUSER'] == 1) ? '<span><ion-icon name="eye-outline"></ion-icon></span>' : '<span><ion-icon name="eye-off-outline"></ion-icon></span>') ."</td>";
                                            echo "<td><a href='updating.php?IDUSER=". $r['IDUSER'] ."'>modifer <span><ion-icon name='create-outline'></ion-icon></span></a></td>";
                                            echo "<td><a href='removing.php?IDUSER=". $r['IDUSER'] ."'>supprimer <span><ion-icon name='trash-outline'></ion-icon></span></a></td></tr>";
                                        }
                                        echo '</table>';
                                    }
                                ?>
                            </div>
                        </div>
            <?php }}} else if (isset($_SESSION['id'])) { ?>
                <!-- Paramètre personnelles -->
                <div class="param">
                    <div class="form">
                        <?php
                            // Récupération des informations du membre
                            $search = $pdo->prepare("SELECT * FROM MEMBER WHERE IDUSER = ?");
                            $search->execute(array($_SESSION['user']));
                            $s = $search->fetch();
                        ?>
                        <form action="CONFIG/changeuser.php?IDUSER=<?php echo $s['IDUSER'];?>&change_user=true" method="POST">
                            <h2> MODIFIER MON COMPTE </h1>
                            <!-- Affichage des erreurs, succeesss et informations -->
                            <?php 
                                if (isset($_GET['msgsucc'])) {
                                    echo '<div id="success">' . $_GET['msgsucc'] . "</div>";
                                } else if (isset($_GET['msgserr'])) {
                                    echo '<div id="error">' . $_GET['msgserr'] . "</div>";
                                } if (isset($_GET['msginf'])) {
                                    echo '<div id="info">' . $_GET['msginf'] . "</div>";
                                }
                            ?>
                            <h2 style="text-decoration: none;">Informations personnelles</h2>
                            <!-- L'input pour le prénom -->
                            <h3><label for="user_name">Mon prénom</label></h3>
                            <input required autocomplete="off" id="user_name" type="text" name="name" value="<?php echo $s["NAMEUSER"]; ?>" />
                            <!-- L'input pour le nom de famille -->
                            <h3><label for="user_fam">Mon nom de famille</label></h3>
                            <input required autocomplete="off" id="user_fam" type="text" name="family" value="<?php echo $s["FAMILYUSER"]; ?>" />
                            <!-- L'input pour l'adresse mail -->
                            <h3><label for="user_email">Mon adresse mail</label></h3>
                            <input required autocomplete="off" id="user_email" type="text" name="email" value="<?php echo $s["EMAILUSER"]; ?>" />
                            <h2 style="text-decoration: none;">Mes paramètres d'affichage</h2>
                            <!-- L'input pour saisir la limite d'affichage -->
                            <h3><label for="user_par_lim">Limite d'affichage des récents ajouts</label></h3>
                            <input value="5" autocomplete="off" id="user_par_lim" type="text" name="par_limit" pattern="[0-9]{1,2}"/>
                            <h2 style="text-decoration: none;">Mot de passe</h2>
                            <!-- L'input pour saisir l'ancien mot de passe -->
                            <h3><label for="user_passrem">Mon mot de passe actuel</label></h3>
                            <input  autocomplete="off" id="user_passrem" type="password" name="rem_pass" pattern="[a-zA-Z0-9]{10,}"/>
                            <!-- L'input pour saisir le nouveau mot de passe -->
                            <h3><label for="user_passadd">Mon nouveau mot de passe</label></h3>
                            <input  autocomplete="off" id="user_passadd" type="password" name="add_pass" pattern="[a-zA-Z0-9]{10,}"/>
                            <!-- L'input pour reconfirmer le nouveau mot de passe -->
                            <h3><label for="user_passaddconfirm">Confirmer le nouveau mot de passe</label></h3>
                            <input  autocomplete="off" id="user_passaddconfirm" type="password" name="confirm_add_pass" pattern="[a-zA-Z0-9]{10,}"/>
                            <!-- Le bouton de soumission du formulaire de modification des données -->
                            <input type="submit" class="submit_button" value="Modifier" name="modif_user" />
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
        <!-- Insertion des modules JS -->
        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
        <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    </body>
</html>