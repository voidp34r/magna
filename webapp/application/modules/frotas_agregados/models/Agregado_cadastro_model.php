<?php

/**
 * Description of Agregado_cadastro_model
 *
 * @author Administrador
 */
class Agregado_cadastro_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_CADASTRO';
        $this->primary_key = 'ID';
    }

}