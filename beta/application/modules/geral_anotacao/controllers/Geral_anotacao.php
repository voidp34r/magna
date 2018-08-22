<?php

/**
 * Description of Geral_anotacao
 *
 * @author Administrador
 */
class Geral_anotacao extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('geral_anotacao_model');

        $this->dados['modulo_nome'] = 'Anotações';
        $this->dados['modulo_menu'] = array(
            'Listagem' => 'listar',
        );

        $this->publico = array(
            'adicionar',
            'editar',
            'excluir',
            'listar',
            'ver_json',
        );
        parent::__construct();
    }

    function index ()
    {
        $this->redirect('geral_anotacao/listar');
    }

    function listar ()
    {
        $lista = $this->geral_anotacao_model
                ->where('USUARIO_ID', $this->sessao['usuario_id'])
                ->order_by('TITULO', 'ASC')
                ->get_all();

        $this->dados['lista'] = $lista;
        $this->dados['total'] = $lista ? count($lista) : 0;
        $this->render('listar');
    }

    function ver_json ($id)
    {
        $this->_nao_e_dono($id);
        $this->dados['item'] = $this->geral_anotacao_model->as_array()->get($id);
        $this->render();
    }

    function adicionar ()
    {
        $this->_validation();
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['USUARIO_ID'] = $this->sessao['usuario_id'];
            $id = $this->geral_anotacao_model->insert($post);
            if ($id)
            {
                $this->redirect('geral_anotacao/listar', 'sucesso', 'Anotação gravada com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['titulo'] = 'Nova anotação';
        $this->render('formulario');
    }

    function editar ($id)
    {
        $this->_nao_e_dono($id);
        $this->_validation();
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['ATIVO'] = isset($post['ATIVO']) ? TRUE : FALSE;
            $update = $this->geral_anotacao_model->update($post, $id);
            if ($update)
            {
                $this->redirect('geral_anotacao/listar', 'sucesso', 'Anotação gravada com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $this->geral_anotacao_model->as_array()->get($id);
        $this->dados['titulo'] = 'Editar anotação';
        $this->render('formulario');
    }

    function excluir ($id)
    {
        $this->_nao_e_dono($id);
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->geral_anotacao_model->delete($id);
            if ($delete)
            {
                $this->redirect('geral_anotacao/listar', 'sucesso', 'Anotação excluída com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    function _validation ()
    {
        $this->form_validation->set_rules('TITULO', 'Título', 'trim|required');
        $this->form_validation->set_rules('DESCRICAO', 'Descrição', 'trim|required');
    }

    function _nao_e_dono ($id)
    {
        $get = $this->geral_anotacao_model->get($id);
        if (empty($get))
        {
            exit('Registro inexistente');
        }
        if ($get->USUARIO_ID != $this->sessao['usuario_id'])
        {
            exit('Você não tem autorização sobre esse registro');
        }
    }

}
