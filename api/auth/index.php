<?php
    /// Récupération de la biliothèque JWT
    require_once '../../Model/jwt_utils.php';

    /// Connexion à la base de données
    require_once '../../Model/ConnexionDB.php';
    $db = ConnexionDB::getInstance();
    
    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method) {
        /// Cas de la méthode POST
        case "POST":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[Auth] POST request : Erreur lors de la récupération des données.");

                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[Auth] POST request : Erreur lors du décodage des données.");

                $postedData = $json_data;
                if (!isset($postedData['username'])) throw new Exception("[Auth] POST request : Erreur vous devez donner une clé username.");
                if (!isset($postedData['password'])) throw new Exception("[Auth] POST request : Erreur vous devez donner une clé password.");

                /// Evité la faille XSS
                $username = htmlspecialchars($postedData['username'], ENT_QUOTES, 'UTF-8');
                $password = htmlspecialchars($postedData['password'], ENT_QUOTES, 'UTF-8');

                /// Vérification de l'existence de l'utilisateur
                $q = $db->prepare("SELECT * FROM users WHERE username = :username");
                $q->execute([
                    'username' => $username
                ]);
                $nb_user = $q->rowCount();
                $result = $q->fetch();

                if ($nb_user == 0) throw new Exception("[Auth] POST request : L'utilisateur n'existe pas.");
                //if (!password_verify($password, $result['password'])) throw new Exception("[Auth] POST request : Le mot de passe est incorrect.");

                /// Création du token
                $headers = [
                    'alg' => 'HS256',
                    'typ' => 'JWT'
                ];
                $payload = [
                    'username' => $username,
                    'exp' => time() + 3600
                ];
                $token = generate_jwt($headers, $payload);

                /// Envoi de la réponse au Client
                deliver_response(200, "[Auth] POST request : Authentification OK", $token);
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas par défaut
        default:
            /// Envoi de la réponse au Client
            deliver_response(405, "Méthode ". $http_method ." non autorisée", NULL);
            break;
    }
    /**
     * Envoi de la réponse au Client
     * @param int $status Code de statut HTTP
     * @param string $status_message Message de statut HTTP
     * @param array $data Données à retourner au Client
     */
    function deliver_response($status, $status_message, $data)
    {
        /// Paramétrage de l'entête HTTP, suite
        header("HTTP/1.1 $status $status_message");
        /// Paramétrage de la réponse retournée
        $response['status'] = $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;
        /// Mapping de la réponse au format JSON
        $json_response = json_encode($response);
        echo $json_response;
    }