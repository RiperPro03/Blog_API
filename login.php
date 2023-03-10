<?php
    require_once './Model/ClientREST.php';
    $client_auth = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/auth/');
    if (isset($_POST['formlogin'])) {

        extract($_POST);

        if (!empty($user) && !empty($pass)) {
            $result_token = $client_auth->post(array('username' => 'testModo', 'password' => 'iutinfo'));
            $result_token = json_decode($result_token, true);
            $_SESSION['token'] = $result_token['data'];
            echo $_SESSION['token'];
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
                    <input type="text" name="user" required="required" autocomplete="off">
                    <span>Utilisateur</span>
                    <i></i>
                </div>
    
                <div class="inputBox">
                    <input type="password" name="pass" required="required" autocomplete="off">
                    <span>Mot de passe</span>
                    <i></i>
                </div>
                <input type="submit" value="Connexion" name="formlogin">
            </form>
        </div>
    </div>
    
</body>