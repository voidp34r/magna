<?php

/**
 * Description of Sac_enviosms
 *
 * @author Administrador
 */
class Ti_webservices extends MY_Controller{

    public function __construct(){
        $this->load->model('ws_log_model');
        
        $this->dados['modulo_nome'] = 'TI > Webservices';
        $this->dados['modulo_menu'] = array(
            'Entregas' => 'listarEntregas0800'
        );
        
        $this->publico = ['checkLog'];

        parent::__construct();
    }

    function index(){
        $this->redirect('ti_webservices/listarEntregas0800');
    }

    function listarEntregas0800($page = 1){
    	$total = $this->ws_log_model
	    	->count_rows();
    	
        $this->dados['lista'] = $this->ws_log_model
        	->order_by('ID', 'DESC')
        	->paginate(10,$total, $page);
        
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->ws_log_model->all_pages;

        $this->render('listarEntregas0800');
    }
    
    function getDetalhesRequisicaoJson($id = null){
    	$det = [];
    	if (!is_null($id)){
    		$det = $this->ws_log_model
	    		->where('ID', $id)
	    		->get();
    	}
    	
    	echo json_encode($det);
    }
    
    /**
     * Retorna código do último log no banco
     */
    public function checkLog(){
    	$max = $this->ws_log_model
    		->order_by('ID', 'DESC')
    		->get();
    	
	   	echo json_encode($max->ID);
    }
}
