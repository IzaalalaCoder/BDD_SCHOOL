<?php 
    // Page de suppression des éléments de la base et dans nos dossiers 
    include "CONFIG/databases.php";
    // Si le membre n'est pas connecté alors redirection dans la page de formulaire de connexion
    if (!isset($_SESSION['id'])) {
        header('Location: ../index.php');
        exit();
    }
    // Si le membre n'est pas un membre administrateur alors redirection vers la page d'accueil
    if ($_SESSION['admin'] == false) {
        header('Location: index.php');
        exit();
    }
    // Si la suppression ne concernent ni les disciplines, ni les unités d'enseignements,
    // ni les documents, ni les utilisateurs alors redirection vers la pages des différents paramètres
    if ((!(isset($_GET['IDDIS'])) || empty($_GET['IDDIS'])) 
        && (!(isset($_GET['IDUE'])) || empty($_GET['IDUE'])) 
        && (!(isset($_GET['IDDOC'])) || empty($_GET['IDDOC'])) 
        && (!(isset($_GET['IDUSER'])) || empty($_GET['IDUSER'])))  {
        header('Location: setting.php');
        exit();
    }
    define("PATH", "../FILES/");
    // Cette fonction nous sert à supprimer tout le contenu d'un dossier
    function deleteFolder($id_ue){
        include "CONFIG/databases.php";
        $search_delete = $pdo->prepare("SELECT * FROM DOCUMENT WHERE IDUEDOC = ?");
        $search_delete->execute(array($id_ue));
        while ($sd = $search_delete->fetch()) {
            if (file_exists($sd['URLDOC'])) {
                unlink($sd['URLDOC']);
            }
        }
    }
    // Suppression d'une discipline
    if (isset($_GET['IDDIS'])) {
        // Suppression des fonctionnalité associés à cette discipline
        $sub_request = $pdo->prepare("DELETE FROM FONCTION WHERE IDDISFON = ?");
        $sub_request->execute(array($_GET['IDDIS']));
        // Suppression de la discipline
        $request = $pdo->prepare("DELETE FROM DISCIPLINE WHERE IDDIS = ?");
        $request->execute(array($_GET['IDDIS']));
        // Redirection
        header('Location: setting.php?ITEM=dis');
        exit();
    // Suppression d'une unité d'enseignement
    } else if (isset($_GET['IDUE'])) {
        // Suppression du contenue de l'unité d'enseignement
        deleteFolder(intval($_GET['IDUE']));
        // Suppression du dossier de l'unité d'enseignement
        $name = $pdo->prepare('SELECT NOMUE FROM UNIENS WHERE IDUE = ?');
        $name->execute(array($_GET['IDUE']));
        $n = $name->fetch();
        $dirPath = PATH . $n['NOMUE'];
        rmdir($dirPath);
        // Suppression des fonction, des documents et de l'unité d'enseignement lui même depuis la base de données 
        $sub_request = $pdo->prepare("DELETE FROM FONCTION WHERE IDUEFON = ?");
        $sub_request->execute(array($_GET['IDUE']));
        $doc_request = $pdo->prepare("DELETE FROM DOCUMENT WHERE IDUEDOC = ?");
        $doc_request->execute(array($_GET['IDUE']));
        $request = $pdo->prepare("DELETE FROM UNIENS WHERE IDUE = ?");
        $request->execute(array($_GET['IDUE']));
        // Redirection
        header('Location: setting.php?ITEM=ues');
        exit();
    // Suppression d'un document
    } else if (isset($_GET['IDDOC'])) {
        // On récupère le lien du document
        $file = $pdo->prepare('SELECT URLDOC FROM DOCUMENT WHERE IDDOC = ?');
        $file->execute(array($_GET['IDDOC']));
        $f = $file->fetch();
        // Suppression du document depuis la base de données
        $delete_request = $pdo->prepare('DELETE FROM DOCUMENT WHERE IDDOC = ?');
        $delete_request->execute(array($_GET['IDDOC']));
        // Suppression du fichier dans nos dossiers
        if (file_exists($f['URLDOC'])) {
            unlink($f['URLDOC']);
        }
        header('Location: setting.php?ITEM=doc');
        exit();
    // Suppression d'un utilisateur
    } else if (isset($_GET['IDUSER'])) {
        // Suppression de l'utilisateur de la base de données
        if ($_GET['IDUSER'] != $_SESSION['user']) {
            $verif = $pdo->prepare("SELECT * FROM MEMBER WHERE ADMINUSER = 1");
            $verif->execute(array());
            $verif_user = $pdo->prepare("SELECT * FROM MEMBER WHERE IDUSER = ? AND ADMINUSER = 0");
            $verif_user->execute(array($_GET['IDUSER']));
            if (($verif->rowCount() > 1) || ($verif->rowCount() == 1 && $verif_user->rowCount() == 1)) {
                $delete_request = $pdo->prepare('DELETE FROM MEMBER WHERE IDUSER = ?');
                $delete_request->execute(array($_GET['IDUSER']));
            } 
        }
        // Redirection
        header('Location: setting.php?ITEM=mem');
        exit();
    }
?>