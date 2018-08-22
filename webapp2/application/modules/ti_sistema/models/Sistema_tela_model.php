<?php

/**
 * Description of sistema_tela_model
 *
 * @author Administrador
 */
class Sistema_tela_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'SISTEMA_TELA';
        $this->primary_key = 'ID';
    }

}
