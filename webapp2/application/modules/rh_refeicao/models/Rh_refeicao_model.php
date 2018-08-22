<?php

/**
 * Description of Biometria_equipamento_model
 *
 * @author Administrador
 */
class Rh_refeicao_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'RH_REFEICAO';
        $this->primary_key = 'ID';
    }

}

