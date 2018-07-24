<?php

/**
 * Description of Agregado_contrato_model
 *
 * @author Administrador
 */
class Agregado_contrato_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'AGREGADO_CONTRATO';
        $this->primary_key = 'ID';
    }
    
}
