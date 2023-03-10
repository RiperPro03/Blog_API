<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire</title>
    <link rel="stylesheet" href="./css/mvp.css">
</head>

<body>
    <div class="container">
        <div class="card">

        <?php
            require_once './Model/ConnexionDB.php';
            $db = ConnexionDB::getInstance();
            if (isset($_POST['formsend'])) {

                extract($_POST);

                if(!empty($suser) && !empty($spass) && !empty($cpass)) {

                    if($spass == $cpass) {
                        $option = ['cost' => 12];
                        $hashpass = password_hash($spass, PASSWORD_BCRYPT, $option); //Pour crypter un MDP

                        $c = $db->prepare("SELECT username FROM users WHERE username = :user");
                        $c->execute([
                            'user' => $suser
                        ]);
                        $nbUser = $c->rowCount();

                        if($nbUser == 0) {
                            $q = $db->prepare("INSERT INTO users (username, password, role) VALUES(:user,:password,:role)");
                            $q->execute([
                                'user' => $suser,
                                'password' => $hashpass,
                                'role' => $role
                            ]);
                            echo "Le compte a été créé ";
                        } else {
                            echo "Ce nom Utilisateur existe déjà ";
                        }
                    }
                }
            }
        ?>

            <h3>S'enregistrer</h3>

            <form method="post">
                <div class="inputBox">
                <span>Utilisateur</span>
                    <input type="text" name="suser" required="required">
                    
                </div>

                <div class="inputBox">
                <span>Mot de passe</span>
                    <input type="password" name="spass" required="required">
                    
                </div>

                <div class="inputBox">
                <span>Confirmer MDP</span>
                    <input type="password" name="cpass" required="required">
                   
                </div>

                <div class="inputBox">
                <span>role</span>
                    <input type="text" name="role" required="required">
                    
                </div>

                <input type="submit" name="formsend" value="Singin" class="button">
            </form>
        </div>
    </div>
</body>

</html>