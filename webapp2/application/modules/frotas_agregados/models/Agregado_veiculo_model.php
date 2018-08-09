<?php

/**
 * Description of Agregado_veiculo_model
 *
 * @author Administrador
 */
class Agregado_veiculo_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_VEICULO';
        $this->primary_key = 'ID';
    }

}
