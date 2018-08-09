<?php

/**
 * Description of usuario_perfil_model
 *
 * @author Administrador
 */
class Usuario_perfil_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'USUARIO_PERFIL';
        $this->primary_key = NULL;
    }


}
