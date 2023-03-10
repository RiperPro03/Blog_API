<?php
    require_once './Model/ClientREST.php';

    //Récupération des données
    $client = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/mon-api/');
    $client_auth = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/auth/');
    $r = $client_auth->post(array('username' => 'testModo', 'password' => 'iutinfo'));
    echo '<pre>' . htmlspecialchars($r) . '</pre>';
    $r = json_decode($r, true);
    echo '<pre>' . htmlspecialchars($r['data']) . '</pre>';
    $result = $client->get(null, $r['data']);
    echo '<pre>' . htmlspecialchars($result) . '</pre>';
    $result = json_decode($result, true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client</title>
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/client.css">
    <link rel="stylesheet" href="./css/mvp.css">
    <link rel="stylesheet" href="./css/cartePost.css">
</head>
<body>
    <?php
        require './include/navBar.php'
    ?>

    <a href="./">Home</a>
    <h1>API REST</h1>
    <div>
        <table>
            <tr>
                <th>ID</th>
                <th>Phrase</th>
                <th>Vote</th>
                <th>Date d'ajout</th>
                <th>Date de modification</th>
                <th>Faute</th>
                <th>Signalement</th>
                <th>Action</th>
                <th><a href="add.php"><button>Ajouter</button></a></th>
            </tr>
            <?php
                
                foreach ($result['data'] as $data) {
                    echo '<tr>';
                    echo '<td>' . (isset($data['id']) ? htmlspecialchars(strip_tags($data['id'])) : '') . '</td>';
                    echo '<td>' . (isset($data['phrase']) ? htmlspecialchars(strip_tags($data['phrase'])) : '') . '</td>';
                    echo '<td>' . (isset($data['vote']) ? htmlspecialchars(strip_tags($data['vote'])) : '') . '</td>';
                    echo '<td>' . (isset($data['date_ajout']) ? htmlspecialchars(strip_tags($data['date_ajout'])) : '') . '</td>';
                    echo '<td>' . (isset($data['date_modif']) ? htmlspecialchars(strip_tags($data['date_modif'])) : '') . '</td>';
                    echo '<td>' . (isset($data['faute']) ? htmlspecialchars(strip_tags($data['faute'])) : '') . '</td>';
                    echo '<td>' . (isset($data['signalement']) ? htmlspecialchars(strip_tags($data['signalement'])) : '') . '</td>';
                    echo '<td>
                                <a href="delete.php?id=' . (isset($data['id']) ? htmlspecialchars(strip_tags($data['id'])) : '') . '"><button>Supprimer</button></a>
                                <a href="edit.php?id=' . (isset($data['id']) ? htmlspecialchars(strip_tags($data['id'])) : '') . '"><button>Edit</button></a>
                            </td>';
                    echo '</tr>';
                }
            ?>
        </table>
    </div>
    
</body>
</html>