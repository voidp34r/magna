<?php

/**
 * Description of Agregado_modelo_model
 *
 * @author Administrador
 */
class Agregado_modelo_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'AGREGADO_MODELO';
        $this->primary_key = 'ID';
    }

}
