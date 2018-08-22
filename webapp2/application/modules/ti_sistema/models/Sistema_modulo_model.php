<?php

/**
 * Description of sistema_modulo_model
 *
 * @author Administrador
 */
class Sistema_modulo_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'SISTEMA_MODULO';
        $this->primary_key = 'ID';
    }

}
