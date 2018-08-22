<?php

/**
 * Description of Ti_biometria
 *
 *
 */
class Ti_projetos extends MY_Controller {

    public function __construct(){
        $this->load->model('projetosProjetoModel');

        $this->dados['modulo_nome'] = 'TI > Gestão de Projetos';
        $this->dados['modulo_menu'] = array(
            'Projetos' => 'listar',
            'Backlog' => 'listar_backlog'
        );
//         $this->publico = array(
//             'gerar_template',
//             'combinar_template',
//             'ver_usuario',
//         );
        parent::__construct();
    }

    public function index(){
    	$this->redirect('ti_projetos/listar');
    }

    public function listar($page = 1){
    	$total = $this->projetosProjetoModel->count_rows();
    	$lista = $this->projetosProjetoModel->paginate(10, $total, $page);
    	
    	$this->dados['lista'] = $lista;
    	$this->dados['total'] = $total;
    	$this->dados['paginacao'] = $this->projetosProjetoModel->all_pages;
    	
        $this->render('listar');
    }
    
    public function listarBacklog ($page = 1){
    	$this->render('listar_backlog');
    }
    
    public function adicionarProjeto(){
    	$this->setValidation();
    	
    	if ($this->form_validation->run()){
    		$post = $this->input->post();
    		
    		$id = $this->projetosProjetoModel->insert($post);
    		print_pre($this->db->last_query());
    		
    		if ($id){
    			$this->redirect('ti_projetos/listar', 'sucesso', 'Projeto gravado com sucesso');
    		} else {
    			$this->dados['erro'] = 'Erro ao gravar';
    		}
    	}
    	$this->dados['titulo'] = 'Novo';
    	
    	$this->render('formularioProjeto');
    }
    
    /**
     * editar_usuario
     * Tela para editar um usuário cadastrado
     * @param int $id
     */
    public function editarProjeto($id){
    	$this->setValidation();
    	
    	if ($this->form_validation->run()){
    		$post = $this->input->post();
    		$update = $this->projetosProjetoModel->update($post, $id);
    		
    		if ($update){
    			$this->redirect('ti_projetos/listar', 'sucesso', 'Projeto gravado com sucesso');
    		} else {
    			$this->dados['erro'] = 'Erro ao gravar';
    		}
    	}
    	$idProjeto = $this->projetosProjetoModel->as_array()->get($id);
    	$_POST = $idProjeto;
    	
    	$this->dados['titulo'] = 'Editar';

    	$this->render('formularioProjeto');
    }
    
    /**
     * excluir_horario
     * Tela para excluir um horário cadastrado
     * @param int $id
     */
    public function excluirProjeto($id){
    	$confirmacao = $this->input->post('confirmacao');
    	
    	if ($confirmacao){
    		$this->biometria_horario_faixa_model->delete(array('BIOMETRIA_HORARIO_ID' => $id));
    		$delete = $this->biometria_horario_model->delete($id);
    		
    		if ($delete){
    			$this->redirect('ti_biometria/listar_horario', 'sucesso', 'Horário excluído com sucesso');
    		} else {
    			$this->dados['erro'] = 'Erro ao gravar';
    		}
    	}
    	$this->render('_generico/excluir');
    }
    
    private function setValidation(){
    	$this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
    	$this->form_validation->set_rules('DESCRICAO', 'Descrição', 'trim|required');
    }
}
