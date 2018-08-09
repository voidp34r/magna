<?php

/**
 * Description of Geral_municipio
 *
 * @author Administrador
 */
class Geral_municipio extends MY_Controller {

    public function __construct() {
        $this->load->model('geral_municipio_model');

        $this->publico = array(
            'listar_json',
            'ver_json',
        );

        parent::__construct();
    }

    function listar_json($uf) {
        $estados = $this->geral_municipio_model
                ->as_dropdown('NOME')
                ->order_by('NOME')
                ->get_all(array('UF' => $uf));
        echo json_encode($estados);
    }

    function ver_json($id){
        $municipio = $this->geral_municipio_model->get($id);
        echo json_encode($municipio);        
    }
    
}
