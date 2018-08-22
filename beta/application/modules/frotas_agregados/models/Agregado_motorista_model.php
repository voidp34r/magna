<?php

/**
 * Description of Agregado_motorista_model
 *
 * @author Administrador
 */
class Agregado_motorista_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_MOTORISTA';
        $this->primary_key = 'ID';
    }

}
