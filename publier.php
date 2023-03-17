<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/page.css">
    <link rel="stylesheet" href="./css/publier.css">
    <title>Publier</title>
</head>
<body>
<?php
    require './include/navBar.php'
    ?>
    <div class="Main-Content">
        <div class="Main-Write-Box">
            <h2>Edition publication</h2>
            <form action="" class="Post-Field">
                <div class="Edit-Post-Title">
                    <label for="titre">Titre du post :</label>
                    <input type="text" id="titre" class="Input Input-titre">
                </div>
                <div class="Edit-Post-Content">
                    <label for="Content">Contenu du post :</label>
                    <textarea type="text" id="content" class="Input Input-Content"></textarea>
                </div>
                <div class="Edit-post-option">
                    <a class="Edit-post-option-button Annuler" onclick = "history.back()"> Annuler</a>
                    <input type="submit" class="Edit-post-option-button Valider" value="Valider">
                </div>
            </form>
        </div>
    </div>
</body>
</html>