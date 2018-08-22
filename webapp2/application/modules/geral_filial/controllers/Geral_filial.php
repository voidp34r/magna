<?php

/**
 * Description of Geral_filial
 *
 * @author Administrador
 */
class Geral_filial extends MY_Controller {

    public function __construct() {
        $this->load->model('geral_filial_model');
        $this->load->model('geral_municipio_model');

        $this->dados['modulo_nome'] = 'Geral > Filiais';
        $this->dados['modulo_menu'] = array(
            'Listagem' => 'listar',
        );

        parent::__construct();
    }

    function index() {
        redirect('geral_filial/listar');
    }

    function listar() {
        $order_by = array(
            'NOME' => 'ASC',
        );

        $lista = $this->geral_filial_model
                ->order_by($order_by)
                ->get_all();

        $this->dados['municipios'] = $this->geral_municipio_model->listar_tudo();
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $lista ? count($lista) : 0;
        $this->render('listar');
    }

    function adicionar() {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $id = $this->geral_filial_model->insert($post);
            if ($id) {
                $this->session->set_flashdata('sucesso', 'Filial gravado com sucesso');
                redirect('geral_filial/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['titulo'] = 'Nova filial';
        $this->render('formulario');
    }

    function editar($id) {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['ATIVO'] = isset($post['ATIVO']) ? TRUE : FALSE;
            $update = $this->geral_filial_model->update($post, $id);
            if ($update) {
                $this->session->set_flashdata('sucesso', 'Filial gravado com sucesso');
                redirect('geral_filial/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $this->geral_filial_model->as_array()->get($id);
        $this->dados['titulo'] = 'Editar filial';
        $this->render('formulario');
    }

    function excluir($id) {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao) {
            $delete = $this->geral_filial_model->delete($id);
            if ($delete) {
                $this->session->set_flashdata('sucesso', 'Filial excluído com sucesso');
                redirect('geral_filial/listar');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    function _validation() {
        $this->form_validation->set_rules('GERAL_MUNICIPIO_ID', 'Município', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('ENDERECO', 'Endereço', 'trim|required');
        $this->form_validation->set_rules('CEP', 'CEP', 'trim|required');
        $this->form_validation->set_rules('TELEFONE', 'Telefone', 'trim|required');
        $this->form_validation->set_rules('IP', 'IP', 'trim|valid_ip');
    }
    
}
