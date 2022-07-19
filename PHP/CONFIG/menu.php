<!-- Il s'agit du contenu du menu latéral -->
<div class="container-lateral">
    <div class="navigation">
        <!-- Tout les liens cliquable -->
        <ul>
            <li id="brand">
                <a href="index.php">
                    <span class="icon"><ion-icon name="bookmark-outline"></ion-icon></span>
                    <span class="title">BDD SCHOOL</span>
                </a>
            </li>
            <li id="new">
                <a href="index.php">
                    <span class="icon"><ion-icon name="apps-outline"></ion-icon></span>
                    <span class="title">A la une</span>
                </a>
            </li>
            <li id="search">
                <a href="search.php">
                    <span class="icon"><ion-icon name="search-outline"></ion-icon></span>
                    <span class="title">Recherche</span>
                </a>
            </li>
            <li id="uniens">
                <a href="uniens.php">
                    <span class="icon"><ion-icon name="school-outline"></ion-icon></span>
                    <span class="title">UEs</span>
                </a>
            </li>
            <?php if (isset($_SESSION['id']) && isset($_SESSION['admin']) && $_SESSION['admin'] == true) { ?>
                <li id="add_dis">
                    <a href="add_discipline.php">
                        <span class="icon"><ion-icon name="add-outline"></ion-icon></span>
                        <span class="title">Ajouter une discipline </span>
                    </a>
                </li>
                <li id="add_uniens">
                    <a href="add_uniens.php">
                        <span class="icon"><ion-icon name="bag-add-outline"></ion-icon></span>
                        <span class="title">Ajouter une UE</span>
                    </a>
                </li>
                <li id="add_doc">
                    <a href="add_document.php">
                        <span class="icon"><ion-icon name="documents-outline"></ion-icon></span>
                        <span class="title">Ajouter un document</span>
                    </a>
                </li>
                <li id="add_user">
                    <a href="add_user.php">
                        <span class="icon"><ion-icon name="person-add-outline"></ion-icon></span>
                        <span class="title">Ajouter un utilisateur</span>
                    </a>
                </li>
            <?php } ?>
            <li id="setting">
                <a href="setting.php">
                    <span class="icon"><ion-icon name="options-outline"></ion-icon></span>
                    <span class="title">Paramètres</span>
                </a>
            </li>
            <?php 
                if (isset($_SESSION['id'])) { ?>
                    <li id="disconnect">
                        <a href="CONFIG/disconnect.php">
                            <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                            <span class="title">Déconnecter</span>
                        </a>
                    </li>
            <?php } ?>
        </ul>
    </div>
</div>
<!-- Insertion des modules JS -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<!-- Une fonction qui permet de mettre en avant les liens sur lequel on est -->
<script>
    function clickedItem(title) {
        const e = document.getElementById(title);
        if (e == null) {
            return
        } else {
            e.classList.add('clicked')
            li = e
        }
    }
</script>