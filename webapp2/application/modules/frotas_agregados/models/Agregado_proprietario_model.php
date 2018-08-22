<?php

/**
 * Description of Agregado_proprietario_model
 *
 * @author Administrador
 */
class Agregado_proprietario_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_PROPRIETARIO';
        $this->primary_key = 'ID';
    }

}
