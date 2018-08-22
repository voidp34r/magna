<?php

/**
 * Description of Biometria_usuario_equipamento_model
 *
 * @author Administrador
 */
class Biometria_usuario_equipamento_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_USUARIO_EQUIPAMENTO';
        $this->primary_key = NULL;
    }

}

