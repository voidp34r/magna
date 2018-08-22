<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of geral_tarefa_model
 *
 * @author Administrador
 */
class Geral_tarefa_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'GERAL_TAREFA';
        $this->primary_key = 'ID';
    }

    function inserir_de_gatilho($tarefa) {
        $tarefa['DATAHORA_CRIACAO'] = date('YmdHis');
        $this->geral_tarefa_model->insert($tarefa);
    }

}
