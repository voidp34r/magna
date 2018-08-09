<?php

/**
 * Description of usuario_gatilho_model
 *
 * @author Administrador
 */
class Usuario_gatilho_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'USUARIO_GATILHO';
        $this->primary_key = NULL;
    }


}
