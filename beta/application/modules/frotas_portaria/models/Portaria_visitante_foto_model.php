
<?php

class Portaria_visitante_foto_model extends MY_Model {

    public function __construct() { 
        parent::__construct();
        $this->table = 'VISITANTE_PORTARIA_FOTO';
        $this->primary_key = 'ID';
    }

}

