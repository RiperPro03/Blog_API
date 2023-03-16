<?php
    /// Récupération de la biliothèque JWT
    require_once '../../model/jwt_utils.php';

    /// Connexion à la base de données
    require_once '../../model/ConnexionDB.php';
    $db = ConnexionDB::getInstance();
    
    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Déclaration des constantes
    const API_NAME = "gestiArticle";
    const SECRET_KEY = "iutinfo2023";

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method) {
        /// Cas de la méthode GET
        case "GET":
            /// Récupération des critères de recherche envoyés par le Client
            try {
                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    
                    if (is_Id($id, $db)) {
                        $q = $db->prepare("SELECT * FROM posts WHERE id_user = :id_user");
                        $q->execute(['id_user' => $id]);
                        $matchingData = $q->fetch();
                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucun post ne correspond à cet identifiant.");
                    }
                    
                } else {
                    $q = $db->prepare("SELECT * FROM posts");
                    $q->execute();
                    $matchingData = $q->fetchAll();
                }
                /// Envoi de la réponse au Client
                deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Read Data OK", $matchingData);
            } catch (Exception $e) {
                deliver_response(404, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode POST
        case "POST":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors de la récupération des données.");
                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors du décodage des données.");
                
                $postedData = $json_data;
                if (!isset($postedData['title'])) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner une clé title.");
                if (!isset($postedData['content'])) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner une clé content.");
                //Eviter la faille XSS
                $title = htmlspecialchars($postedData['title'], ENT_QUOTES, 'UTF-8');
                $content = htmlspecialchars($postedData['content'], ENT_QUOTES, 'UTF-8');

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") 
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                $q = $db->prepare("INSERT INTO posts (title, contenu, date_ajout, id_user) 
                                    VALUE (:title, :contenu, :date_ajout, :id_user)");
                $q->execute([
                    'title' => $title,
                    'contenu' => $content,
                    'date_ajout' => date('Y-m-d H:i:s'),
                    'id_user' => get_id_token($bearer_token)
                ]);
                
                $matchingData = lastData($db, "posts");

                /// Envoi de la réponse au Client
                deliver_response(201, "[". API_NAME ."] ". $http_method ." request : enregistrement ok", $matchingData);
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode PATCH
        case "PATCH":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors de la récupération des données.");
                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors du décodage des données.");

                //Eviter la faille XSS
                $postedData = $json_data;
                if (!isset($postedData['title']) && !isset($postedData['content'])) {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner au moins une des clés suivantes : title, content.");
                }
                
                //Eviter la faille XSS
                $title = isset($postedData['title']) ? htmlspecialchars($postedData['title'], ENT_QUOTES, 'UTF-8') : null;
                $content = isset($postedData['content']) ? htmlspecialchars($postedData['content'], ENT_QUOTES, 'UTF-8') : null;

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") 
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (is_Id($id, $db)) {
                        /// Vérification de l'appartenance de l'utilisateur au post pour les publishers
                        if(!is_owner($bearer_token, $db, $id) && get_role_token($bearer_token) === "publisher")
                            throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                        $sql = "UPDATE posts SET";
                        $parametre = [];

                        if (isset($title)) {
                            $sql .= " title = :title,";
                            $parametre['title'] = $title;
                        }

                        if (isset($content)) {
                            $sql .= " content = :content,";
                            $parametre['content'] = $content;
                        }

                        $sql = rtrim($sql, ', ');
                        $sql .= ", date_modif = :date_modif WHERE id_post = :id_post";
                        $parametre['id_post'] = $id;
                        $parametre['date_modif'] = date('Y-m-d H:i:s');

                        var_dump($sql);
                        var_dump($parametre);

                        $q = $db->prepare($sql);
                        $q->execute($parametre);


                        $c = $db->prepare("SELECT * FROM posts WHERE id_post = :id_post");
                        $c->execute(['id_post' => $id]);
                        $matchingData = $c->fetch();

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[". API_NAME ."] ". $http_method ." request : mise à jour ok", $matchingData);
                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode PUT
        case "PUT":
            try {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                if ($postedData === false) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors de la récupération des données.");
                $json_data = json_decode($postedData, true);
                if (is_null($json_data)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur lors du décodage des données.");

                //Eviter la faille XSS
                $postedData = $json_data;
                if (!isset($postedData['title']) || !isset($postedData['content'])) {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner les clés suivantes : title, content.");
                }
                $title = htmlspecialchars($postedData['title'], ENT_QUOTES, 'UTF-8');
                $content = htmlspecialchars($postedData['content'], ENT_QUOTES, 'UTF-8');

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") 
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");
                
                

                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (is_Id($id, $db)) {
                        /// Vérification de l'appartenance de l'utilisateur au post pour les publishers
                        if(!is_owner($bearer_token, $db, $id) && get_role_token($bearer_token) === "publisher")
                            throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                        $q = $db->prepare('UPDATE posts SET title = :title, 
                                                            content = :content, 
                                                            date_modif = :date_modif
                                                            WHERE id_post = :id_post');
                        $q->execute([
                            'title' => $title,
                            'content' => $content,
                            'id_post' => $id,
                            'date_modif' => date('Y-m-d H:i:s')
                        ]);

                        $c = $db->prepare("SELECT * FROM posts WHERE id_post = :id_post");
                        $c->execute(['id_post' => $id]);
                        $matchingData = $c->fetch();

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[". API_NAME ."] ". $http_method ." request : mise à jour ok", $matchingData);
                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
            }
            break;
        /// Cas de la méthode DELETE
        case "DELETE":
            try {

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") 
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                /// Récupération de l'identifiant de la ressource envoyé par le Client
                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

                    if (is_Id($id, $db)) {

                        /// Vérification de l'appartenance de l'utilisateur au post pour les publishers
                        if(!is_owner($bearer_token, $db, $id) && get_role_token($bearer_token) === "publisher")
                            throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à ce post.");

                        /// Récupération des données de la ressource à supprimer
                        $c = $db->prepare("SELECT * FROM posts WHERE id_post = :id_post");
                        $c->execute(['id_post' => $id]);
                        $matchingData = $c->fetch();
                        
                        /// Suppression de la ressource
                        $q = $db->prepare("DELETE FROM posts WHERE id_post = :id_post");
                        $q->execute(['id_post' => $id]);

                        /// Envoi de la réponse au Client
                        deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Post supprimé OK : ", $matchingData);
                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucune phrase ne correspond à cet identifiant.");
                    }
                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Identifiant id est requis.");
                }
            } catch (Exception $e) {
                deliver_response(404, $e->getMessage(), NULL);
            }
            
            break;
        default:
            /// Envoi de la réponse au Client
            deliver_response(405, "[". API_NAME ."] Méthode ". $http_method ." non autorisée", NULL);
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
     * Vérifie si l'id existe dans la base de données dans la table posts
     * @param int $id Identifiant de la ressource
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si l'id existe, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function is_Id($id, $db) {
        $c = $db->prepare("SELECT id_post FROM post WHERE id_post = :id_post");
        $c->execute(['id_post' => $id]);
        $nbId = $c->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Récupère les données de la dernière insertion dans la table posts
     * @param PDO $db Le lien de connexion à la base de données
     * @return array Les données de la dernière insertion
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function lastData($db) {
        $c = $db->prepare("SELECT * FROM posts WHERE id_post = :id_post");
        $c->execute(['id_post' => $db->lastInsertId()]);
        return $c->fetch();
    }

    /**
     * Récupérer le rôle d'un utilisateur à partir de son token JWT
     * @param string $token Le token de l'utilisateur
     * @return string Le rôle de l'utilisateur
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function get_role_token($token) {
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $role = json_decode($payload)->role;
        return $role;
    }

    /**
     * Récupérer le nom d'utilisateur d'un utilisateur à partir de son token JWT
     * @param string $token Le token de l'utilisateur
     * @return string Le nom d'utilisateur de l'utilisateur
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function get_username_token($token) {
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $username = json_decode($payload)->username;
        return $username;
    }

    /**
     * Récupérer l'identifiant d'un utilisateur à partir de son token JWT
     * @param string $token Le token de l'utilisateur
     * @return string L'identifiant de l'utilisateur
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function get_id_token($token) {
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $id = json_decode($payload)->id_user;
        return $id;
    }

    /**
     * Vérifie si le token JWT est valide
     * @param string $token Le token JWT
     * @param string $http_method La méthode HTTP
     * @throws Exception Si le token n'est pas valide
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function validation_token($bearer_token, $http_method) {
        if ($bearer_token !== null && is_jwt_valid($bearer_token, SECRET_KEY)) {
            return true;
        } else {
            throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur token invalide.");
        }
    }

    /**
     * Vérifie si le post appartient à l'utilisateur
     * @param string $token Le token JWT
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si le post appartient à l'utilisateur, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function is_owner($token, $db, $id_post) {
        $q = $db->prepare("SELECT id_user FROM post WHERE id_user = :id_user AND id_post = :id_post");
        $q->execute(['id_user' => get_id_token($token), 'id_post' => $id_post]);
        $nbId = $q->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }