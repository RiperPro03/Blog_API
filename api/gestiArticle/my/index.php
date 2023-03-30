<?php
    /**
     * API REST pour la gestion des likes et dislikes des articles
     * Affichage, création, modification et suppression des likes et dislikes des articles
     * Méthodes HTTP : GET, POST, PUT, DELETE
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */

    /// Récupération de la biliothèque JWT
    require_once '../../../model/jwt_utils.php';

    /// Connexion à la base de données
    require_once '../../../model/ConnexionDB.php';
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

                        /// Vérification de la validité du token
                        $bearer_token = get_bearer_token();
                        validation_token($bearer_token, $http_method);

                        /// Vérification du role de l'utilisateur publisher
                        if(get_role_token($bearer_token) !== "publisher") {
                            deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                            exit;
                        }

                        $q = $db->prepare("SELECT p.id_post, p.title, p.content, p.date_ajout, p.date_modif, p.id_user,
                                            SUM(CASE WHEN a.is_like = 1 THEN 1 ELSE 0 END) as nb_like,
                                            SUM(CASE WHEN a.is_like = 0 THEN 1 ELSE 0 END) AS nb_dislike
                                            FROM posts p 
                                            LEFT JOIN aimer a ON p.id_post = a.id_post
                                            WHERE p.id_post = :id_post
                                            AND p.id_user = :id_user
                                            GROUP BY p.id_post");
                        $q->execute([
                            'id_post' => $id, 
                            'id_user' => get_id_token($bearer_token)
                        ]);

                        /// Traitement des données
                        $matchingData = new ArrayObject();
                        foreach ($q->fetchAll() as $post) {
                            $s = $db->prepare("SELECT id_user, username FROM users WHERE id_user = :id_user");
                            $s->execute(['id_user' => $post['id_user']]);

                            $post['author'] = $s->fetchAll();

                            unset($post['id_user']);

                            $matchingData->append($post);
                        }

                    } else {
                        throw new Exception("[". API_NAME ."] ". $http_method ." request : Aucun post ne correspond à cet identifiant.");
                    }
                    
                } else {

                    /// Vérification de la validité du token
                    $bearer_token = get_bearer_token();
                    validation_token($bearer_token, $http_method);

                    /// Vérification du role de l'utilisateur publisher
                    if(get_role_token($bearer_token) !== "publisher") {
                        deliver_response(403, "[". API_NAME ."] ". $http_method ." request : Erreur vous n'avez pas les droits pour accéder à cette ressource.", NULL);
                        exit;
                    }

                    $q = $db->prepare("SELECT p.id_post, p.title, p.content, p.date_ajout, p.date_modif, p.id_user,
                                        SUM(CASE WHEN a.is_like = 1 THEN 1 ELSE 0 END) as nb_like,
                                        SUM(CASE WHEN a.is_like = 0 THEN 1 ELSE 0 END) AS nb_dislike
                                        FROM posts p 
                                        LEFT JOIN aimer a ON p.id_post = a.id_post
                                        WHERE p.id_user = :id_user
                                        GROUP BY p.id_post");
                    $q->execute(['id_user' => get_id_token($bearer_token)]);

                    /// Traitement des données
                    $matchingData = new ArrayObject();
                    foreach ($q->fetchAll() as $post) {
                        $s = $db->prepare("SELECT id_user, username FROM users WHERE id_user = :id_user");
                        $s->execute(['id_user' => $post['id_user']]);

                        $post['author'] = $s->fetchAll();

                        unset($post['id_user']);

                        $matchingData->append($post);
                    }
                }
                /// Envoi de la réponse au Client
                deliver_response(200, "[". API_NAME ."] ". $http_method ." request : Read Data OK", $matchingData);
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