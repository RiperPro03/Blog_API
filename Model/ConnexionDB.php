<?php
    /**
     * Classe ConnexionDB.
     * Permet d'établir une connexion avec la base de données
     * 
     * Liste des méthodes :
     * - getInstance() : Récupère l'instance unique de la classe ConnexionDB
     * 
     * Exemple d'utilisation :
     * $connexion = ConnexionDB::getInstance();
     * 
     * @author Christopher ASIN <https://github.com/RiperPro03>
     */
    class ConnexionDB {
        /**
         * Instance unique de la classe
         * @var null|ConnexionDB
         * @access private
         */
        private static $instance = null;

        /**
         * Nom d'hôte de la base de données
         * @var string
         * @access private
         */
        private $HOST = "localhost";

        /**
         * Nom d'utilisateur de la base de données
         * @var string
         * @access private
         */
        private $USER = "root";

        /**
         * Mot de passe de la base de données
         * @var string
         * @access private
         */
        private $PASS = "";

        /**
         * Nom de la base de données
         * @var string
         * @access private
         */
        private $DB_NAME = "projet_r401";

        /**
         * Objet de connexion PDO
         * @var PDO
         * @access private
         */
        private $db;

        /**
         * Constructeur privé pour empêcher l'instanciation directe de la classe
         * Initialise la connexion à la base de données
         * @access private
         */
        private function __construct() {
            try {
                $this->db = new PDO('mysql:host='. $this->HOST .';dbname='. $this->DB_NAME .';charset=utf8', $this->USER, $this->PASS);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                echo '<script>alert("Erreur de connetion à la base de donneés : '. $e .'");</script>';
                exit;
            }
        }

        /**
         * Méthode statique pour récupérer l'instance unique de la classe ConnexionDB
         * Si l'instance n'existe pas, la crée
         * @return PDO
         * @access public
         */
        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new ConnexionDB();
            }
            return self::$instance->db;
        }
    }
?>
