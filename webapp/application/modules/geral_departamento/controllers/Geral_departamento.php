<?php

/**
 * Description of Geral_departamento
 *
 * @author Administrador
 */
class Geral_departamento extends MY_Controller {

    public function __construct() {
        $this->load->model('geral_departamento_model');

        $this->dados['modulo_nome'] = 'Geral > Departamentos';
        $this->dados['modulo_menu'] = array(
            'Listagem' => 'listar',
        );

        parent::__construct();
    }

    function index() {
        redirect('geral_departamento/listar');
    }

    function listar() {
        $order_by = array(
            'GERAL_DEPARTAMENTO_ID' => 'DESC',
            'NOME' => 'ASC',
        );

        $departamentos = $this->geral_departamento_model
                ->order_by($order_by)
                ->get_all();

        $this->dados['lista'] = $departamentos;
        $this->dados['total'] = $departamentos ? count($departamentos) : 0;
        $this->render('listar');
    }

    function adicionar() {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $id = $this->geral_departamento_model->insert($post);
            if ($id) {
                $this->session->set_flashdata('sucesso', 'Departamento gravado com sucesso');
                redirect('geral_departamento/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->_campos();
        $this->dados['titulo'] = 'Novo departamento';
        $this->render('formulario');
    }

    function editar($id) {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['ATIVO'] = isset($post['ATIVO']) ? TRUE : FALSE;
            $update = $this->geral_departamento_model->update($post, $id);
            if ($update) {
                $this->session->set_flashdata('sucesso', 'Departamento gravado com sucesso');
                redirect('geral_departamento/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->_campos();
        $_POST = $this->geral_departamento_model->as_array()->get($id);
        $this->dados['titulo'] = 'Editar departamento';
        $this->render('formulario');
    }

    function excluir($id) {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao) {
            $delete = $this->geral_departamento_model->delete($id);
            if ($delete) {
                $this->session->set_flashdata('sucesso', 'Departamento excluído com sucesso');
                redirect('geral_departamento/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    function _validation() {
        $this->form_validation->set_rules('GERAL_DEPARTAMENTO_ID', 'Departamento pai', 'trim');
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
    }

    function _campos() {
        $this->dados['departamentos'] = $this->geral_departamento_model
                ->as_dropdown('NOME')
                ->order_by('NOME')
                ->get_all();
    }

}
