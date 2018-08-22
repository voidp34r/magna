<?php

/**
 * Description of Biometria_usuario_equipamento_model
 *
 * @author Administrador
 */
class Biometria_usu_equip_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->table = 'BIOMETRIA_USU_EQUIP';
        $this->primary_key = 'ID';
    }

}

