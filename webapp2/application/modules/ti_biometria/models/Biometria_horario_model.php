<?php

/**
 * Description of Biometria_equipamento_model
 *
 * @author Administrador
 */
class Biometria_horario_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_HORARIO';
        $this->primary_key = 'ID';
    }

}

