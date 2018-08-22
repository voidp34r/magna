<?php

/**
 * Description of Biometria_usuario_digital_model
 *
 * @author Administrador
 */
class Biometria_usuario_digital_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_USUARIO_DIGITAL';
        $this->primary_key = 'ID';
    }

}

