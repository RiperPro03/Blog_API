<?php
    require_once './Model/ClientREST.php';
    $client_auth = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/auth/');
    if (isset($_POST['formlogin'])) {

        extract($_POST);

        if (!empty($user) && !empty($pass)) {
            try {
                $result_token = $client_auth->post(array('username' => $user, 'password' => $pass));
                if ($result_token) {
                    $result_token = json_decode($result_token, true);
                    $_SESSION['token'] = $result_token['data'];
                    header('Location: index');
                }
            } catch (Exception $e) {
                echo '<div class="error">' . $e->getMessage() . '</div>';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/page.css">
</head>
<body>
    <div class="Main-Content">
        <div class="box">
            <div class="form">
                <div class="titre">
                    <h1>Connexion</h1>
                </div>
                <form method="post">
                    <div class="inputBox">
                        <span>Identifiant </span>
                        <input type="text" name="user" required="required" autocomplete="off">
                    </div>
        
                    <div class="inputBox">
                        <span>Mot de passe</span>
                        <input type="password" name="pass" required="required" autocomplete="off">
                    </div>
                    <div class="Option-login">
                        <a href="" class="Option-login-button Annuler">Annuler</a>
                        <input type="submit" value="Connexion" name="formlogin" class="Option-login-button Connexion">
                    </div>
                </form>
            </div>
        </div>
    </div>    
</body>