<?php
    // Il s'agit de la page de connexion à la base de données
    session_start();
    $username = "root";
    $password = "";
    $pdo = new PDO("mysql:host=localhost;dbname=bdd_school", $username, $password);