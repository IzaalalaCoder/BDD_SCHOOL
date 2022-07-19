<?php 
    // Il s'agit de la déconnexion du membre
    include "databases.php";
    session_destroy();
    header("Location: ../index.php");
    exit();