<?php
    require_once './Model/ClientREST.php';
    $client_monApi = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/mon-api/');
    $client_Auth = new ClientREST('http://localhost/projet/TP-API/projet-r401/api/auth/');

?>