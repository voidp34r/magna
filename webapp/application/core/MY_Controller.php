<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends MX_Controller
{

    protected $dados = array();
    protected $sessao = array();
    protected $publico = array();

    function __construct ()
    {
        $this->load->library('perfil_permissao');
        $this->sessao = $this->session->all_userdata();
        $this->_token();
        $logado = !empty($this->sessao['logado']) ? $this->sessao['logado'] : FALSE;
        $usuario_id = !empty($this->sessao['usuario_id']) ? $this->sessao['usuario_id'] : FALSE;
        if (!$usuario_id)
        {
            redirect('autenticacao/sair');
        }
        
        $controller = $this->uri->segment(1);
        $metodo = $this->uri->segment(2);
        $uri_string = $this->uri->uri_string();
        $this->_log($controller, $metodo, $uri_string);
        $this->_modulo_selecionado($controller, $metodo);
        $this->menu_selecionado($metodo);
        
        $this->dados['sessao'] = $this->sessao;
        $this->dados['modulo_url'] = $controller;
        $this->dados['tarefas_qtd'] = $this->_retorna_tarefas();
        
        if ($this->session->flashdata('sucesso'))
        {
            $this->dados['sucesso'] = $this->session->flashdata('sucesso');
        }
        if ($this->session->flashdata('erro'))
        {
            $this->dados['erro'] = $this->session->flashdata('erro');
        }
        // e a sessão estiver ativa
        if ($logado || in_array($metodo, $this->publico))
        {
            // carrega o menu lateral com os módulos autorizados
            $this->dados['navbar'] = $this->perfil_permissao->retorna_modulos_autorizados($usuario_id);
            // se não for público e o método for diferente de privado
            if (!in_array($metodo, $this->publico) && $metodo != 'privado')
            {
                // necessário verificar se o usuário tem permissão para essa tela
                if (!$this->perfil_permissao->verifica_permissao($controller, $metodo, $usuario_id))
                {
                    exit('Você não tem permissão para acessar essa tela.');
                }
            }
        }
        // mas se o usuário não estiver logado, manda ele para o login
        else
        {
            redirect('autenticacao/sair');
        }
    }

    /**
     * render
     * Função utilizada para renderizar a view passada como parâmetro conforme
     * o template escolhido (master por padrão). Também essa função exibe os dados
     * do atributo público "dados" em formato json caso venha a solicitação por get
     * @param string $the_view
     * @param string $template
     */
    protected function render ($the_view = NULL, $template = 'master')
    {
        if ($template == 'master' && !$this->dados['sessao']['logado'])
        {
            redirect('autenticacao/sair');
        }
        if (!empty($_GET['template']) && $_GET['template'] == 'json')
        {
            $template = 'json';
            unset($this->dados['sessao']);
        }
        if ($template == 'json' || $this->input->is_ajax_request())
        {
            header('Content-Type: application/json');
            echo json_encode($this->dados);
        }
        elseif (is_null($template))
        {
            $this->load->view($the_view, $this->dados);
        }
        else
        {
            $this->dados['the_view_content'] = (is_null($the_view)) ? '' : $this->load->view($the_view, $this->dados, TRUE);
            $this->load->view('templates/' . $template . '_view', $this->dados);
        }
    }

    /**
     * redirect
     * Função utilizada para redirecionar para um outro método e enviar uma mensagem
     * de erro ou sucesso caso seja passado por parâmetro
     * @param string $the_view
     * @param string $tipo
     * @param string $mensagem
     */
    protected function redirect ($the_view, $tipo = NULL, $mensagem = NULL)
    {
        if (!empty($_GET['template']) && $_GET['template'] == 'json')
        {
            $retorno = array(
                'view' => $the_view,
                'tipo' => ucfirst($tipo),
                'mensagem' => $mensagem,
            );
            echo json_encode($retorno);
            exit();
        }
        else
        {
            if ($tipo && $mensagem)
            {
                $this->session->set_flashdata($tipo, $mensagem);
            }
            redirect($the_view);
        }
    }

    /**
     * _token
     * Função privada utilizada pelo construtor para token e hash passado por 
     * requisições que utilizam essa forma de sessão (mobile, por exemplo).
     */
    function _token ()
    {
        if ($this->input->post('TOKEN') && $this->input->post('HASH'))
        {
            $token = $this->input->post('TOKEN');
            $hash = $this->input->post('HASH');
            if (password_verify($token, $hash))
            {
                $logado = TRUE;
                $this->load->model('ti_permissoes/usuario_sessao_model');
                $this->load->model('ti_permissoes/usuario_model');
                $sessao_where = array(
                    'TOKEN' => $token,
                );
                $sessao = $this->usuario_sessao_model->get($sessao_where);
                $usuario = $this->usuario_model->get($sessao->USUARIO_ID);
                $this->sessao['usuario'] = $usuario->USUARIO;
                $this->sessao['usuario_nome'] = $usuario->NOME;
                $this->sessao['logado'] = $logado;
                $this->sessao['usuario_id'] = $usuario->ID;
                $this->sessao['token'] = $sessao->TOKEN;
                $this->sessao['filial'] = $usuario->CDEMPRESA;
                $this->sessao['centro_custo'] = $usuario->CDCENTROCUSTO;
            }
            unset($_POST['TOKEN']);
            unset($_POST['HASH']);
        }
    }

    /**
     * _modulo_selecionado
     * Função privada utilizada pelo construtor para setar o atributo público
     * "dados" com os dados do módulo selecionado pelo usuário
     * @param string $controller
     */
    function _modulo_selecionado ($controller)
    {
        $partes = explode('_', $controller);
        $this->dados['modulo_selecionado'] = $controller;
        $this->dados['modulo_pai_selecionado'] = $partes[0];
    }

    /**
     * _menu_selecionado
     * Função privada utilizada pelo construtor para setar o atributo público
     * "dados" e na sessão com o menu selecionado pelo usuário
     * @param string $metodo
     */
    private function menu_selecionado ($metodo){
        $this->dados['menu_selecionado'] = '';
        if (!empty($this->dados['modulo_menu']))
        {
            if (in_array($metodo, $this->dados['modulo_menu']))
            {
                $this->dados['menu_selecionado'] = $metodo;
                $this->session->set_userdata('menu_selecionado', $metodo);
            }
            else
            {
                $menu_selecionado = $this->session->userdata('menu_selecionado');
                if (!empty($menu_selecionado))
                {
                    $this->dados['menu_selecionado'] = $menu_selecionado;
                }
            }
        }
    }

    /**
     * _log
     * Função privada utilizada pelo construtor para registrar as movimentações
     * do usuário na tabela de logs dos usuários
     * @param string $controller
     * @param string $metodo
     * @param string $uri_string
     */
    function _log ($controller, $metodo, $uri_string)
    {
        if (!$controller || !$metodo)
        {
            return NULL;
        }
        if (empty($this->sessao['usuario_id']))
        {
            return NULL;
        }
        if ($controller == 'geral_historico')
        {
            return NULL;
        }
        $this->load->model('ti_permissoes/usuario_log_model');
        $log = array();
        $log['USUARIO_ID'] = $this->sessao['usuario_id'];
        $log['DATAHORA'] = date('YmdHis');
        $log['MODULO'] = $controller;
        $log['TELA'] = $metodo;
        $log['URI'] = $uri_string;
        $log['IP'] = $_SERVER['REMOTE_ADDR'];
        $log['USER_AGENT'] = $_SERVER["HTTP_USER_AGENT"];
        $log['INPUT_POST'] = (!empty($_POST)) ? json_encode($_POST, JSON_PRETTY_PRINT) : NULL;
        $log['INPUT_GET'] = (!empty($_GET)) ? json_encode($_GET, JSON_PRETTY_PRINT) : NULL;
        //HABILITE ESSA VERIFICAÇÃO CASO QUEIRA 
        //REGISTRAR APENAS ALTERAÇÕES NO SISTEMA
        //if ($log['INPUT_POST'] || $log['INPUT_GET'])
        //{
        //$this->usuario_log_model->insert($log);
        //}
    }

    /**
     * _retorna_tarefas
     * Função privada utilizada pelo construtor para obter as tarefas não 
     * visualizas pelo usuário
     * @return int
     */
    function _retorna_tarefas ()
    {
        if (!empty($this->sessao['usuario_id']))
        {
            $this->load->model('geral_tarefa_model');
            $where = array(
                'DESTINATARIO_USUARIO_ID' => $this->sessao['usuario_id'],
                'VISUALIZADO' => '0',
            );
            return $this->geral_tarefa_model->count_rows($where);
        }
    }

    /**
     * _filtro_where
     * Função privada utilizada para obter os filtros e atribuir no where da
     * consulta que deseja filtrar
     */
    function _filtro_where(){
        $this->dados['filtro']['where'] = array();
        $filtro = $this->input->get('filtro');
        
        if (!empty($filtro['where'])){
            foreach ($filtro['where'] as $f_chave => $f_valor){
                if ($f_valor != ''){
                    $this->db->where($f_chave, $f_valor);
                    $this->dados['filtro']['where'][$f_chave] = $f_valor;
                }
            }
        }
    }

    /**
     * Função privada utilizada para obter os filtros e atribuir no like da
     * consulta que deseja filtrar
     * TODO Fun��o privada que � p�blica? (ti_permissoes)
     * @name _filtro_like
     */
    function _filtro_like (){
        $this->dados['filtro']['like'] = array();
        $filtro = $this->input->get('filtro');
        
        if (!empty($filtro['like'])){
            foreach ($filtro['like'] as $f_chave => $f_valor){
                if ($f_valor != ''){
                    $this->db->like('UPPER(' . $f_chave . ')', strtoupper($f_valor));
                    $this->dados['filtro']['like'][$f_chave] = $f_valor;
                }
            }
        }
    }

    /**
     * verifica_gatilho
     * Função utilizada para verificar se o gatilho passado por parâmetro está
     * liberado para o usuário da sessão
     * @param string $nome
     * @return boolean
     */
    function verifica_gatilho ($nome)
    {
        $destinatarios = $this->retorna_destinatario_gatilho($nome);
        foreach ($destinatarios as $destinatario)
        {
            if ($destinatario->USUARIO_ID == $this->sessao['usuario_id'])
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * retorna_destinatario_gatilho
     * Função utilizada para listar os usuários que possuem um gatilho
     * @param string $nome
     * @return array
     */
    function retorna_destinatario_gatilho ($nome)
    {
        $this->load->model('ti_sistema/sistema_gatilho_model');
        $this->load->model('ti_permissoes/usuario_gatilho_model');
        $gatilho = $this->sistema_gatilho_model->get(array('NOME' => $nome));
        if (!empty($gatilho))
        {
            $destinatarios = $this->usuario_gatilho_model->get_all(array('SISTEMA_GATILHO_ID' => $gatilho->ID));
            if (!empty($destinatarios))
            {
                return $destinatarios;
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

}
