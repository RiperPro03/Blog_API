<?php
    /**
     * Class Article
     * 
     * 
     * @author Christopher ASIN <https://github.com/RiperPro03>
     * @author Henri JEZEQUEL <https://github.com/HenriJez>
     */
    class Article {

        private $auteur;
        private $datePubli;
        private $titre;
        private $contenu;
    
        /**
         * Constructeur de la classe Article.
         * Initialise les attributs de la classe.
         * @param string $auteur Auteur de l'article
         * @param string $datePubli Date de publication de l'article
         * @param string $titre Titre de l'article
         * @param string $contenu Contenu de l'article
         * @access public
         */
        function __construct($auteur,$datePubli,$titre,$contenu) {
            $this->auteur = $auteur;
            $this->datePubli = $datePubli;
            $this->titre = $titre;
            $this->contenu = $contenu;
        }


        /**
         * Retourne la carte HTML de l'article
         * @return string Carte HTML de l'article
         * @access public
         */
        function getCartePost() {
            return '"<div class="Card-Main-Box">
                        <div class="Post-header">
                            <div class="Title">
                                <h3>'.$this->titre.'</h3>
                            </div>
                            <div>
                                <div class="Post-author"><p>'.$this->auteur.'</p></div>
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
                                <a href=""class="Like">Like</a>
                                <a href="" class="Dislike">Dislike</a>
                            </div>
                        </div>"';
         }

    }

?>