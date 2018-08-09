<?php

/**
 * Description of Portaria_motorista_model
 *
 * @author Administrador
 */
class Portaria_motorista_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'PORTARIA_MOTORISTA';
        $this->primary_key = 'ID';
    }

}
