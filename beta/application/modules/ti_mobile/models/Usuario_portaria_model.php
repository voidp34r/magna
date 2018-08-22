
<?php

class Usuario_portaria_model extends MY_Model {

    public function __construct() {
        
        parent::__construct();
        $this->table = 'USUARIO_PORTARIA';
        $this->primary_key = 'ID';
    }

}

