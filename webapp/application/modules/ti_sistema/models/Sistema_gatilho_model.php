<?php

/**
 * Description of sistema_gatilho_model
 *
 * @author Administrador
 */
class Sistema_gatilho_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'SISTEMA_GATILHO';
        $this->primary_key = 'ID';
    }


}
