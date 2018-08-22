<?php

/**
 * Description of Agregado_analise_model
 *
 * @author Administrador
 */
class Agregado_analise_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_ANALISE';
        $this->primary_key = 'ID';
    }

}

