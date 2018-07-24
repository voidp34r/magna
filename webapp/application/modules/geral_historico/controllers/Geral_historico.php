<?php

/**
 * Description of Geral_tarefa
 *
 * @author Administrador
 */
class Geral_historico extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('ti_permissoes/usuario_log_model');

        $this->dados['modulo_nome'] = 'Histórico';
        $this->dados['modulo_menu'] = array(
            'Listagem' => 'listar',
        );

        $this->publico = array(
            'listar',
        );

        parent::__construct();
    }

    function index ()
    {
        $this->redirect('geral_historico/listar');
    }

    function listar ($page = 1)
    {
        $where = array(
            'USUARIO_ID' => $this->sessao['usuario_id']
        );
        $order = array(
            'DATAHORA' => 'DESC'
        );
        $total = $this->usuario_log_model
                ->where($where)
                ->count_rows();
        $lista = $this->usuario_log_model
                ->where($where)
                ->order_by($order)
                ->paginate(10, $total, $page);
                
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->usuario_log_model->all_pages;
        $this->render('listar');
    }

}
