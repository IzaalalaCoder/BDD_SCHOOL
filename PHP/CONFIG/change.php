<?php 
    include 'databases.php';
    // Cette page nous sert à modifier le mot de passe aléatoirement
    // Une fonction qui nous retourne un mot de passe alétoirement
    function getPassAlea() {
        $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); 
        $combLen = strlen($comb) - 1; 
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $combLen);
            array_push($pass, $comb[$n]);
        }
        return implode($pass); 
    }
    // Dans le cas ou nous somme un administrateur et qu'on connait l'identifiant de l'utilisateur
    if (isset($SESSION['admin']) && $SESSION['admin'] == true && isset($_GET['userid']) && isset($_GET['change']) && !empty($_GET['userid']) && !empty($_GET['change']) && $_GET['change'] == "on") {
        $request = $pdo->prepare('UPDATE MEMBER SET PASSUSER = ? WHERE IDUSER = ?');
        $new_pass = getPassAlea();
        $request->execute(array($new_pass, $_GET['userid']));
        header("Location: ../updating.php?IDUSER=". $_GET['userid']);
        exit();
    } else {
        // dans le cas contraire
        header('Location: ../');
        exit();
    }
?>