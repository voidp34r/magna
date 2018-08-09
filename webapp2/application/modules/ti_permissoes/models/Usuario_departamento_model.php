<?php

/**
 * Description of usuario_model
 *
 * @author Administrador
 */
class Usuario_departamento_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'USUARIO_DEPARTAMENTO';
        $this->primary_key = NULL;
    }

}
