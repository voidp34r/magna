
<?php

class Portaria_visitante_model extends MY_Model {

    public function __construct() { 
        parent::__construct();
        $this->table = 'VISITANTE_PORTARIA';
        $this->primary_key = 'ID';
    }

}

