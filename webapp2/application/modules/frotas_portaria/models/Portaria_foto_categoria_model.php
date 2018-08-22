<?php

/**
 * Description of Portaria_foto_categoria_model
 *
 * @author Administrador
 */
class Portaria_foto_categoria_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'PORTARIA_FOTO_CATEGORIA';
        $this->primary_key = 'ID';
    }

}
