<?php

/**
 * Description of sistema_perfil_model
 *
 * @author Administrador
 */
class Sistema_perfil_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'SISTEMA_PERFIL';
        $this->primary_key = 'ID';
    }

}
