<?php
    /**
     * Class Article
     * 
     * 
     * @author Christopher ASIN <https://github.com/RiperPro03>
     * @author Henri JEZEQUEL <https://github.com/HenriJez>
     */
    class Article {
        private $id_Pub;
        private $auteur;
        private $datePubli;
        private $titre;
        private $contenu;
        private $nbLike; 
        private $nbDislike;
    
        /**
         * Constructeur de la classe Article.
         * Initialise les attributs de la classe.
         * @param int $id_Pub identifiant de l'article
         * @param string $auteur Auteur de l'article
         * @param string $datePubli Date de publication de l'article
         * @param string $titre Titre de l'article
         * @param string $contenu Contenu de l'article
         * @param int $nbLike nombre de like sur l'article
         * @param int $nbDislike nombre de dislike sur l'article
         * @access public
         */
        public function __construct($id_Pub,$auteur,$datePubli,$titre,$contenu,$nbLike,$nbDislike) {
            $this->id_Pub = $id_Pub;
            $this->auteur = $auteur;
            $this->datePubli = $datePubli;
            $this->titre = $titre;
            $this->contenu = $contenu;
            $this->nbLike = $nbLike;
            $this->nbDislike = $nbDislike;
        }


        /**
         * Retourne la carte HTML de l'article pour les personnes non authentifie
         * @return string Carte HTML de l'article
         * @access public
         */
        public function getCartePostNonAuthentifie() {
            return '"<div class="Card-Main-Box">
                        <div class="Post-header">
                            <div class="Title">
                                <h3>'.$this->titre.'</h3>
                            </div>
                            <div class="Post-author"><p>'.$this->auteur.'</p>
                            </div>
                        </div>
                        
                        <div class="Post-content">
                            <label>Message:</label>
                            <p>'.$this->contenu.'</p>
                        </div>
                        <div class="Post-Footer">
                            <div class="Post-Date">
                                <label>Date de publication:</label>
                                <p>'.$this->datePubli.'</p>
                            </div>
                        </div>"';
         }

         /**
         * Retourne la carte HTML de l'article pour les personnes avec le role publisher
         * @return string Carte HTML de l'article
         * @access public
         */
         public function getCartePostLikeDislike() {
            return '"<div class="Card-Main-Box">
                        <div class="Post-header">
                            <div class="Title">
                                <h3>'.$this->titre.'</h3>
                            </div>
                            <div class="Post-author"><p>'.$this->auteur.'</p>
                            </div>
                        </div>
                        <div class="Post-content">
                            <label>Message:</label>
                            <p>'.$this->contenu.'</p>
                        </div>
                        <div class="Post-Footer">
                            <div class="Post-Date">
                                <label>Date de publication:</label>
                                <p>'.$this->datePubli.'</p>
                            </div>
                            <div class="Like-Dislike-Button">
                                <p class="Like-vote">'.$this->nbLike.' Like</p>
                                <p class="Dislike-vote">'.$this->nbDislike.' Dislike</p>
                                <a href="../like.php?id_pub='.$this->id_Pub.'" class="Like">Like</a>
                                <a href="../dislike.php?id_pub='.$this->id_Pub.'" class="Dislike">Dislike</a>
                            </div>
                        </div>
                    </div>"';
         }

         /**
         * Retourne la carte HTML de l'article pour les articles publier par l'utilisateur actuel et pour les modérateurs
         * @return string Carte HTML de l'article
         * @access public
         */
         public function getCartePostAllRight() {
            return '"<div class="Card-Main-Box">
                        <div class="Card-Option">
                            <a href="" class="Modifier">Modifier</a>
                            <a href="" class="Supprimer">Supprimer</a>
                        </div>
                        <div class="Post-header">
                            <div class="Title">
                                <h3>'.$this->titre.'</h3>
                            </div>
                            <div class="Post-author"><p>'.$this->auteur.'</p>
                            </div>
                        </div>
                        <div class="Post-content">
                            <label>Message:</label>
                            <p>'.$this->contenu.'</p>
                        </div>
                        <div class="Post-Footer">
                            <div class="Post-Date">
                                <label>Date de publication:</label>
                                <p>'.$this->datePubli.'</p>
                            </div>
                            <div class="Like-Dislike-Button">
                                <p class="Like-vote">'.$this->nbLike.' Like</p>
                                <p class="Dislike-vote">'.$this->nbDislike.' Dislike</p>
                                <a href="../like.php?id_pub='.$this->id_Pub.'" class="Like">Like</a>
                                <a href="../dislike.php?id_pub='.$this->id_Pub.'" class="Dislike">Dislike</a>
                            </div>
                        </div>
                    </div>"';
         }
    }

?>