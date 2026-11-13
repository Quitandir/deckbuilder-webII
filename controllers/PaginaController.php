<?php

    class PaginaController{
        private $db;
    
        public function __construct($db)
        {
            $this->db = $db;
        }

        // GET index.php?modulo=pagina&acao=telaTermos
        public function telaTermos() {
            require 'views/pagina/termos.php';
        }

    }
    