<?php

/**
 * Description of Biometria_usuario_model
 *
 * @author Administrador
 */
class ProjetosProjetoModel extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'PROJETOS_PROJETO';
        $this->primary_key = 'ID';
    }

}

