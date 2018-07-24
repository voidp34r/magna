<?php

/**
 * Description of sistema_perfil_tela_model
 *
 * @author Administrador
 */
class Sistema_perfil_tela_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'SISTEMA_PERFIL_TELA';
        $this->primary_key = NULL;
    }

}
