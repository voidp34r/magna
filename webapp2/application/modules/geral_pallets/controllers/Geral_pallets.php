<?php


class Geral_pallets extends MY_Controller{
	public function __construct(){
		$this->load->library('pagination');
		
		$this->dados['modulo_nome'] = 'Coleta e Entrega > Entregas 0800';
		$this->dados['modulo_menu'] = [
			'Remessas' => 'listarRemessas',
			'Movimentação' => 'listarMovimentacao',
			'Clientes' => 'listarClientes',
		];
		
		parent::__construct();
	}
	
	function index(){
		$this->redirect('geral_pallets/listarClientes');
	}
	
	function listarClientes($page = 1){
		$this->load->model('pallets_cliente_model');
		
        $order = ['DATAHORA' => 'DESC'];
        $total = $this->pallets_cliente_model
            ->count_rows();
        
        $lista = $this->pallets_cliente_model
            ->order_by('DSNOME', 'ASC')
            ->paginate(10, $total, $page);
                
        $this->dados['lista'] = $lista;
        $this->dados['titulo'] = 'Listar Clientes';
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->pallets_cliente_model->all_pages;
        
		$this->render('listarClientes');
	}

	function listarRemessas($page = 1){
		$this->load->model('pallets_remessa_model');
		
		$order = ['ID' => 'DESC'];
		$total = $this->pallets_remessa_model
			->count_rows();
	
		$lista = $this->pallets_remessa_model
			->join('pallets_cnpj', 'DESENV_TESTE.CLIENTECOLETA_ID = ID', 'left')
			->order_by('ID', 'DESC')
			->paginate(10, $total, $page);
	
		$this->dados['lista'] = $lista;
		$this->dados['titulo'] = 'Listar Remessas';
		$this->dados['total'] = $total;
		$this->dados['paginacao'] = $this->pallets_remessa_model->all_pages;
	
		$this->render('listarRemessas');
	}
	
	function adicionarCliente(){
		$this->form_validation->set_rules('CNPJ', 'CNPJ', 'trim|required');
		$this->form_validation->set_rules('DSNOME', 'Nome', 'trim|required');
		
		if ($this->form_validation->run()){
			$post = $this->input->post();
			
			//Remove tudo que não for dígito para salvar (remove máscara)
			$post["CNPJ"] = preg_replace('/[^[:digit:]]/', null, $post["CNPJ"]);
			
			$id = $this->pallets_cliente_model->insert($post);
			if ($id){
				$this->redirect('geral_pallets/listarClientes', 'sucesso', 'Cliente gravado com sucesso');
			} else {
				$this->dados['erro'] = 'Erro ao adicionar Cliente.';
			}
		}
		$this->dados['titulo'] = 'Novo Cliente para controle de Pallets';
		
		$this->render('formularioCliente');
	}
	
	
}
