<?php

/**
 * Description of Agregado_gerenciador_risco_model
 *
 * @author Administrador
 */
class Agregado_gerenciador_risco_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_GERENCIADOR_RISCO';
        $this->primary_key = 'ID';
    }

}

