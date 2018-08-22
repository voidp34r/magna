<?php


class sistema_noticias extends MY_Model {

    public function __construct() {
        parent::__construct();        
        $this->table = 'GERAL_NOTICIAS';
        $this->primary_key = 'ID';
    }

}
