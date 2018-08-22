<?php

class Grupo_empresa_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->table = 'SOFTRAN_MAGNA.SISGRUPO';
        $this->primary_key = 'CDGRUPO';
    }
}
