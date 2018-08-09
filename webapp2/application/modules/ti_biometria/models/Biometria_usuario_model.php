<?php

/**
 * Description of Biometria_usuario_model
 *
 * @author Administrador
 */
class Biometria_usuario_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_USUARIO';
        $this->primary_key = 'ID';
    }

}

