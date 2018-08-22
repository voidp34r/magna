<?php

/**
 * Description of usuario_model
 *
 * @author Administrador
 */
class Usuario_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'USUARIO';
        $this->primary_key = 'ID';
    }


}
