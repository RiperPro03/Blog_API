<?php
    /**
     * Classe ClientREST.
     * Permet d'effectuer des requêtes HTTP GET, POST, PUT, PATCH et DELETE sur une API REST.
     * 
     * Liste des méthodes :
     * - get($id = null) : Effectue une requête HTTP GET sur l'API avec l'ID spécifié.
     * - delete($id = null) : Effectue une requête HTTP DELETE sur l'API avec l'ID spécifié.
     * - post($data) : Effectue une requête HTTP POST sur l'API avec les données spécifiées.
     * - put($id, $data) : Effectue une requête HTTP PUT sur l'API avec l'ID et les données spécifiées.
     * - patch($id, $data) : Effectue une requête HTTP PATCH sur l'API avec l'ID et les données spécifiées.
     * 
     * Exemple d'utilisation :
     * $client = new ClientREST('http://localhost//projet/TP-API/project/api/mon-api/');
     * $result = $client->get();
     * 
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    class ClientREST {
        /**
         * L'URL de l'API.
         * @var string
         * @access private
         */
        private $urlAPI;

        /**
         * Constructeur de la classe ClientREST.
         * Initialise l'URL de l'API.
         * Lève une exception si l'URL de l'API est invalide.
         * 
         * @param string $urlAPI L'URL de l'API.
         * @access public
         */
        public function __construct($urlAPI) {
            $this->urlAPI = $urlAPI;
            if (!filter_var($this->urlAPI, FILTER_VALIDATE_URL)) {
                throw new Exception('L\'URL de l\'API est invalide.');
            }
        }

        /**
         * Effectue une requête HTTP GET sur l'API avec l'ID spécifié.
         *
         * @param int|null $id L'ID de l'élément à récupérer. Si null, retourne tous les éléments.
         * @param string|null $token Le token d'authentification.
         * @return string Le résultat de la requête.
         * @throws Exception Lève une exception si la requête échoue.
         * @access public
         */
        public function get($id = null, $token = null) {
            $headers = array(
                'Content-Type: application/json',
            );
            if ($token !== null) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }
            $reponse = @file_get_contents($this->urlAPI . $id, false,
                stream_context_create(array('http' => array('method' => 'GET',
                                                            'header' => implode("\r\n", $headers)))));

            if ($reponse === false) {
                $error = error_get_last();
                $message = $error['message'];
                $message = substr($message, strrpos($message, ':') + 1);
                throw new Exception($message);
            }
            return $reponse;
        }

        /**
         * Effectue une requête HTTP DELETE sur l'API avec l'ID spécifié.
         *
         * @param int|null $id L'ID de l'élément à supprimer. Si null, supprime tous les éléments.
         * @return string Le résultat de la requête.
         * @throws Exception Lève une exception si la requête échoue.
         * @access public
         */
        public function delete($id = null, $token = null) {
            $headers = array(
                'Content-Type: application/json',
            );
            if ($token !== null) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }
            $reponse = @file_get_contents($this->urlAPI. $id, false,
                stream_context_create(array('http' => array('method' => 'DELETE',
                                                            'header' => implode("\r\n", $headers)))));

            if ($reponse === false) {
                $error = error_get_last();
                $message = $error['message'];
                $message = substr($message, strrpos($message, ':') + 1);
                throw new Exception($message);
            }
            return $reponse;
        }

        /**
         * Effectue une requête HTTP POST sur l'API avec les données spécifiées.
         *
         * @param mixed $data Les données à envoyer dans la requête POST.
         * @return string Le résultat de la requête.
         * @throws Exception Lève une exception si la requête échoue.
         * @access public
         */
        public function post($data, $token = null) {
            $data_string = json_encode($data);
            $headers = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            );
            if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
            }

            $reponse = @file_get_contents($this->urlAPI, false,
                stream_context_create(array('http' => array('method' => 'POST', 
                                                            'content' => $data_string,
                                                            'header' => implode("\r\n", $headers)))));
            
            if ($reponse === false) {
                $error = error_get_last();
                $message = $error['message'];
                $message = substr($message, strrpos($message, ':') + 1);
                throw new Exception($message);
            }
            return $reponse;
        }

        /**
         * Effectue une requête HTTP PUT sur l'API avec l'ID et les données spécifiées.
         *
         * @param int $id L'ID de l'élément à modifier.
         * @param mixed $data Les données à envoyer dans la requête PUT.
         * @return string Le résultat de la requête.
         * @throws Exception Lève une exception si la requête échoue.
         * @access public
         */
        public function put($id, $data, $token = null) {
            $data_string = json_encode($data);
            $headers = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            );
            if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
            }

            $reponse = @file_get_contents($this->urlAPI . $id, false,
                stream_context_create(array('http' => array('method' => 'PUT', 'content' => $data_string,
                    'header' => implode("\r\n", $headers)))));

            if ($reponse === false) {
                $error = error_get_last();
                $message = $error['message'];
                $message = substr($message, strrpos($message, ':') + 1);
                throw new Exception($message);
            }
            return $reponse;
        }

        /**
         * Effectue une requête HTTP PATCH sur l'API avec l'ID et les données spécifiées.
         *
         * @param int $id L'ID de l'élément à modifier.
         * @param mixed $data Les données à envoyer dans la requête PATCH.
         * @return string Le résultat de la requête.
         * @throws Exception Lève une exception si la requête échoue.
         * @access public
         */
        public function patch($id, $data, $token = null) {
            $data_string = json_encode($data);
            $reponse = @file_get_contents($this->urlAPI . $id, false,
                stream_context_create(array('http' => array('method' => 'PATCH', 'content' => $data_string,
                    'header' => array('Content-Type: application/json' . "\r\n" . 'Content-Length: ' . strlen($data_string) . "\r\n")))));

            if ($reponse === false) {
                $error = error_get_last();
                $message = $error['message'];
                $message = substr($message, strrpos($message, ':') + 1);
                throw new Exception($message);
            }
            return $reponse;
        }
    }