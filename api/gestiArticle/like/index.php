<?php
    /**
     * API REST pour la gestion des likes et dislikes des articles
     * Affichage, création, modification et suppression des likes et dislikes des articles
     * Méthodes HTTP : GET, POST, PUT, DELETE
     * @author Christopher ASIN <https://github.com/RiperPro03>
     * @author Henri JEZEQUEL <https://github.com/HenriJez>
     * @author Anthony LOZANO <https://github.com/Anthooooooo>
     */

    /// Récupération de la biliothèque JWT
    require_once '../../../model/jwt_utils.php';

    /// Connexion à la base de données
    require_once '../../../model/ConnexionDB.php';
    $db = ConnexionDB::getInstance();
    
    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Déclaration des constantes
    const API_NAME = "likeAPI";
    const SECRET_KEY = "iutinfo2023";

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method) {
        /// Cas de la méthode GET
        case "GET":
            /// Récupération des critères de recherche envoyés par le Client
            try {
                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (moderator)
                if(get_role_token($bearer_token) !== "moderator") {
                    deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                    exit;
                }

                /// Vérification de l'existence du paramètre id_post
                if (!empty($_GET['id'])) {
                    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    
                    if (is_Id($id, $db)) {
                        $q = $db->prepare("SELECT * FROM aimer WHERE id_post = :id_post");
                        $q->execute(['id_post' => $id]);
                        $matchingData = $q->fetchAll();
                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucun post ne correspond à cet identifiant.");
                    }
                    
                } else {
                    $q = $db->prepare("SELECT * FROM aimer");
                    $q->execute();
                    $matchingData = $q->fetchAll();
                }
                /// Envoi de la réponse au Client
                deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Read Data OK", $matchingData);
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
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
                if (!isset($postedData['is_like'])) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner une clé is_like.");

                //Eviter la faille XSS
                $is_like = htmlspecialchars($postedData['is_like'], ENT_QUOTES, 'UTF-8');

                /// Vérifier si is_like est égalent à 1 ou 0
                if ($is_like != 1 && $is_like != 0) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner une clé is_like égale à 1 ou 0.");

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") {
                    deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                    exit;
                }

                /// Vérification de l'existence du paramètre id_post
                if (!empty($_GET['id'])) {

                    $id_post = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    $id_user = get_id_token($bearer_token);

                    /// Vérification de l'existence de l'id_user et id_post dans la base de données
                    if (is_id_in($id_user, $id_post, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous avez déjà liké ou disliké cette article.");
                    if (!exist_id_post($id_post, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un un post existant (clé id_post n'existe pas).");
                    if (!exist_id_user($id_user, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un un utilisateur existant (clé id_user n'existe pas).");
                    
                    $q = $db->prepare("INSERT INTO aimer (id_user, id_post, is_like) 
                                    VALUE (:id_user, :id_post, :is_like)");
                    $q->execute([
                        'id_user' => $id_user,
                        'id_post' => $id_post,
                        'is_like' => $is_like,
                    ]);
                
                    $matchingData = lastDataId($db, $id_user, $id_post);

                    /// Envoi de la réponse au Client
                    deliver_response(201, "[". API_NAME ."] ". $http_method ." request : Appreciations enregistrement ok", $matchingData);

                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un id_post en paramètre.");
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

                $postedData = $json_data;
                if (!isset($postedData['is_like'])) {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner la clé is_like.");
                }

                //Eviter la faille XSS
                $is_like = htmlspecialchars($postedData['is_like'], ENT_QUOTES, 'UTF-8');

                /// Vérifier si is_like est égalent à 1 ou 0
                if ($is_like != 1 && $is_like != 0) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner une clé is_like égale à 1 ou 0.");

                /// Vérification de la validité du token
                $bearer_token = get_bearer_token();
                validation_token($bearer_token, $http_method);
                
                /// Vérification du role de l'utilisateur (publisher ou moderator)
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") {
                    deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                    exit;
                }

                /// Vérification de l'existence du paramètre id_post
                if (!empty($_GET['id'])) {

                    $id_post = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    $id_user = get_id_token($bearer_token);

                    /// Vérification de l'existence de l'id_user et id_post dans la base de données
                    if (!is_id_in($id_user, $id_post, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas encore liké ou disliké ce post.");
                    if (!exist_id_post($id_post, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un post existant (clé id_post n'existe pas).");
                    if (!exist_id_user($id_user, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un utilisateur existant (clé id_user n'existe pas).");

                    $q = $db->prepare('UPDATE aimer SET is_like = :is_like
                                                    WHERE id_post = :id_post
                                                    AND id_user = :id_user');
                    $q->execute([
                        'id_user' => $id_user,
                        'id_post' => $id_post,
                        'is_like' => $is_like,
                    ]);

                    $c = $db->prepare("SELECT * FROM aimer WHERE id_post = :id_post AND id_user = :id_user");
                    $c->execute(['id_post' => $id_post, 'id_user' => $id_user]);
                    $matchingData = $c->fetch();

                    /// Envoi de la réponse au Client
                    deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Appreciations mise à jour ok", $matchingData);

                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un id_post en paramètre.");
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
                if(get_role_token($bearer_token) !== "publisher" && get_role_token($bearer_token) !== "moderator") {
                    deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                    exit;
                }

                /// Vérification de l'existence du paramètre id_post
                if (!empty($_GET['id'])) {

                    $id_post = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                    $id_user = get_id_token($bearer_token);

                    /// Vérification de l'existence de l'id_user et id_post dans la base de données
                    if (!is_id_in($id_user, $id_post, $db)) throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas encore liké ou disliké ce post.");

                    /// Récupération des données de la ressource à supprimer
                    $c = $db->prepare("SELECT * FROM aimer WHERE id_post = :id_post AND id_user = :id_user");
                    $c->execute(['id_post' => $id_post, 'id_user' => $id_user]);
                    $matchingData = $c->fetch();
                    
                    /// Suppression de la ressource
                    $q = $db->prepare("DELETE FROM aimer WHERE id_post = :id_post AND id_user = :id_user");
                    $q->execute(['id_post' => $id_post, 'id_user' => $id_user]);

                    /// Envoi de la réponse au Client
                    deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Appreciations supprimé OK : ", $matchingData);

                } else {
                    throw new Exception("[". API_NAME ."] ". $http_method ." request : Erreur vous devez donner un id_post en paramètre.");
                }
            } catch (Exception $e) {
                deliver_response(400, $e->getMessage(), NULL);
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
     * Vérifie si l'id existe dans la base de données dans la table aimer
     * @param int $id Identifiant de la ressource
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si l'id existe, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function is_Id($id, $db) {
        $c = $db->prepare("SELECT id_post FROM aimer WHERE id_post = :id_post");
        $c->execute(['id_post' => $id]);
        $nbId = $c->rowCount();

        if ($nbId >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Récupère les données de la dernière insertion dans la table posts
     * @param PDO $db Le lien de connexion à la base de données
     * @param int $id_user Identifiant de l'utilisateur
     * @param int $id_post Identifiant du post
     * @return array Les données de la dernière insertion
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function lastDataId($db, $id_user, $id_post) {
        $c = $db->prepare("SELECT * FROM aimer WHERE id_post = :id_post and id_user = :id_user");
        $c->execute(['id_post' => $id_post, 'id_user' => $id_user]);
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
            deliver_response(401, "[". API_NAME ."] ". $http_method ." request : Erreur token a expiré ou est invalide.", NULL);
            exit;
        }
    }

    /**
     * Vérifie si l'utilisateur a déjà liké le post
     * @param int $id_user L'identifiant de l'utilisateur
     * @param int $id_post L'identifiant du post
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si l'utilisateur a déjà liké le post, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function is_id_in($id_user, $id_post, $db) {
        $q = $db->prepare("SELECT * FROM aimer WHERE id_user = :id_user AND id_post = :id_post");
        $q->execute(['id_user' => $id_user, 'id_post' => $id_post]);
        $nbId = $q->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vérifie si le post existe
     * @param int $id_post L'identifiant du post
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si le post existe, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function exist_id_post($id_post, $db) {
        $q = $db->prepare("SELECT * FROM posts WHERE id_post = :id_post");
        $q->execute(['id_post' => $id_post]);
        $nbId = $q->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur existe
     * @param int $id_user L'identifiant de l'utilisateur
     * @param PDO $db Le lien de connexion à la base de données
     * @return bool Vrai si l'utilisateur existe, faux sinon
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    function exist_id_user($id_user, $db) {
        $q = $db->prepare("SELECT * FROM users WHERE id_user = :id_user");
        $q->execute(['id_user' => $id_user]);
        $nbId = $q->rowCount();

        if ($nbId == 1) {
            return true;
        } else {
            return false;
        }
    }