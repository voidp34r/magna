<?php

/**
 * Description of Biometria_equipamento_model
 *
 * @author Administrador
 */
class Biometria_erro_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'BIOMETRIA_ERRO';
        $this->primary_key = 'ID';
    }

}

