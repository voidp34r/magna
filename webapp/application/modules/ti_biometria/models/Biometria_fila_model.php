<?php

/**
 * Description of Biometria_fila_model
 *
 * @author Administrador
 */
class Biometria_fila_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_FILA';
        $this->primary_key = 'ID';
    }

}

