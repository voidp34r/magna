<?php

/**
 * Description of Geral_gerenciador_risco
 *
 * @author Administrador
 */
class Geral_gerenciador_risco extends MY_Controller {

    public function __construct() {
        $this->load->model('geral_gerenciador_risco_model');

        $this->dados['modulo_nome'] = 'Geral > Gerenciadores de risco';
        $this->dados['modulo_menu'] = array(
            'Listagem' => 'listar',
        );

        parent::__construct();
    }

    function index() {
        $this->redirect('geral_gerenciador_risco/listar');
    }

    function listar() {
        $order_by = array(
            'NOME' => 'ASC',
        );

        $lista = $this->geral_gerenciador_risco_model
                ->order_by($order_by)
                ->get_all();

        $this->dados['lista'] = $lista;
        $this->dados['total'] = $lista ? count($lista) : 0;
        $this->render('listar');
    }

    function adicionar() {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $id = $this->geral_gerenciador_risco_model->insert($post);
            if ($id) {
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco gravado com sucesso');
                $this->redirect('geral_gerenciador_risco/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['titulo'] = 'Novo gerenciador de risco';
        $this->render('formulario');
    }

    function editar($id) {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['ATIVO'] = isset($post['ATIVO']) ? TRUE : FALSE;
            $update = $this->geral_gerenciador_risco_model->update($post, $id);
            if ($update) {
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco gravado com sucesso');
                $this->redirect('geral_gerenciador_risco/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $this->geral_gerenciador_risco_model->as_array()->get($id);
        $this->dados['titulo'] = 'Editar gerenciador de risco';
        $this->render('formulario');
    }

    function excluir($id) {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao) {
            $delete = $this->geral_gerenciador_risco_model->delete($id);
            if ($delete) {
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco excluído com sucesso');
                $this->redirect('geral_gerenciador_risco/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    function _validation() {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('TELEFONE', 'Telefone', 'trim|required');
    }
    
}
