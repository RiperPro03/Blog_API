<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="./testcard.css">
    <link rel="stylesheet" href="../css/cartePost.css">
    <title>test carte post php</title>
</head>
<body>
    <?php
    require '../include/navBar.php'
    ?>
    <div class="Main-Content">
        <div class="Card-Main-Box">
            <div class="Card-Option-Mod">
                <div class="Mod-Option">
                    <a href="" class="Details">Details</a>
                </div>
                <div class="Edit-Del-Option">
                    <a href="" class="Supprimer">Supprimer</a>
                </div>
            </div>
            <div class="Post-header">
                <div class="Title">
                    <h3>Echange</h3>
                </div>
                <div class="Post-author">
                    <p>Jean marc</p>
                </div>
            </div>
            <div class="Post-content">
                <label>Message:</label>
                <p>un test qui fonctionne bien comme il faut</p>
            </div>
            <div class="Post-Footer">
                <div class="Post-Date">
                    <label>Date de publication:</label>
                    <p>21/02/2023</p>
                </div>
                <div class="Like-Dislike-Button">
                    <p class="Like-vote">330 Like</p>
                    <p class="Dislike-vote">400 Dislike</p>
                    <a href=""class="Like">Like</a>
                    <a href="" class="Dislike">Dislike</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>