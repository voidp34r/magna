<?php

class Portaria_foto_config extends MY_Model {

    public function __construct() {
        
        parent::__construct();
        $this->table = 'PORTARIA_FOTO_CONFIG';
        $this->primary_key = 'ID';
    }

}

