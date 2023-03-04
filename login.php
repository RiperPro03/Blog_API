<?php
    if (isset($_POST['formlogin'])) {

        extract($_POST);

        if (!empty($luser) && !empty($lpass)) {
            echo '<script>alert("User : '. $luser .' Pass: '. $lpass .'");</script>';
        } else {
            echo '<script>alert("Veuiller remplir tout les champs");</script>';
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoL Gestion | Connexion</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="titre">
        <h1> Lol Gestion</h1>
        <h2><span>Gestion d'équipe esport</span><span>League Of Legends</span></h2>
    </div>
    
    <div class="box">
        <div class="form">
            <h2>Connexion</h2>
            <form method="post">

                <div class="inputBox">
                    <input type="text" name="luser" required="required" autocomplete="off">
                    <span>Utilisateur</span>
                    <i></i>
                </div>
    
                <div class="inputBox">
                    <input type="password" name="lpass" required="required" autocomplete="off">
                    <span>Mot de passe</span>
                    <i></i>
                </div>
                <input type="submit" value="Connexion" name="formlogin">
            </form>
        </div>
    </div>
    
</body>