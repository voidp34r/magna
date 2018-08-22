<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of usuario_model
 *
 * @author Administrador
 */
class Geral_departamento_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'GERAL_DEPARTAMENTO';
        $this->primary_key = 'ID';
    }

}
