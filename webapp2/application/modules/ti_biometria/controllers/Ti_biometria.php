<?php

/**
 * Description of Ti_biometria
 *
 * @author Administrador
 */
class Ti_biometria extends MY_Controller{

    public function __construct(){
        $this->load->model('biometria_equipamento_model');
        $this->load->model('biometria_usuario_model');
        $this->load->model('biometria_usuario_digital_model');
        $this->load->model('biometria_usuario_equipamento_model');
        $this->load->model('biometria_horario_model');
        $this->load->model('biometria_horario_faixa_model');
        $this->load->model('biometria_fila_model');

        $this->dados['modulo_nome'] = 'TI > Biometria';
        $this->dados['modulo_menu'] = array(
            'Usuários' => 'listar_usuario',
            'Equipamentos' => 'listar_equipamento',
            'Horários' => 'listar_horario',
            'Liberações' => 'listar_liberacao',
            'Fila' => 'listar_fila',
        );
        $this->publico = array(
            'loginControlId',
            'gerar_outrotemplate',
            'combinar_template',
            'ver_usuario',
        );
        parent::__construct();
    }

    function index(){
        $this->redirect('ti_biometria/listar_usuario');
    }

    ////////////////////////////////
    // Usuário
    ////////////////////////////////

    /**
     * listar_usuario
     * Listagem dos usuários cadastrados na biometria
     * @param int $page
     */
    function listar_usuario($page = 1){
        $this->_filtro_where();
        $this->_filtro_like();
        $total = $this->biometria_usuario_model->count_rows();
        
        $this->_filtro_where();
        $this->_filtro_like();
        $lista = $this->biometria_usuario_model->paginate(10, $total, $page);
        
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_usuario_model->all_pages;
        
        $this->render('listar_usuario');
    }

    /**
     * ver_usuario
     * Função utilizada na tela de adicionar liberação para obter dados do usuário
     * @param int $id
     */
    function ver_usuario($id){
        $retorno = $this->biometria_usuario_model->as_array()->get($id);
        
        echo json_encode($retorno);
    }

    /**
     * _validation_usuario
     * Função de validação dos campos de usuário
     */
    function _validation_usuario(){
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('BIOMETRIA_HORARIO_ID', 'Horário de acesso', 'trim|required|is_natural_no_zero');
    }

    /**
     * adicionar_usuario
     * Tela para adicionar usuário utilizada pelo RH
     */
    function adicionar_usuario(){
        $this->form_validation->set_rules('CPF', 'CPF', 'trim|required|valid_cpf|is_unique[BIOMETRIA_USUARIO.CPF]');
        $this->_validation_usuario();
        
        if ($this->form_validation->run()){
            $post = $this->input->post();
            $post['VALIDADE'] = (!empty($post['VALIDADE'])) ? data_web_para_oracle($post['VALIDADE']) : NULL;
            
            if (!empty($post['templates'])){
                $digitais = $post['templates'];
                
                unset($post['templates']);
            }
            
            $id = $this->biometria_usuario_model->insert($post);
            
            print_pre($this->db->last_query());
            
            if ($id){
                if (!empty($digitais)){
                    $usuario_template = array();
                    $usuario_template['BIOMETRIA_USUARIO_ID'] = $id;
                    foreach ($digitais as $posicao => $template){
                        $usuario_template['POSICAO'] = $posicao;
                        $usuario_template['TEMPLATE_0'] = substr($template, 0, 4000);
                        
                        if (strlen($template) > 4000){
                            $usuario_template['TEMPLATE_1'] = substr($template, 4000, 4000);
                        }
                        
                        if (strlen($template) > 8000){
                            $usuario_template['TEMPLATE_2'] = substr($template, 8000, 4000);
                        }
                        
                        $this->biometria_usuario_digital_model->insert($usuario_template);
                    }
                }
                
                $this->redirect('ti_biometria/listar_usuario', 'sucesso', 'Usuário gravado com sucesso');
                
            } else {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        
        $this->dados['titulo'] = 'Novo';
        $this->dados['digitais_cadastradas'] = '';
        $this->dados['horarios'] = $this->biometria_horario_model
        	->as_dropdown('NOME')
        	->get_all();
        
        $this->render('formulario_usuario');
    }

    /**
     * editar_usuario
     * Tela para editar um usuário cadastrado
     * @param int $id
     */
    function editar_usuario($id){
        $usuario_get = $this->biometria_usuario_model->as_array()->get($id);
        $cpf_diferente = ($usuario_get['CPF'] != $this->input->post('CPF')) ? '|is_unique[BIOMETRIA_USUARIO.CPF]' : '';
        $this->form_validation->set_rules('CPF', 'CPF', 'trim|required|valid_cpf' . $cpf_diferente);
        $this->_validation_usuario();
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['VALIDADE'] = (!empty($post['VALIDADE'])) ? data_web_para_oracle($post['VALIDADE']) : NULL;
            if (!empty($post['templates']))
            {
                $digitais = $post['templates'];
                unset($post['templates']);
            }
            $update = $this->biometria_usuario_model->update($post, $id);
            if ($update)
            {
                if (!empty($digitais))
                {
                    $usuario_digitais = $this->biometria_usuario_digital_model->count_rows(array('BIOMETRIA_USUARIO_ID' => $id));
                    $usuario_template = array();
                    $usuario_template['BIOMETRIA_USUARIO_ID'] = $id;
                    foreach ($digitais as $posicao => $template)
                    {
                        $usuario_template['POSICAO'] = (!empty($usuario_digitais)) ? $usuario_digitais : 0;
                        $usuario_template['TEMPLATE_0'] = substr($template, 0, 4000);
                        if (strlen($template) > 4000)
                        {
                            $usuario_template['TEMPLATE_1'] = substr($template, 4000, 4000);
                        }
                        if (strlen($template) > 8000)
                        {
                            $usuario_template['TEMPLATE_2'] = substr($template, 8000, 4000);
                        }
                        $this->biometria_usuario_digital_model->insert($usuario_template);
                    }
                }
                $this->redirect('ti_biometria/listar_usuario', 'sucesso', 'Usuário gravado com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $usuario_get;
        $this->dados['titulo'] = 'Editar';
        $this->dados['digitais_cadastradas'] = $this->biometria_usuario_digital_model
                ->count_rows(array('BIOMETRIA_USUARIO_ID' => $id));
        $this->dados['horarios'] = $this->biometria_horario_model
                ->as_dropdown('NOME')
                ->get_all();
        $this->render('formulario_usuario');
    }

    /**
     * loginControlId
     * Realiza login no equipamento ControlID
     * @param int $width
     * @param int $height
     */
    function loginControlId($tipoEquipamento){
    	$this->load->library('controlid_webservice');
    
    	//TODO buscar equipamentos padrão do cadastro de equipamentos
    	if ($tipoEquipamento == "idclass")
    		$this->controlid_webservice->config('192.168.99.49');
    	else //idaccess
    		$this->controlid_webservice->config('192.168.99.243');
    	
    	echo json_encode($this->controlid_webservice->login('admin', 'admin'));
    }
    
    /**
     * excluir_usuario_digital
     * Função de excluir todas as digitais de um usuário
     * @param int $usuario_id
     */
    function excluir_usuario_digital ($usuario_id)
    {
        $this->load->library('controlid_webservice');
        $delete = $this->biometria_usuario_digital_model
                ->delete(array('BIOMETRIA_USUARIO_ID' => $usuario_id));
        if ($delete)
        {
            $equipamentos_where = array('BIOMETRIA_USUARIO_ID' => $usuario_id);
            $usuario_equipamentos = $this->biometria_usuario_equipamento_model->get_all($equipamentos_where);
            if (!empty($usuario_equipamentos))
            {
                foreach ($usuario_equipamentos as $usuario_equipamento)
                {
                    $equipamento = $this->biometria_equipamento_model->get($usuario_equipamento->BIOMETRIA_EQUIPAMENTO_ID);
                    $this->controlid_webservice->config($equipamento->IP, $equipamento->PROTOCOLO);
                    $sessao = $this->controlid_webservice->login($equipamento->USUARIO, $equipamento->SENHA);
                    $usuario_arr = array();
                    $usuario_arr['user_id'] = array((int) $usuario_id);
                    $retorno = $this->controlid_webservice->destroy_objects('templates', $usuario_arr);
                    if (empty($retorno->changes) || !$sessao)
                    {
                        $fila_arr = array();
                        $fila_arr['DATAHORA'] = date('YmdHis');
                        $fila_arr['BIOMETRIA_EQUIPAMENTO_ID'] = $equipamento->ID;
                        $fila_arr['FUNCAO'] = $this->controlid_webservice->uri;
                        $fila_arr['COMANDO'] = $this->controlid_webservice->prepare;
                        $this->biometria_fila_model->insert($fila_arr);
                        $retornos[] = $equipamento->NOME . ' - Exclusão alocada na fila de espera';
                    }
                    else
                    {
                        $retornos[] = $equipamento->NOME . ' - Excluído com sucesso';
                    }
                }
            }
            $this->redirect('ti_biometria/editar_usuario/' . $usuario_id, 'sucesso', implode('<br>', $retornos));
        }
        else
        {
            $this->dados['erro'] = 'Erro ao gravar';
        }
    }

    
    ////////////////////////////////
    // Template
    ////////////////////////////////

    /**
     * gerar_template
     * Função utilizada pela tela de adicionar usuário para gerar o template da digital
     * @param int $width
     * @param int $height
     */
    function gerar_template($width, $height){
        $this->load->library('controlid_webservice');
        $this->load->library('dev_utils');
        
        $arquivo = base64_decode($_POST['file']);
        
        $this->dev_utils->criar_arquivo("digital.txt", $arquivo, "wb");
        
        $this->controlid_webservice->config('192.168.99.49');
        $this->controlid_webservice->login('admin', 'admin');
        echo json_encode($this->controlid_webservice->template_extract($arquivo, $width, $height));
    }

    /**
     * gerar_template
     * Função utilizada pela tela de adicionar usuário para gerar o template da digital
     * @param int $width
     * @param int $height
     */
    function gerar_outrotemplate($width, $height){
    	$this->load->library('controlid_webservice');
    	$this->load->library('dev_utils');
    
    	$arquivo = base64_decode($_POST['file']);
    
    	$this->dev_utils->criar_arquivo("digital.txt", $arquivo, "wb");
    
    	$this->controlid_webservice->config('192.168.99.49');
    	$this->controlid_webservice->login('admin', 'admin');
    	echo json_encode($this->controlid_webservice->template_extract($arquivo, $width, $height));
    }
    /**
     * combinar_template
     * Função utilizada pela tela de adicionar usuário para combinar os templates
     * @param int $s0
     * @param int $s1
     * @param int $s2
     */
    function combinar_template ($s0, $s1, $s2){
        $this->load->library('controlid_webservice');
        $arquivo = base64_decode($_POST['file']);
        $this->controlid_webservice->config('192.168.99.49');
        $this->controlid_webservice->login('admin', 'admin');
        log_message('error', $_POST['file']);
        echo json_encode($this->controlid_webservice->template_match($arquivo, $s0, $s1, $s2));
    }

    ////////////////////////////////
    // Equipamento
    ////////////////////////////////

    /**
     * listar_equipamento
     * Tela para listar todos os iDClass e iDAccess
     * @param int $page
     */
    function listar_equipamento ($page = 1)
    {
        $total = $this->biometria_equipamento_model->count_rows();
        $lista = $this->biometria_equipamento_model->order_by('NOME')->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_equipamento_model->all_pages;
        $this->render('listar_equipamento');
    }

    /**
     * listar_equipamento
     * Função utilizada pela tela de adicionar e editar equipamento
     * @param int $id
     */
    function _formulario_equipamento ($id = null)
    {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('CDEMPRESA', 'Filial', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('IP', 'IP', 'trim|required|valid_ip');
        $this->form_validation->set_rules('USUARIO', 'Usuário', 'trim|required');
        $this->form_validation->set_rules('SENHA', 'Senha', 'trim|required');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['PROTOCOLO'] = ($post['TIPO'] == 'IDACCESS') ? 'http' : 'https';
            if ($id)
            {
                $this->biometria_equipamento_model->update($post, $id);
            }
            else
            {
                $id = $this->biometria_equipamento_model->insert($post);
            }
            if ($id)
            {
                $this->redirect('ti_biometria/listar_equipamento', 'sucesso', 'Equipamento gravado com sucesso');
            }
        }
    }

    /**
     * adicionar_equipamento
     * Tela para adicionar um novo equipamento
     */
    function adicionar_equipamento ()
    {
        $this->load->library('softran_oracle');
        $this->_formulario_equipamento();
        $this->dados['titulo'] = 'Novo';
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->render('formulario_equipamento');
    }

    /**
     * editar_equipamento
     * Tela para editar um equipamento cadastrado
     * @param int $id
     */
    function editar_equipamento ($id)
    {
        $this->load->library('softran_oracle');
        $this->_formulario_equipamento($id);
        $_POST = $this->biometria_equipamento_model->as_array()->get($id);
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->dados['titulo'] = 'Editar';
        $this->render('formulario_equipamento');
    }

    /**
     * excluir_equipamento
     * Tela para excluir um equipamento cadastrado
     * @param int $id
     */
    function excluir_equipamento ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->biometria_equipamento_model->delete($id);
            if ($delete)
            {
                $this->redirect('ti_biometria/listar_equipamento', 'sucesso', 'Equipamento excluído com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    ////////////////////////////////
    // Horário
    ////////////////////////////////

    /**
     * listar_horario
     * Tela utilizada para listar horários cadastrados
     * @param int $page
     */
    function listar_horario ($page = 1)
    {
        $total = $this->biometria_horario_model->count_rows();
        $lista = $this->biometria_horario_model->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_horario_model->all_pages;
        $this->render('listar_horario');
    }

    /**
     * _formulario_horario
     * Função utilizada para adicionar e editar horários
     * @param int $id
     */
    function _formulario_horario ($id = null)
    {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        if (!$id)
        {
            $this->form_validation->set_rules('FAIXAS[]', 'Faixas de horário', 'trim|required');
        }
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $faixas = $post['FAIXAS'];
            unset($post['FAIXA']);
            unset($post['FAIXAS']);
            if ($id)
            {
                $this->biometria_horario_model->update($post, $id);
            }
            else
            {
                $id = $this->biometria_horario_model->insert($post);
            }
            if ($id)
            {
                if (!empty($faixas))
                {
                    foreach ($faixas as $faixa_parse)
                    {
                        $FAIXA = array();
                        parse_str($faixa_parse);
                        $FAIXA['INICIO'] = hora_para_segundos($FAIXA['INICIO']);
                        $FAIXA['FIM'] = hora_para_segundos($FAIXA['FIM']);
                        $FAIXA['BIOMETRIA_HORARIO_ID'] = $id;
                        $this->biometria_horario_faixa_model->insert($FAIXA);
                    }
                }
                $this->redirect('ti_biometria/listar_horario', 'sucesso', 'Horário gravado com sucesso');
            }
        }
    }

    /**
     * adicionar_horario
     * Tela para adicionar um novo horário
     */
    function adicionar_horario ()
    {
        $this->_formulario_horario();
        $this->dados['titulo'] = 'Novo';
        $this->render('formulario_horario');
    }

    /**
     * editar_horario
     * Tela para editar um horário cadastrado
     * @param int $id
     */
    function editar_horario ($id)
    {
        $this->_formulario_horario($id);
        $_POST = $this->biometria_horario_model->as_array()->get($id);
        $this->dados['horario_faixas'] = $this->biometria_horario_faixa_model
                ->get_all(array('BIOMETRIA_HORARIO_ID' => $id));
        $this->dados['titulo'] = 'Editar';
        $this->render('formulario_horario');
    }

    /**
     * excluir_horario
     * Tela para excluir um horário cadastrado
     * @param int $id
     */
    function excluir_horario ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $this->biometria_horario_faixa_model->delete(array('BIOMETRIA_HORARIO_ID' => $id));
            $delete = $this->biometria_horario_model->delete($id);
            if ($delete)
            {
                $this->redirect('ti_biometria/listar_horario', 'sucesso', 'Horário excluído com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    ////////////////////////////////
    // Liberações
    ////////////////////////////////

    /**
     * listar_liberacao
     * Tela para listar as liberações dos usuários nos equipamentos
     * @param int $page
     */
    function listar_liberacao ($page = 1)
    {
        $total = $this->biometria_usuario_equipamento_model->count_rows();
        $lista = $this->biometria_usuario_equipamento_model->order_by('BIOMETRIA_EQUIPAMENTO_ID')->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_usuario_equipamento_model->all_pages;
        $this->dados['usuarios'] = array('') + $this->biometria_usuario_model->as_dropdown('NOME')->get_all();
        $this->dados['horarios'] = $this->biometria_horario_model->as_dropdown('NOME')->get_all();
        $this->dados['equipamentos'] = $this->biometria_equipamento_model->as_dropdown('NOME')->get_all();
        $this->render('listar_liberacao');
    }

    /**
     * adicionar_liberacao
     * Tela para adicionar um usuário a um ou mais equipamentos
     */
    function adicionar_liberacao ()
    {
        $post = $this->input->post();
        if ($post)
        {
            //PREPARA OBJETOS QUE SERÃO CRIADOS NOS EQUIPAMENTOS
            $usuario_id = $post['BIOMETRIA_USUARIO_ID'];
            $usuario_get = $this->biometria_usuario_model->get($usuario_id);
            $horarios = $post['HORARIOS'];
            $equipamentos = $post['EQUIPAMENTOS'];
            if (!empty($equipamentos))
            {
                $retorno = array();
                foreach ($equipamentos as $equipamento_id => $on)
                {
                    $equipamento_get = $this->biometria_equipamento_model->get($equipamento_id);
                    $horario_get = $this->biometria_horario_model->get($horarios[$equipamento_id]);
                    if (!empty($equipamento_get) && !empty($horario_get))
                    {
                        $retorno[] = $this->_enviar_liberacao($equipamento_get, $horario_get, $usuario_get, TRUE);
                    }
                }
                $retornos = implode('<br>', $retorno);
                if (strstr($retornos, 'Erro'))
                {
                    $this->dados['erro'] = $retornos;
                }
                else
                {
                    $this->redirect('ti_biometria/listar_liberacao', 'sucesso', $retornos);
                }
            }
        }
        $this->dados['usuarios'] = array('') + $this->biometria_usuario_model->as_dropdown('NOME')->get_all();
        $this->dados['horarios'] = $this->biometria_horario_model->as_dropdown('NOME')->get_all();
        $this->dados['equipamentos'] = $this->biometria_equipamento_model->as_dropdown('NOME')->get_all();
        $this->render('adicionar_liberacao');
    }

    /**
     * reenviar_liberacao
     * Tela reenviar um ou mais usuários aos respectivos equipamentos previamente cadastrados
     */
    function reenviar_liberacao ()
    {
        $get = $this->input->get();
        if (!empty($get['liberacoes']))
        {
            $retorno = array();
            $liberacoes = explode('_', $get['liberacoes']);
            foreach ($liberacoes as $liberacao)
            {
                if ($liberacao)
                {
                    $partes = explode('-', $liberacao);
                    $liberacao_where = array();
                    $liberacao_where['BIOMETRIA_EQUIPAMENTO_ID'] = $partes[0];
                    $liberacao_where['BIOMETRIA_USUARIO_ID'] = $partes[1];
                    $liberacao_get = $this->biometria_usuario_equipamento_model->get($liberacao_where);
                    $usuario_get = $this->biometria_usuario_model->get($liberacao_get->BIOMETRIA_USUARIO_ID);
                    $equipamento_get = $this->biometria_equipamento_model->get($liberacao_get->BIOMETRIA_EQUIPAMENTO_ID);
                    $horario_get = $this->biometria_horario_model->get($liberacao_get->BIOMETRIA_HORARIO_ID);
                    $retorno[] = $this->_enviar_liberacao($equipamento_get, $horario_get, $usuario_get, FALSE);
                }
            }
            $retornos = implode('<br>', $retorno);
            $retorno_tipo = (strstr($retornos, 'Erro')) ? 'erro' : 'sucesso';
            $this->redirect('ti_biometria/listar_liberacao', $retorno_tipo, $retornos);
        }
    }

    /**
     * _enviar_liberacao
     * Função privada utilizada pelas telas de adicionar e reenviar liberação
     * @param object $equipamento_get
     * @param object $horario_get
     * @param object $usuario_get
     * @param boolean $insert
     * @return string
     */
    function _enviar_liberacao ($equipamento_get, $horario_get, $usuario_get, $insert)
    {
        $this->load->library('controlid_webservice');
        $this->controlid_webservice->config($equipamento_get->IP, $equipamento_get->PROTOCOLO);
        $sessao = $this->controlid_webservice->login($equipamento_get->USUARIO, $equipamento_get->SENHA);
        
        $ret = $this->controlid_webservice->get_user_image();
        
        //VERIFICA SE O EQUIPAMENTO ESTÁ CONECTADO E COM AUTENTICAÇÃO VÁLIDA
        if ($sessao)
        {
            $usuario_digital_where = array();
            $usuario_digital_where['BIOMETRIA_USUARIO_ID'] = $usuario_get->ID;
            $usuario_digital_get_all = $this->biometria_usuario_digital_model->get_all($usuario_digital_where);
            if ($equipamento_get->TIPO == 'IDCLASS')
            {
                $usuario_arr = array();
                $usuario_arr['code'] = (int) $usuario_get->ID;
                $usuario_arr['pis'] = (float) preg_replace('/[^0-9]/', '', $usuario_get->CPF);
                $usuario_arr['name'] = $usuario_get->NOME;
                if (!empty($usuario_digital_get_all))
                {
                    foreach ($usuario_digital_get_all as $usuario_digital)
                    {
                        $template = '';
                        $template .= $usuario_digital->TEMPLATE_0;
                        $template .= $usuario_digital->TEMPLATE_1;
                        $template .= $usuario_digital->TEMPLATE_2;
                        $usuario_arr['templates'][] = $template;
                    }
                }
                $this->controlid_webservice->add_users($usuario_arr);
                $enviado = 1;
                $retorno = $equipamento_get->NOME . ' - Gravado com sucesso no banco de dados e no equipamento';
            }
            else
            {
                $usuario_arr = array();
                $usuario_arr['id'] = (int) $usuario_get->ID;
                $usuario_arr['registration'] = $usuario_get->CPF;
                $usuario_arr['name'] = $usuario_get->NOME;
                $this->controlid_webservice->create_objects('users', $usuario_arr);
                //CADASTRA O HORÁRIO CASO NÃO EXISTIR
                $horario_arr = array();
                $horario_arr['id'] = (int) $horario_get->ID;
                $horario_arr['name'] = $horario_get->NOME;
                $this->controlid_webservice->create_objects('time_zones', $horario_arr);
                //CADASTRA A FAIXA DO HORÁRIO (EX: 08:00 AS 18:00 SEG,TER,QUA,QUI,SEX)
                $horario_faixa_where = array(
                    'BIOMETRIA_HORARIO_ID' => $horario_get->ID
                );
                $horario_faixa_get_all = $this->biometria_horario_faixa_model->get_all($horario_faixa_where);
                if (!empty($horario_faixa_get_all))
                {
                    foreach ($horario_faixa_get_all as $horario_faixa)
                    {
                        $horario_faixa_arr = array();
                        $horario_faixa_arr['id'] = (int) $horario_faixa->ID;
                        $horario_faixa_arr['time_zone_id'] = (int) $horario_get->ID;
                        $horario_faixa_arr['start'] = (int) $horario_faixa->INICIO;
                        $horario_faixa_arr['end'] = (int) $horario_faixa->FIM;
                        $horario_faixa_arr['sun'] = (int) $horario_faixa->DOM;
                        $horario_faixa_arr['mon'] = (int) $horario_faixa->SEG;
                        $horario_faixa_arr['tue'] = (int) $horario_faixa->TER;
                        $horario_faixa_arr['wed'] = (int) $horario_faixa->QUA;
                        $horario_faixa_arr['thu'] = (int) $horario_faixa->QUI;
                        $horario_faixa_arr['fri'] = (int) $horario_faixa->SEX;
                        $horario_faixa_arr['sat'] = (int) $horario_faixa->SAB;
                        $this->controlid_webservice->create_objects('time_spans', $horario_faixa_arr);
                    }
                }
                //CRIA UMA REGRA DE ACESSO PARA O USUÁRIO
                $access_rules_arr = array();
                $access_rules_arr['id'] = (int) $usuario_get->ID;
                $access_rules_arr['name'] = $usuario_get->NOME;
                $access_rules_arr['type'] = 1;
                $access_rules_arr['priority'] = 0;
                $this->controlid_webservice->create_objects('access_rules', $access_rules_arr);
                //VINCULA ESSA REGRA COM O USUÁRIO
                $user_access_rules_arr = array();
                $user_access_rules_arr['user_id'] = (int) $usuario_get->ID;
                $user_access_rules_arr['access_rule_id'] = (int) $usuario_get->ID;
                $this->controlid_webservice->create_objects('user_access_rules', $user_access_rules_arr);
                //VINCULA TAMBÉM A REGRA COM O HORÁRIO
                $access_rule_time_zones_arr = array();
                $access_rule_time_zones_arr['access_rule_id'] = (int) $usuario_get->ID;
                $access_rule_time_zones_arr['time_zone_id'] = (int) $horario_get->ID;
                $this->controlid_webservice->create_objects('access_rule_time_zones', $access_rule_time_zones_arr);
                //VINCULA O USUÁRIO NO GRUPO PADRÃO DO CONTROLID
                $user_groups_arr = array();
                $user_groups_arr['user_id'] = (int) $usuario_get->ID;
                $user_groups_arr['group_id'] = 1;
                $this->controlid_webservice->create_objects('user_groups', $user_groups_arr);
                //HABILITA O ACESSO A TODOS OS PORTALS DO EQUIPAMENTO
                $portals = $this->controlid_webservice->load_objects('portals');
                if (!empty($portals->portals))
                {
                    foreach ($portals->portals as $portal)
                    {
                        $portal_access_rules_arr = array();
                        $portal_access_rules_arr['access_rule_id'] = (int) $usuario_get->ID;
                        $portal_access_rules_arr['portal_id'] = (int) $portal->id;
                        $this->controlid_webservice->create_objects('portal_access_rules', $portal_access_rules_arr);
                    }
                }
                //CADASTRA TODAS AS DIGITAIS DO USUÁRIO
                if (!empty($usuario_digital_get_all))
                {
                    foreach ($usuario_digital_get_all as $usuario_digital)
                    {
                        $templates_arr = array();
                        $templates_arr['template'] = '';
                        $templates_arr['template'] .= $usuario_digital->TEMPLATE_0;
                        $templates_arr['template'] .= $usuario_digital->TEMPLATE_1;
                        $templates_arr['template'] .= $usuario_digital->TEMPLATE_2;
                        $templates_arr['user_id'] = (int) $usuario_get->ID;
                        $this->controlid_webservice->create_objects('templates', $templates_arr);
                    }
                }
                $enviado = 1;
                $retorno = $equipamento_get->NOME . ' - Gravado com sucesso no banco de dados e no equipamento';
            }
        }
        else
        {
            $enviado = 0;
            $retorno = $equipamento_get->NOME . ' - Usuário foi gravado no banco de dados, mas, não foi enviado para o equipamento por problemas de conectividade';
        }
        $usuario_equipamento_where = array();
        $usuario_equipamento_where['BIOMETRIA_USUARIO_ID'] = $usuario_get->ID;
        $usuario_equipamento_where['BIOMETRIA_EQUIPAMENTO_ID'] = $equipamento_get->ID;
        $usuario_equipamento_where['BIOMETRIA_HORARIO_ID'] = $horario_get->ID;
        if ($insert)
        {
            $usuario_equipamento_where['ENVIADO'] = $enviado;
            $this->biometria_usuario_equipamento_model->insert($usuario_equipamento_where);
        }
        else
        {
            $usuario_equipamento_arr = array();
            $usuario_equipamento_arr['ENVIADO'] = $enviado;
            $this->biometria_usuario_equipamento_model->update($usuario_equipamento_arr, $usuario_equipamento_where);
        }
        return $retorno;
    }

    /**
     * excluir_liberacao
     * Função utilizada para excluir uma liberação cadastrada
     * @param int $equipamento_id
     * @param int $usuario_id
     */
    function excluir_liberacao ($equipamento_id, $usuario_id)
    {
        $retorno = $this->_excluir_liberacao($equipamento_id, $usuario_id);
        $retorno_tipo = (strstr($retorno, 'Erro')) ? 'erro' : 'sucesso';
        $this->redirect('ti_biometria/listar_liberacao', $retorno_tipo, $retorno);
    }

    /**
     * excluir_liberacao_selecionado
     * Função utilizada para excluir uma ou mais liberações selecionadas
     */
    function excluir_liberacao_selecionado ()
    {
        $get = $this->input->get();
        if (!empty($get['liberacoes']))
        {
            $retorno = array();
            $liberacoes = explode('_', $get['liberacoes']);
            foreach ($liberacoes as $liberacao)
            {
                if ($liberacao)
                {
                    $partes = explode('-', $liberacao);
                    $equipamento_id = $partes[0];
                    $usuario_id = $partes[1];
                    $retorno[] = $this->_excluir_liberacao($equipamento_id, $usuario_id);
                }
            }
            $retornos = implode('<br>', $retorno);
            $retorno_tipo = (strstr($retornos, 'Erro')) ? 'erro' : 'sucesso';
            $this->redirect('ti_biometria/listar_liberacao', $retorno_tipo, $retornos);
        }
    }

    /**
     * _excluir_liberacao
     * Função privada utilizada pela função de excluir liberação e excluir liberações selecionadas
     * @param int $equipamento_id
     * @param int $usuario_id
     * @return string
     */
    function _excluir_liberacao ($equipamento_id, $usuario_id)
    {
        $this->load->library('controlid_webservice');
        $retorno = '';
        $equipamento_get = $this->biometria_equipamento_model->get($equipamento_id);
        if (!empty($equipamento_get))
        {
            $this->controlid_webservice->config($equipamento_get->IP, $equipamento_get->PROTOCOLO);
            $sessao = $this->controlid_webservice->login($equipamento_get->USUARIO, $equipamento_get->SENHA);
            if (!empty($sessao))
            {
                if ($equipamento_get->TIPO == 'IDCLASS')
                {
                    $usuario_get = $this->biometria_usuario_model->get($usuario_id);
                    $usuario_arr = array((float) preg_replace('/[^0-9]/', '', $usuario_get->CPF));
                    $destroy_user = $this->controlid_webservice->remove_users($usuario_arr);
                    $changes = isset($destroy_user) ? 1 : 0;
                }
                else
                {
                    $usuario_arr = array();
                    $usuario_arr['id'] = array((int) $usuario_id);
                    $destroy_user = $this->controlid_webservice->destroy_objects('users', $usuario_arr);
                    $changes = $destroy_user->changes;
                }
                $liberacao_where = array();
                $liberacao_where['BIOMETRIA_EQUIPAMENTO_ID'] = $equipamento_id;
                $liberacao_where['BIOMETRIA_USUARIO_ID'] = $usuario_id;
                $liberacao_get = $this->biometria_usuario_equipamento_model->get($liberacao_where);
                if ($liberacao_get->ENVIADO == 0)
                {
                    $this->biometria_usuario_equipamento_model->delete($liberacao_where);
                    $retorno = $equipamento_get->NOME . ' - Usuário excluído com sucesso do banco de dados';
                }
                else
                {
                    if ($changes > 0)
                    {
                        $this->biometria_usuario_equipamento_model->delete($liberacao_where);
                        $retorno = $equipamento_get->NOME . ' - Usuário excluído com sucesso do banco de dados e no equipamento';
                    }
                    else
                    {
                        $retorno = $equipamento_get->NOME . ' - Erro: Não foi possível excluir o usuário no equipamento';
                    }
                }
            }
            else
            {
                $retorno = $equipamento_get->NOME . ' - Erro: equipamento sem conectividade';
            }
        }
        else
        {
            $retorno = 'Erro: equipamento cód. ' . $equipamento_id . ' não encontrado';
        }
        return $retorno;
    }

    ////////////////////////////////
    // Fila
    ////////////////////////////////

    /**
     * listar_fila
     * Tela que lista todas as operações não concluídas que acabaram caindo na fila
     */
    function listar_fila ()
    {
        $this->dados['total'] = $this->biometria_fila_model->count_rows();
        $this->dados['lista'] = $this->biometria_fila_model->get_all();
        $this->dados['equipamentos'] = $this->biometria_equipamento_model->as_dropdown('NOME')->get_all();
        $this->render('listar_fila');
    }

    /**
     * excluir_fila
     * Função para excluir uma operação da fila
     * @param int $id
     */
    function excluir_fila ($id)
    {
        $delete = $this->biometria_fila_model->delete($id);
        if ($delete)
        {
            $this->redirect('ti_biometria/listar_fila', 'sucesso', 'Comando excluído com sucesso');
        }
        else
        {
            $this->redirect('ti_biometria/listar_fila', 'erro', 'Erro ao excluir');
        }
    }

    /**
     * processar_fila
     * Função utilizada para tentar o envio da operação para o equipamento
     */
    function processar_fila ()
    {
        $retorno_tipo = 'sucesso';
        $retorno = 'Fila processada com sucesso';
        $lista = $this->biometria_fila_model->get_all();
        if (!empty($lista))
        {
            foreach ($lista as $item)
            {
                $equipamento_get = $this->biometria_equipamento_model->get($item->BIOMETRIA_EQUIPAMENTO_ID);
                $this->controlid_webservice->config($equipamento_get->IP, $equipamento_get->PROTOCOLO);
                $sessao = $this->controlid_webservice->login($equipamento_get->USUARIO, $equipamento_get->SENHA);
                if ($sessao)
                {
                    $this->controlid_webservice->comando($item->FUNCAO, $item->COMANDO);
                    $this->biometria_fila_model->delete($item->ID);
                }
                else
                {
                    $retorno = 'Fila parcialmente processada';
                }
            }
        }
        else
        {
            $retorno_tipo = 'erro';
            $retorno = 'Nenhum item para ser processado';
        }
        $this->redirect('ti_biometria/listar_fila', $retorno_tipo, $retorno);
    }
    
    function dev_teste(){
    	$this->load->library('controlid_webservice');
    	$this->load->library('dev_utils');
    	//240x400
    	$this->controlid_webservice->config('192.168.99.49', 'https');
    	
    	$binFile = $this->dev_utils->ler_arquivo("um_template.txt", "rb"); //read binary
    	
//     	function template_extract ($file, $width, $height)
//     	{
//     		$param = '?width=' . $width . '&height=' . $height;
//     		return $this->_curl('/template_extract.fcgi' . $param, $file, true);
//     	}
    	
    	//Login
    	$session = $this->controlid_webservice->login("admin", "admin");
    	
    	$ret = $this->controlid_webservice->comando('/template_extract.fcgi',
    												$binFile,
    												true);
    	 
    	
    	//Verificar sess�o
    	//$ret = $this->controlid_webservice->comando('/session_is_valid.fcgi',json_encode(["session" => "4vyLTUf/sGDMTM5ZnEe3Sx6f"]));
    	
    	//Login
    	//$ret = $this->controlid_webservice->comando('/login.fcgi',json_encode(["login" => "admin",
    	//																	   "password" => "admin"]));
    }

}
