<?php

/**
 * Description of usuario_sessao_model
 *
 * @author Administrador
 */
class Usuario_sessao_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'USUARIO_SESSAO';
        $this->primary_key = 'ID';
    }

}
