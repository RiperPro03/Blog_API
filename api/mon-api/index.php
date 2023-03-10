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
        /// Cas de la méthode GET
        case "GET":
            /// Récupération des critères de recherche envoyés par le Client
            try {
                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    
                    if (isId($id, $db, "chuckn_facts")) {
                        $q = $db->prepare("SELECT * FROM chuckn_facts WHERE id = :id");
                        $q->execute(['id' => $id]);
                        $matchingData = $q->fetch();
                    } else {
                        throw new Exception("[MON API REST] GET request : Aucune phrase ne correspond à cet identifiant.");
                    }
                    
                } else {
                    /// Vérification de la validité du token
                    $bearer_token = '';
                    $bearer_token = get_bearer_token();
                    if ($bearer_token !== null) {
                        if (!(is_jwt_valid($bearer_token))) throw new Exception("[MON API REST] GET request : Erreur token invalide.");
                    } else {
                        throw new Exception("[MON API REST] GET request : Erreur token invalide.");
                    }
                    /// Vérification du role de l'utilisateur
                    if(get_role($bearer_token) !== "moderator") throw new Exception("[MON API REST] GET request : Erreur vous n'avez pas les droits pour accéder à cette ressource.");

                    $q = $db->prepare("SELECT * FROM chuckn_facts");
                    $q->execute();
                    $matchingData = $q->fetchAll();

                    $matchingData = [
                        'role' => get_role($bearer_token),
                        'token' => $bearer_token,
                        'data' => $matchingData
                    ];

                    
                }
                /// Envoi de la réponse au Client
                deliver_response(200, "[MON API REST] GET request : Read Data OK", $matchingData);
            } catch (Exception $e) {
                deliver_response(404, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode POST
        case "POST":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[MON API REST] POST request : Erreur lors de la récupération des données.");
                
                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[MON API REST] POST request : Erreur lors du décodage des données.");
                
                $postedData = $json_data;
                if (!isset($postedData['phrase'])) throw new Exception("[MON API REST] POST request : Erreur vous devez donner une clé phrase.");
                //Eviter la faille XSS
                $phrase = htmlspecialchars($postedData['phrase'], ENT_QUOTES, 'UTF-8');

                $q = $db->prepare("INSERT INTO chuckn_facts (phrase, date_ajout) 
                                    VALUE (:phrase, :date_ajout)");
                $q->execute([
                    'phrase' => $phrase,
                    'date_ajout' => date('Y-m-d H:i:s')
                ]);
                
                $matchingData = lastData($db, "chuckn_facts");

                /// Envoi de la réponse au Client
                deliver_response(201, "[MON API REST] POST request : enregistrement ok", $matchingData);
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode PATCH
        case "PATCH":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[MON API REST] PATCH request : Erreur lors de la récupération des données.");

                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[MON API REST] PATCH request : Erreur lors du décodage des données.");

                //Eviter la faille XSS
                $postedData = $json_data;
                if (!isset($postedData['phrase']) && !isset($postedData['vote']) && !isset($postedData['faute']) && !isset($postedData['signalement'])) {
                    throw new Exception("[MON API REST] PATCH request : Erreur vous devez donner au moins une des clés suivantes : phrase, vote, faute, signalement.");
                }
                
                //Eviter la faille XSS
                $phrase = isset($postedData['phrase']) ? htmlspecialchars($postedData['phrase'], ENT_QUOTES, 'UTF-8') : null;
                $vote = isset($postedData['vote']) ? htmlspecialchars($postedData['vote'], ENT_QUOTES, 'UTF-8') : null;
                $faute = isset($postedData['faute']) ? htmlspecialchars($postedData['faute'], ENT_QUOTES, 'UTF-8') : null;
                $signalement = isset($postedData['signalement']) ? htmlspecialchars($postedData['signalement'], ENT_QUOTES, 'UTF-8') : null;

                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (isId($id, $db, "chuckn_facts")) {
                        $sql = "UPDATE chuckn_facts SET";
                        $parametre = [];

                        if (isset($phrase)) {
                            $sql .= " phrase = :phrase,";
                            $parametre['phrase'] = $phrase;
                        }

                        if (isset($vote)) {
                            $sql .= " vote = :vote,";
                            $parametre['vote'] = $vote;
                        }

                        if (isset($faute)) {
                            $sql .= " faute = :faute,";
                            $parametre['faute'] = $faute;
                        }

                        if (isset($signalement)) {
                            $sql .= " signalement = :signalement,";
                            $parametre['signalement'] = $signalement;
                        }

                        $sql = rtrim($sql, ', ');
                        $sql .= ", date_modif = :date_modif WHERE id = :id";
                        $parametre['id'] = $id;
                        $parametre['date_modif'] = date('Y-m-d H:i:s');

                        var_dump($sql);
                        var_dump($parametre);

                        $q = $db->prepare($sql);
                        $q->execute($parametre);


                        $c = $db->prepare("SELECT * FROM chuckn_facts WHERE id = :id");
                        $c->execute(['id' => $id]);
                        $matchingData = $c->fetch();

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[MON API REST] PATCH request : mise à jour ok", $matchingData);
                    } else {
                        throw new Exception("[MON API REST] PATCH request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[MON API REST] PATCH request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        case "PUT":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[MON API REST] PUT request : Erreur lors de la récupération des données.");

                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[MON API REST] PUT request : Erreur lors du décodage des données.");

                //Eviter la faille XSS
                $postedData = $json_data;
                if (!isset($postedData['phrase']) || !isset($postedData['vote']) || !isset($postedData['faute']) || !isset($postedData['signalement'])) {
                    throw new Exception("[MON API REST] PUT request : Erreur vous devez donner les clés suivantes : phrase, vote, faute, signalement.");
                }
                
                //Eviter la faille XSS
                $phrase = htmlspecialchars($postedData['phrase'], ENT_QUOTES, 'UTF-8');
                $vote = htmlspecialchars($postedData['vote'], ENT_QUOTES, 'UTF-8');
                $faute = htmlspecialchars($postedData['faute'], ENT_QUOTES, 'UTF-8');
                $signalement = htmlspecialchars($postedData['signalement'], ENT_QUOTES, 'UTF-8');

                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (isId($id, $db, "chuckn_facts")) {
                        $q = $db->prepare('UPDATE chuckn_facts SET phrase = :phrase, 
                                                                    vote = :vote, 
                                                                    faute = :faute, 
                                                                    signalement = :signalement, 
                                                                    date_modif = :date_modif 
                                                                    WHERE id = :id');
                        $q->execute([
                            'phrase' => $phrase,
                            'vote' => $vote,
                            'faute' => $faute,
                            'signalement' => $signalement,
                            'date_modif' => date('Y-m-d H:i:s'),
                            'id' => $id
                        ]);

                        $c = $db->prepare("SELECT * FROM chuckn_facts WHERE id = :id");
                        $c->execute(['id' => $id]);
                        $matchingData = $c->fetch();

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[MON API REST] PUT request : mise à jour ok", $matchingData);
                    } else {
                        throw new Exception("[MON API REST] PUT request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[MON API REST] PUT request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode DELETE
        case "DELETE":
            try {
                /// Récupération de l'identifiant de la ressource envoyé par le Client
                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (isId($id, $db,"chuckn_facts")) {
                        /// Vérification de la validité du token
                        $bearer_token = '';
                        $bearer_token = get_bearer_token();
                        if ($bearer_token !== null) {
                            if (!(is_jwt_valid($bearer_token))) throw new Exception("[MON API REST] DELETE request : Erreur token invalide.");
                        } else {
                            throw new Exception("[MON API REST] DELETE request : Erreur token invalide.");
                        }
                        /// Vérification du role de l'utilisateur
                        if(get_role($bearer_token) !== "admin") throw new Exception("[MON API REST] DELETE request : Erreur vous n'avez pas les droits pour accéder à cette ressource.");

                        $q = $db->prepare("DELETE FROM chuckn_facts WHERE id = :id");
                        $q->execute(['id' => $id]);

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[MON API REST] DELETE request : OK", NULL);
                    } else {
                        throw new Exception("[MON API REST] DELETE request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[MON API REST] DELETE request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(404, $e->getMessage(), NULL);
            }
            
            break;
        default:
            /// Envoi de la réponse au Client
            deliver_response(405, "Méthode ". $http_method ." non autorisée", NULL);
            break;
    }
    /**
     * Envoi de la réponse au Client
     * @param int $status Le code de statut HTTP
     * @param string $status_message Le message du statut HTTP
     * @param array $data Les données à retourner
     */
    function deliver_response($status, $status_message, $data) {
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

    /**
     * Vérifie si l'id existe dans la base de données dans une table définie
     * @param int $id Identifiant de la ressource
     * @param PDO $db Le lien de connexion à la base de données
     * @param string $table La table dans laquelle on vérifie l'id
     * @return bool Vrai si l'id existe, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function isId($id, $db, $table) {
        $c = $db->prepare("SELECT id FROM ". $table ." WHERE id = :id");
        $c->execute(['id' => $id]);
        $nbId = $c->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Récupère les données de la dernière insertion dans une table définie
     * @param PDO $db Le lien de connexion à la base de données
     * @return array Les données de la dernière insertion
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function lastData($db, $table) {
        $c = $db->prepare("SELECT * FROM ". $table ." WHERE id = :id");
        $c->execute(['id' => $db->lastInsertId()]);
        return $c->fetch();
    }

    /**
     * Récupérer le rôle d'un utilisateur à partir de son token JWT
     * @param string $token Le token de l'utilisateur
     * @return string Le rôle de l'utilisateur
     */
    function get_role($token) {
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $role = json_decode($payload)->role;
        return $role;
    }