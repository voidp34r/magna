<?php

/**
 * Description of Agregado_status_model
 *
 * @author Administrador
 */
class Agregado_status_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'AGREGADO_STATUS';
        $this->primary_key = 'ID';
    }

}
