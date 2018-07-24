<?php

/**
 * Description of Geral_tarefa
 *
 * @author Administrador
 */
class Geral_tarefa extends MY_Controller {

    public function __construct() {
        $this->load->model('ti_permissoes/usuario_model');
        $this->load->model('geral_tarefa_model');

        $this->dados['modulo_nome'] = 'Tarefas';
        $this->dados['modulo_menu'] = array(
            'Pendentes' => 'listar_pendente',
            'Criadas por mim' => 'listar_criadas',
            'Concluídas' => 'listar_concluida',
        );

        $this->publico = array(
            'adicionar',
            'editar',
            'concluir',
            'listar_pendente',
            'listar_concluida',
            'listar_criadas',
        );

        parent::__construct();
    }

    function index() {
        $this->redirect('geral_tarefa/listar_pendente');
    }

    function listar_pendente() {
        $update_where = array(
            'DESTINATARIO_USUARIO_ID' => $this->sessao['usuario_id'],
            'VISUALIZADO' => '0',
        );
        $this->geral_tarefa_model
                ->where($update_where)
                ->update(array('VISUALIZADO' => 1));

        $get_where = array(
            'DESTINATARIO_USUARIO_ID' => $this->sessao['usuario_id'],
            'ENTREGA_DATAHORA IS NULL' => null,
        );
        $titulo = 'Tarefas pendentes';
        $this->_listar($get_where, $titulo, __FUNCTION__);
    }

    function listar_concluida() {
        $get_where = array(
            'DESTINATARIO_USUARIO_ID' => $this->sessao['usuario_id'],
            'ENTREGA_DATAHORA IS NOT NULL' => null,
        );
        $titulo = 'Tarefas concluídas';
        $this->_listar($get_where, $titulo, __FUNCTION__);
    }

    function listar_criadas() {
        $get_where = array(
            'REMETENTE_USUARIO_ID' => $this->sessao['usuario_id'],
        );
        $titulo = 'Tarefas criadas por mim';
        $this->_listar($get_where, $titulo, __FUNCTION__);
    }

    function _listar($get_where, $titulo, $metodo) {
        $tarefas_order = array(
            'PRAZO' => 'ASC NULLS FIRST',
            'DATAHORA_CRIACAO' => 'ASC',
        );
        $tarefas = $this->geral_tarefa_model
                ->where($get_where)
                ->order_by($tarefas_order)
                ->get_all();

        if (!empty($tarefas)) {
            $usuarios = array();
            foreach ($tarefas as $tarefa) {
                $usuarios[] = $tarefa->REMETENTE_USUARIO_ID;
                $usuarios[] = $tarefa->DESTINATARIO_USUARIO_ID;
            }
            $this->dados['usuarios'] = $this->usuario_model
                    ->where('ID', array_unique($usuarios))
                    ->as_dropdown('NOME')
                    ->get_all();
        }

        $this->dados['metodo'] = $metodo;
        $this->dados['titulo'] = $titulo;
        $this->dados['lista'] = $tarefas;
        $this->dados['total'] = $tarefas ? count($tarefas) : 0;
        $this->dados['usuario_id'] = $this->sessao['usuario_id'];
        $this->render('listar');
    }

    function adicionar() {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['REMETENTE_USUARIO_ID'] = $this->sessao['usuario_id'];
            $post['DATAHORA_CRIACAO'] = date('YmdHis');
            $post['PRAZO'] = data_web_para_oracle($post['PRAZO']);
            $id = $this->geral_tarefa_model->insert($post);
            if ($id) {
                $this->session->set_flashdata('sucesso', 'Tarefa gravada com sucesso');
                $this->redirect('geral_tarefa/listar_pendente');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->_campos();
        $this->dados['titulo'] = 'Nova tarefa';
        $this->render('formulario');
    }

    function editar($id) {
        $this->_validation();
        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['PRAZO'] = data_web_para_oracle($post['PRAZO']);
            $update = $this->geral_tarefa_model->update($post, $id);
            if ($update) {
                $this->session->set_flashdata('sucesso', 'Tarefa gravada com sucesso');
                $this->redirect('geral_tarefa/lista_pendente');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->_nao_e_remetente($id);
        $this->_campos();
        $this->dados['titulo'] = 'Editar tarefa';
        $this->render('formulario');
    }

    function _validation() {
        $this->form_validation->set_rules('DESTINATARIO_USUARIO_ID', 'Destinatário', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('DESCRICAO', 'Descrição', 'trim|required');
        $this->form_validation->set_rules('PRAZO', 'Prazo', 'trim|valid_date[d/m/Y]');
    }

    function _campos() {
        $this->dados['usuario_id'] = $this->sessao['usuario_id'];
        $this->dados['usuarios'] = $this->usuario_model
                ->as_dropdown('NOME')
                ->order_by('NOME')
                ->get_all();
    }

    function concluir($id) {
        $this->form_validation->set_rules('ENTREGA_DESCRICAO', 'Descrição', 'trim|required');
        $this->form_validation->set_rules('ENTREGA_DATAHORA', 'Data/Hora', 'trim|valid_date[d/m/Y H:i]');

        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['ENTREGA_DATAHORA'] = data_web_para_oracle($post['ENTREGA_DATAHORA']);
            $update = $this->geral_tarefa_model->update($post, $id);
            if ($update) {
                $this->session->set_flashdata('sucesso', 'Tarefa concluída com sucesso');
                $this->redirect('geral_tarefa/listar_pendente');
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->_nao_e_remetente($id);
        $this->render('concluir');
    }

    function _nao_e_remetente($id) {
        $_POST = $this->geral_tarefa_model->as_array()->get($id);
        if ($_POST['REMETENTE_USUARIO_ID'] != $this->sessao['usuario_id']) {
            exit('Você não tem autorização sobre esse registro');
        }
    }

}
