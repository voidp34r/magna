<?php

/**
 * Description of Frotas_agregados
 *
 * @author Administrador
 */
class Frotas_agregados extends MY_Controller
{

    var $pre;

    public function __construct ()
    {
        $this->load->model('agregado_cadastro_model');
        $this->load->model('agregado_gerenciador_risco_model');
        $this->load->model('agregado_motorista_model');
        $this->load->model('agregado_proprietario_model');
        $this->load->model('agregado_status_model');
        $this->load->model('agregado_veiculo_model');
        $this->load->model('agregado_analise_model');
        $this->load->model('agregado_analise_deferido_model');
        $this->load->model('agregado_modelo_model');
        $this->load->model('agregado_contrato_model');
        $this->load->model('agregado_contrato_campo_model');
        $this->load->library('softran_oracle');

        $this->dados['modulo_nome'] = 'Frotas > Agregados';
        $this->publico = array(
            'ver_solicitacao_json',
            'ver_contrato_data_json',
        );
        $this->pre['upload_proprietario'] = array(
            'CONTRATO_SERVICO' => 'Contrato de prestação de serviço',
            'CONTRATO_SOCIAL' => 'Contrato social',
            'DADOS_EMPRESA' => 'Dados da empresa (SINTEGRA)',
            'DADOS_BANCARIOS' => 'Dados bancários',
            'DOCUMENTO_ANTT' => 'ANTT',
        );
        $this->pre['upload_veiculo'] = array(
            'DOCUMENTO_VEICULO' => 'Documento do veículo',
            'ANTT' => 'ANTT',
        );
        $this->pre['upload_motorista'] = array(
            'CNH' => 'CNH',
            'COMPROVANTE_RESIDENCIA' => 'Comprovante de residência',
            'PIS' => 'PIS',
            'CIENCIA' => 'Ciência de cadastro'
        );
        parent::__construct();
        
        
        $this->dados['modulo_menu'] = array(
            'Contrato' => 'listar_contrato',
            'Solicitação' => 'listar_solicitacao'
        );

         
        $Analise  = $this->verifica_gatilho('AGREGADO_ANALISE');
        $Juridico = $this->verifica_gatilho('AGREGADO_JURIDICO');
        
        if($Juridico){
            $this->dados['modulo_menu']['Jurídico'] = 'listar_deferido';
        }
        if($Analise){
            $this->dados['modulo_menu']['Frotas'] = 'listar';
            $this->dados['modulo_menu']['Modelos'] = 'listar_modelo';
        }

    }

    function index ()
    {
        $this->redirect('frotas_agregados/listar_solicitacao');
    }

    ////////////////////////////////
    // Contrato
    ////////////////////////////////

    /**
     * listar_contrato
     * Listagem dos contratos cadastrados
     * @param int $page
     */
    function listar_contrato ($page = 1)
    {
        $total = $this->agregado_contrato_model
                ->where('USUARIO_ID', $this->sessao['usuario_id'])
                ->count_rows();
        $lista = $this->agregado_contrato_model
                ->where('USUARIO_ID', $this->sessao['usuario_id'])
                ->order_by('DATAHORA', 'DESC')
                ->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->agregado_contrato_model->all_pages;
        $this->render('listar_contrato');
    }

    function ver_contrato_data_json ($val1, $val2 = null)
    {
        if ($val2)
        {
            $val1 .= '/' . $val2;
        }
        $where = array(
            'CAMPO' => (strlen($val1) == 14) ? 'CPF' : 'CNPJ',
            'VALOR' => $val1,
        );
        $contrato_campo = $this->agregado_contrato_campo_model->where($where)->get();
        if (!empty($contrato_campo))
        {
            $contrato = $this->agregado_contrato_model->get($contrato_campo->AGREGADO_CONTRATO_ID);
            if (!empty($contrato))
            {
                echo json_encode(data_oracle_para_web($contrato->DATAHORA, 0));
            }
        }
    }

    /**
     * adicionar_contrato
     * Primeira tela para adicionar um novo contrato
     */
    function adicionar_contrato ()
    {
        $post = $this->input->post();

        if (!empty($post))
        {
            if ($post['tipo'] == 'DISTRATO')
            {
                if ($post['pessoa'] == 'PJ')
                {
                    $this->redirect('frotas_agregados/adicionar_contrato_pj_distrato');
                }
                else
                {
                    $this->redirect('frotas_agregados/adicionar_contrato_pf_distrato');
                }
            }
            else
            {
                if ($post['pessoa'] == 'PJ')
                {
                    $this->redirect('frotas_agregados/adicionar_contrato_pj');
                }
                if ($post['pessoa'] == 'PF')
                {
                    if ($post['motorista_proprietario'])
                    {
                        $this->redirect('frotas_agregados/adicionar_contrato_pf_nomeacao');
                    }
                    else
                    {
                        $this->redirect('frotas_agregados/adicionar_contrato_pf');
                    }
                }
            }
        }
        $this->render('adicionar_contrato');
    }

    /**
     * adicionar_contrato_pj
     * Tela para adicionar um contrato pessoa jurídica
     */
    function adicionar_contrato_pj ()
    {
        $this->_adicionar_contrato('PJ');
    }

    /**
     * adicionar_contrato_pj_distrato
     * Tela para adicionar um distrato pessoa jurídica
     */
    function adicionar_contrato_pj_distrato ()
    {
        $this->_adicionar_contrato('PJ_DISTRATO');
    }

    /**
     * adicionar_contrato_pf
     * Tela para adicionar um contrato pessoa física
     */
    function adicionar_contrato_pf ()
    {
        $this->_adicionar_contrato('PF');
    }

    /**
     * adicionar_contrato_pf_nomeacao
     * Tela para adicionar um contrato pessoa física com nomeação
     */
    function adicionar_contrato_pf_nomeacao ()
    {
        $this->_adicionar_contrato('PF_NOMEACAO');
    }

    /**
     * adicionar_contrato_pf_distrato
     * Tela para adicionar um distrato pessoa física
     */
    function adicionar_contrato_pf_distrato ()
    {
        $this->_adicionar_contrato('PF_DISTRATO');
    }

    /**
     * _adicionar_contrato_validation
     * Função privada para validação de contrato
     */
    function _adicionar_contrato_validation ($chave)
    {
        if ($chave == 'PF' || $chave == 'PF_NOMEACAO' || $chave == 'PF_DISTRATO')
        {
            $this->form_validation->set_rules('CPF', 'CPF', 'trim|required|valid_cpf');
            $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
            $this->form_validation->set_rules('RG', 'RG', 'trim|required');
            $this->form_validation->set_rules('RG_UF', 'Órgão expedidor', 'trim|required');
            $this->form_validation->set_rules('ESTADO', 'Estado', 'trim|required');
            $this->form_validation->set_rules('MUNICIPIO', 'Município', 'trim|required');
            $this->form_validation->set_rules('NACIONALIDADE', 'Nacionalidade', 'trim|required');
            $this->form_validation->set_rules('IDADE', 'Idade', 'trim|required');
            $this->form_validation->set_rules('ESTADO_CIVIL', 'Estado civil', 'trim|required');
        }

        if ($chave == 'PF_NOMEACAO')
        {
            $this->form_validation->set_rules('NOMEADO_CPF', 'CPF', 'trim|required|valid_cpf');
            $this->form_validation->set_rules('NOMEADO_NOME', 'Nome', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_ESTADO_CIVIL', 'Estado civil', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_PROFISSAO', 'Profissão', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_RG', 'RG', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_RG_UF', 'Órgão expedidor', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_ENDERECO', 'Endereço', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_NUMERO', 'Número', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_ESTADO', 'Estado', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_MUNICIPIO', 'Município', 'trim|required');
            $this->form_validation->set_rules('NOMEADO_CNH', 'CNH', 'trim|required');
            $this->form_validation->set_rules('VEICULO_MARCA', 'Marca', 'trim|required');
            $this->form_validation->set_rules('VEICULO_MODELO', 'Modelo', 'trim|required');
            $this->form_validation->set_rules('VEICULO_PLACA', 'Placa', 'trim|required');
            $this->form_validation->set_rules('VEICULO_CHASSI', 'Chassi', 'trim|required');
        }
        if ($chave == 'PJ' || $chave == 'PJ_DISTRATO')
        {
            $this->form_validation->set_rules('CNPJ', 'CNPJ', 'trim|required|valid_cnpj');
            $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
            $this->form_validation->set_rules('ESTADO', 'Estado', 'trim|required');
            $this->form_validation->set_rules('MUNICIPIO', 'Município', 'trim|required');
        }
        if ($chave == 'PJ_DISTRATO')
        {
            $this->form_validation->set_rules('CEP', 'CEP', 'trim|required');
            $this->form_validation->set_rules('BAIRRO', 'Bairro', 'trim|required');
            $this->form_validation->set_rules('ENDERECO', 'Endereço', 'trim|required');
            $this->form_validation->set_rules('NUMERO', 'Número', 'trim|required');
            $this->form_validation->set_rules('INSCRICAO_ESTADUAL', 'Inscrição Estadual', 'trim|required');
            $this->form_validation->set_rules('SOCIO_ADMINISTRADOR', 'Sócio-administrador', 'trim|required');
            $this->form_validation->set_rules('SOCIO_CPF', 'CPF', 'trim|required|valid_cpf');
            $this->form_validation->set_rules('SOCIO_RG', 'RG', 'trim|required');
        }
        if ($chave == 'PF_DISTRATO' || $chave == 'PJ_DISTRATO')
        {
            $this->form_validation->set_rules('DATA_CONTRATACAO', 'Data da contratação', 'trim|required|valid_date[d/m/Y]');
        }
    }

    /**
     * _adicionar_contrato
     * Função privada utilizada pela tela de adicionar e editar contrato
     * @param string $chave
     */
    function _adicionar_contrato ($chave)
    {
        $this->_adicionar_contrato_validation($chave);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $contrato = array();
            $contrato['CHAVE'] = $chave;
            $contrato['DATAHORA'] = date('YmdHis');
            $contrato['USUARIO_ID'] = $this->sessao['usuario_id'];
            $contrato['NOME'] = !empty($post['NOME']) ? $post['NOME'] : '';
            $id = $this->agregado_contrato_model->insert($contrato);
            if ($id)
            {
                foreach ($post as $campo => $valor)
                {
                    $contrato_campo = array();
                    $contrato_campo['AGREGADO_CONTRATO_ID'] = $id;
                    $contrato_campo['CAMPO'] = $campo;
                    $contrato_campo['VALOR'] = $valor;
                    $this->agregado_contrato_campo_model->insert($contrato_campo);
                }
                $this->session->set_flashdata('sucesso', 'Contrato gravado com sucesso');
                $this->redirect('frotas_agregados/listar_contrato');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('formulario_contrato_' . strtolower($chave));
    }

    /**
     * editar_contrato
     * Tela para editar um contrato cadastrado
     * @param int $id
     */
    function editar_contrato ($id)
    {
        $contrato_where = array(
            'ID' => $id,
            'USUARIO_ID' => $this->sessao['usuario_id'],
        );
        $contrato = $this->agregado_contrato_model->get($contrato_where);
        if (empty($contrato))
        {
            exit('Você não tem permissão para gerar esse contrato');
        }
        $this->_adicionar_contrato_validation($contrato->CHAVE);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $contrato_update = array();
            $contrato_update['NOME'] = !empty($post['NOME']) ? $post['NOME'] : '';
            $update = $this->agregado_contrato_model->update($contrato_update, $id);
            if ($update)
            {
                $contrato_campo_where = array(
                    'AGREGADO_CONTRATO_ID' => $id,
                );
                $this->agregado_contrato_campo_model->delete($contrato_campo_where);
                foreach ($post as $campo => $valor)
                {
                    $contrato_campo = array();
                    $contrato_campo['AGREGADO_CONTRATO_ID'] = $id;
                    $contrato_campo['CAMPO'] = $campo;
                    $contrato_campo['VALOR'] = $valor;
                    $this->agregado_contrato_campo_model->insert($contrato_campo);
                }
                $this->session->set_flashdata('sucesso', 'Contrato gravado com sucesso');
                $this->redirect('frotas_agregados/listar_contrato');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $contrato_campos_where = array(
            'AGREGADO_CONTRATO_ID' => $id,
        );
        $contrato_campos = $this->agregado_contrato_campo_model->get_all($contrato_campos_where);
        foreach ($contrato_campos as $contrato_campo)
        {
            $_POST[$contrato_campo->CAMPO] = $contrato_campo->VALOR;
        }
        $this->render('formulario_contrato_' . strtolower($contrato->CHAVE));
    }

    /**
     * gerar_contrato
     * Tela para gerar um contrato conforme o modelo
     * @param int $id
     */
    function gerar_contrato ($id)
    {
        $this->load->model('geral_upload_model');
        
        $contrato_where = array('ID' => $id,
            					'USUARIO_ID' => $this->sessao['usuario_id']);
        
        $contrato = $this->agregado_contrato_model->get($contrato_where);
        
        if (empty($contrato))
            exit('Você não tem permissão para gerar esse contrato');
        
        $modelo_where = array('CHAVE' => $contrato->CHAVE,);
        
        $modelo = $this->agregado_modelo_model->get($modelo_where);
        $documento = $this->geral_upload_model->ler($this->agregado_modelo_model->table, $modelo->ID, $contrato->CHAVE);
        
        if (!empty($documento)){
            $campos_where = array('AGREGADO_CONTRATO_ID' => $id);
            $campos = $this->agregado_contrato_campo_model->get_all($campos_where);
            
            foreach ($campos as $campo){
                $documento = str_replace('*|' . $campo->CAMPO . '|*', $campo->VALOR, $documento);
            }
            
            $data = date('d') . ' de ';
            $data .= $this->lang->line('cal_' . strtolower(date('F')));
            $data .= ' de ' . date('Y');
            $documento = str_replace('*|DATA|*', $data, $documento);
            
            if (strpos($documento, '_FILIAL|*')){
            	$this->load->model('ti_permissoes/usuario_model');
            	$usuario = $this->usuario_model->get($contrato->USUARIO_ID);
            	if (!$usuario)
            		exit();

            	$empresa = $this->softran_oracle->getEnderecoFilial($usuario->CDEMPRESA);
            	if (!$empresa)
            		exit();
            		
            	$documento = str_replace('*|MUNICIPIO_FILIAL|*', $empresa->DSLOCAL, $documento);
            	$documento = str_replace('*|ESTADO_FILIAL|*', $empresa->DSUF, $documento);
            }
            
            $this->dados['the_view_content'] = $documento;
            $this->render('templates/print_view', null);
            
        } else {
            exit('Esse modelo de documento não existe');
        }
    }

    ////////////////////////////////
    // Solicitação
    ////////////////////////////////

    /**
     * listar
     * Tela para listar as solicitações para o Frotas analisar
     * @param int $page
     */
    function listar ($page = 1)
    {
        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['titulo'] = 'Frotas - listar';
        $where = array(
            'ABERTO' => 0
        );
        $order = array(
            'AGREGADO_STATUS_ID' => 'DESC',
            'DATAHORA' => 'DESC'
        );
        $this->_listar($where, $order, $page);
    }

    /**
     * listar_solicitacao
     * Tela para listar as solicitações do usuário
     * @param int $page
     */
    function listar_solicitacao ($page = 1)
    {
        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['titulo'] = 'Listar solicitações';
        $ver_tudo = $this->verifica_gatilho('AGREGADO_VER_TUDO');

        $where = array();
        if (!$ver_tudo)
        {
            $where['USUARIO_ID'] = $this->sessao['usuario_id'];
        }
        $order = array(
            'DATAHORA' => 'DESC'
        );
        $this->_listar($where, $order, $page);
    }

    /**
     * listar_deferido
     * Tela para listar as solicitações deferidas pelo frotas e pendentes para 
     * análise do jurídico
     * @param int $page
     */
    function listar_deferido ($page = 1)
    {
        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['titulo'] = 'Jurídico - listar';
        $where = array(
            'AGREGADO_STATUS_ID' => 1
        );
        $order = array(
            'AGREGADO_STATUS_ID_JURIDICO' => 'DESC',
            'DATAHORA' => 'DESC'
        );
        $this->_listar($where, $order, $page);
    }

    /**
     * _listar
     * Função privada utilizada pelas listagens das solicitações
     * @param array $where
     * @param string $order
     * @param int $page
     */
    function _listar ($where, $order, $page)
    {
        $total = $this->agregado_cadastro_model
                ->where($where)
                ->count_rows();
        $lista = $this->agregado_cadastro_model
                ->where($where)
                ->order_by($order)
                ->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->agregado_cadastro_model->all_pages;
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->dados['proprietarios'] = $this->agregado_proprietario_model->as_dropdown('NOME')->get_all();
        $this->dados['status'] = $this->agregado_status_model->as_dropdown('NOME')->get_all();
        $this->render('listar');
    }

    /**
     * ver_solicitacao_json
     * Função para obter dados da solicitação utilizada pelo cadastro de motorista
     * @param int $id
     */
    function ver_solicitacao_json ($id)
    {
        $where = array(
            'ID' => $id,
            'USUARIO_ID' => $this->sessao['usuario_id'],
        );
        $cadastro = $this->agregado_cadastro_model->get($where);
        if (!empty($cadastro))
        {
            $proprietario = $this->agregado_proprietario_model->get($cadastro->AGREGADO_PROPRIETARIO_ID);
            $solicitacao = array();
            $solicitacao['cadastro'] = $cadastro;
            $solicitacao['proprietario'] = $proprietario;
            echo json_encode($solicitacao);
        }
    }

    /**
     * ver_solicitacao
     * Tela para visualizar uma solicitação
     * @param int $id
     */
    function ver_solicitacao ($id)
    {
        $this->load->model('geral_upload_model');
        $this->load->model('geral_municipio_model');
        $this->load->model('geral_gerenciador_risco_model');
        $this->load->model('ti_permissoes/usuario_model');
        $this->_analisar_solicitacao($id);
        $cadastro = $this->agregado_cadastro_model->get($id);
        $this->dados['id'] = $id;
        $this->dados['cadastro'] = $cadastro;
        $this->dados['municipios'] = $this->geral_municipio_model->listar_tudo();
        $this->dados['filial_nome'] = $cadastro->CDEMPRESA;
        $this->dados['usuario_nome'] = $this->usuario_model->get($cadastro->USUARIO_ID)->NOME;
        $this->dados['proprietario'] = $this->agregado_proprietario_model->get($cadastro->AGREGADO_PROPRIETARIO_ID);
        $this->dados['status'] = $this->agregado_status_model->as_dropdown('NOME')->get_all();
        $this->dados['usuarios'] = $this->usuario_model->as_dropdown('NOME')->get_all();
        $where = array('AGREGADO_CADASTRO_ID' => $id);
        $this->dados['analises'] = $this->agregado_analise_model->order_by('DATAHORA')->get_all($where);
        $this->dados['veiculos'] = $this->agregado_veiculo_model->get_all($where);
        $this->dados['motoristas'] = $this->agregado_motorista_model->get_all($where);
        $this->dados['gerenciadores_risco'] = $this->agregado_gerenciador_risco_model->get_all($where);
        $this->dados['gerenciadores_nome'] = $this->geral_gerenciador_risco_model->as_dropdown('NOME')->get_all();
        $this->dados['motoristas_nome'] = array();
        if (!empty($this->dados['motoristas']))
        {
            foreach ($this->dados['motoristas'] as $motorista)
            {
                $upload_where = array(
                    'TABELA' => $this->agregado_motorista_model->table,
                    'TABELA_ID' => $motorista->ID,
                );
                $this->dados['motoristas_upload'][$motorista->ID] = $this->geral_upload_model
                        ->order_by('LABEL, DATAHORA')
                        ->get_all($upload_where);
                $this->dados['motoristas_nome'][$motorista->ID] = $motorista->NOME;
            }
        }
        if (!empty($this->dados['veiculos']))
        {
            foreach ($this->dados['veiculos'] as $veiculo)
            {
                $upload_where = array(
                    'TABELA' => $this->agregado_veiculo_model->table,
                    'TABELA_ID' => $veiculo->ID,
                );
                $this->dados['veiculos_upload'][$veiculo->ID] = $this->geral_upload_model
                        ->order_by('LABEL, DATAHORA')
                        ->get_all($upload_where);
            }
        }
        if (!empty($this->dados['proprietario']))
        {
            $upload_where = array(
                'TABELA' => $this->agregado_proprietario_model->table,
                'TABELA_ID' => $this->dados['proprietario']->ID,
            );
            $this->dados['proprietario_upload'] = $this->geral_upload_model
                    ->order_by('LABEL, DATAHORA')
                    ->get_all($upload_where);
        }
        $this->dados['pre'] = $this->pre;
        $this->dados['gatilho_analise'] = $this->verifica_gatilho('AGREGADO_ANALISE');
        $this->render('ver_solicitacao');
    }

    /**
     * _analisar_solicitacao
     * Função privada para cadastrar uma análise de solicitação
     * @param int $id
     */
    function _analisar_solicitacao ($id)
    {
        $this->load->model('geral_tarefa_model');
        $this->load->model('geral_gerenciador_risco_model');
        $this->form_validation->set_rules('AGREGADO_STATUS_ID', 'Status', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            if (!empty($post['analise']))
            {
                $analises = $post['analise'];
                unset($post['analise']);
            }
            $post['AGREGADO_CADASTRO_ID'] = $id;
            $post['USUARIO_ID'] = $this->sessao['usuario_id'];
            $post['DATAHORA'] = date('YmdHis');
            $post['TIPO'] = 'FROTAS';
            $analise_id = $this->agregado_analise_model->insert($post);
            if ($analise_id)
            {
                $update = array('AGREGADO_STATUS_ID' => $post['AGREGADO_STATUS_ID']);
                $this->agregado_cadastro_model->update($update, $id);
                $this->_abrir_fechar_solicitacao($id, 1);
                if (!empty($analises))
                {
                    foreach ($analises as $modelo => $cadastros)
                    {
                        foreach ($cadastros as $cadastro_id => $campos)
                        {
                            foreach ($campos as $campo => $on)
                            {
                                $analise_deferido = array();
                                $analise_deferido['CAMPO'] = $campo;
                                $analise_deferido['CAMPO_ID'] = $cadastro_id;
                                $analise_deferido['CAMPO_TABELA'] = $modelo;
                                $analise_deferido['AGREGADO_ANALISE_ID'] = $analise_id;
                                $this->agregado_analise_deferido_model->insert($analise_deferido);
                                $this->{'agregado_' . $modelo . '_model'}->update(array($campo => 2), $cadastro_id);
                            }
                        }
                    }
                }
                // SE DEFERIDO, INSERIR TAREFAS PARA VERIFICAÇÃO DE VENCIMENTOS
                if ($post['AGREGADO_STATUS_ID'] == 1)
                {
                    $notificacoes = array();
                    $motoristas_where = array(
                        'AGREGADO_CADASTRO_ID' => $id,
                    );
                    $motoristas = $this->agregado_motorista_model->get_all($motoristas_where);
                    if (!empty($motoristas))
                    {
                        foreach ($motoristas as $motorista)
                        {
                            $texto = ' do agregado ' . $motorista->NOME . ' - CPF ' . $motorista->CPF;
                            //ANTT DO MOTORISTA
                            $texto1 = $texto . ' (ANTT ' . $motorista->ANTT . ' válido até ';
                            $texto1 .= data_oracle_para_web($motorista->ANTT_VALIDADE) . ')';
                            $notificacoes[] = array(
                                'PRAZO' => $motorista->ANTT_VALIDADE,
                                'TEXTO' => $texto1,
                            );
                            //CNH DO MOTORISTA
                            $texto2 = $texto . ' (CNH ' . $motorista->CNH_NUMERO . ' válido até ';
                            $texto2 .= data_oracle_para_web($motorista->CNH_VENCIMENTO) . ')';
                            $notificacoes[] = array(
                                'PRAZO' => $motorista->CNH_VENCIMENTO,
                                'TEXTO' => $texto2,
                            );
                            $gerenciadores_where = array(
                                'AGREGADO_MOTORISTA_ID' => $motorista->ID,
                            );
                            //VALIDADE DO GERENCIADOR DE RISCO
                            $gerenciadores = $this->agregado_gerenciador_risco_model->get_all($gerenciadores_where);
                            if (!empty($gerenciadores))
                            {
                                foreach ($gerenciadores as $gerenciador)
                                {
                                    $gerenciador_risco = $this->geral_gerenciador_risco_model->get($gerenciador->GERAL_GERENCIADOR_RISCO_ID);
                                    $texto3 = $texto . ' (' . $gerenciador_risco->NOME . ' válido até ';
                                    $texto3 .= data_oracle_para_web($gerenciador->DATA_VALIDADE) . ')';
                                    $notificacoes[] = array(
                                        'PRAZO' => $gerenciador->DATA_VALIDADE,
                                        'TEXTO' => $texto3,
                                    );
                                }
                            }
                        }
                    }
                    $usuarios_documentacao_vencida = $this->retorna_destinatario_gatilho('AGREGADO_DOCUMENTACAO_VENCIDA');
                    foreach ($usuarios_documentacao_vencida as $destinatario)
                    {
                        foreach ($notificacoes as $notificacao)
                        {
                            $tarefa = array();
                            $tarefa['REMETENTE_USUARIO_ID'] = $this->sessao['usuario_id'];
                            $tarefa['DESTINATARIO_USUARIO_ID'] = $destinatario->USUARIO_ID;
                            $tarefa['DESCRICAO'] = 'Verificar documentação vencida ' . $notificacao['TEXTO'];
                            $tarefa['PRAZO'] = $notificacao['PRAZO'];
                            $this->geral_tarefa_model->inserir_de_gatilho($tarefa);
                        }
                    }
                    $usuarios_juridico = $this->retorna_destinatario_gatilho('AGREGADO_JURIDICO');
                    foreach ($usuarios_juridico as $destinatario)
                    {
                        $tarefa = array();
                        $tarefa['REMETENTE_USUARIO_ID'] = $this->sessao['usuario_id'];
                        $tarefa['DESTINATARIO_USUARIO_ID'] = $destinatario->USUARIO_ID;
                        $tarefa['DESCRICAO'] = 'Analisar contrato do novo agregado';
                        $tarefa['LINK'] = 'frotas_agregados/ver_solicitacao/' . $id . '?tarefa=AGREGADO_JURIDICO';
                        $this->geral_tarefa_model->inserir_de_gatilho($tarefa);
                    }
                }
                $tarefa_where = array(
                    'LINK' => 'frotas_agregados/ver_solicitacao/' . $id . '?tarefa=AGREGADO_ANALISE',
                );
                $tarefa_update = array(
                    'ENTREGA_DESCRICAO' => 'ANÁLISE REALIZADA POR ' . $this->sessao['usuario_nome'],
                    'ENTREGA_DATAHORA' => date('YmdHis'),
                );
                $this->geral_tarefa_model->update($tarefa_update, $tarefa_where);
                $this->session->set_flashdata('sucesso', 'Análise gravada com sucesso');
                $this->redirect('frotas_agregados/listar');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
    }

    /**
     * adicionar_solicitacao
     * Tela para iniciar a solicitação de agregado
     */
    function adicionar_solicitacao ()
    {
		$this->form_validation->set_rules('CDEMPRESA', 'Filial', 'trim|required|is_natural_no_zero');
    	$this->form_validation->set_rules('AGREGADO_PROPRIETARIO_ID', 'Proprietário', 'trim|required|is_natural_no_zero');

        if ($this->form_validation->run()) {
            $post = $this->input->post();
            $post['USUARIO_ID'] = $this->sessao['usuario_id'];
            $post['DATAHORA'] = date('YmdHis');
            
            $ID = $this->agregado_cadastro_model->insert($post);
            if ($ID)
            {
                $this->redirect('frotas_agregados/ver_solicitacao/' . $ID);
            }
        }

        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['titulo'] = 'Novo agregado';
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->dados['proprietarios'] = $this->agregado_proprietario_model
                ->as_dropdown('NOME')
                ->order_by('NOME')
                ->get_all();
        $this->dados['gatilho_analise'] = $this->verifica_gatilho('AGREGADO_ANALISE');
        $this->render('formulario_solicitacao');
    }

    /**
     * editar_solicitacao
     * Tela para editar dados básicos da solicitação
     * @param int $id
     */
    function editar_solicitacao ($id)
    {
        $this->form_validation->set_rules('CDEMPRESA', 'Filial', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $update = $this->agregado_cadastro_model->update($post, $id);
            if ($update)
            {
                $this->session->set_flashdata('sucesso', 'Solicitação gravada com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $this->agregado_cadastro_model->as_array()->get($id);
        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['titulo'] = 'Editar agregado';
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->dados['gatilho_analise'] = $this->verifica_gatilho('AGREGADO_ANALISE');
        $this->render('formulario_solicitacao');
    }

    /**
     * enviar_solicitacao
     * Tela para enviar a solicitação para análise do frotas
     * @param int $id
     */
    function enviar_solicitacao ($id)
    {
        $this->load->model('geral_tarefa_model');
        $this->_abrir_fechar_solicitacao($id, 0);
        $update = array('AGREGADO_STATUS_ID' => NULL);
        $this->agregado_cadastro_model->update($update, $id);
        $destinatarios = $this->retorna_destinatario_gatilho('AGREGADO_ANALISE');
        foreach ($destinatarios as $destinatario)
        {
            $tarefa = array();
            $tarefa['REMETENTE_USUARIO_ID'] = $this->sessao['usuario_id'];
            $tarefa['DESTINATARIO_USUARIO_ID'] = $destinatario->USUARIO_ID;
            $tarefa['DESCRICAO'] = 'Analisar solicitação de agregado';
            $tarefa['LINK'] = 'frotas_agregados/ver_solicitacao/' . $id . '?tarefa=AGREGADO_ANALISE';
            $this->geral_tarefa_model->inserir_de_gatilho($tarefa);
        }
        $this->session->set_flashdata('sucesso', 'Solicitação enviada para análise');
        $this->redirect('frotas_agregados/listar_solicitacao');
    }

    /**
     * excluir_solicitacao
     * Tela para excluir uma solicitação sem cadastros vinculados
     * @param int $id
     */
    function excluir_solicitacao ($id)
    {
        $this->_nao_e_dono($id);
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->agregado_cadastro_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Solicitação excluída com sucesso');
                $this->redirect('frotas_agregados/listar_solicitacao');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    /**
     * parecer_juridico
     * Tela para o jurídico dar o parecer sobre o contrato do agregado
     * @param int $cadastro_id
     */
    function parecer_juridico ($cadastro_id)
    {
        $this->load->model('geral_tarefa_model');
        $this->form_validation->set_rules('AGREGADO_STATUS_ID', 'Status', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['TIPO'] = 'JURÍDICO';
            $post['DATAHORA'] = date('YmdHis');
            $post['USUARIO_ID'] = $this->sessao['usuario_id'];
            $post['AGREGADO_CADASTRO_ID'] = $cadastro_id;
            $id = $this->agregado_analise_model->insert($post);
            if ($id)
            {
                $cadastro_update = array(
                    'AGREGADO_STATUS_ID_JURIDICO' => $post['AGREGADO_STATUS_ID']
                );
                $this->agregado_cadastro_model->update($cadastro_update, $cadastro_id);
                $tarefa_where = array(
                    'LINK' => 'frotas_agregados/ver_solicitacao/' . $id . '?tarefa=AGREGADO_JURIDICO',
                );
                $tarefa_update = array(
                    'ENTREGA_DESCRICAO' => 'PARECER REALIZADO POR ' . $this->sessao['usuario_nome'],
                    'ENTREGA_DATAHORA' => date('YmdHis'),
                );
                $this->geral_tarefa_model->update($tarefa_update, $tarefa_where);
                $this->session->set_flashdata('sucesso', 'Parecer gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $gatilho = $this->verifica_gatilho('AGREGADO_JURIDICO');
        if (!$gatilho)
        {
            exit('Você não tem autorização para dar parecer jurídico');
        }
        $this->dados['metodo'] = __FUNCTION__;
        $this->dados['status'] = $this->agregado_status_model->as_dropdown('NOME')->get_all();
        $this->render('frotas_agregados/adicionar_analise');
    }

    /**
     * _abrir_fechar_solicitacao
     * Função privada utilizada para abrir e fechar a edição de documentos
     * pelo solicitante no cadastro do agregado
     * @param int $id
     * @param int $aberto
     */
    function _abrir_fechar_solicitacao ($id, $aberto)
    {
        $get = $this->agregado_cadastro_model->get($id);
        $this->agregado_cadastro_model->update(array('ABERTO' => $aberto), $id);
        // PROPRIETARIO
        $proprietario_id = $get->AGREGADO_PROPRIETARIO_ID;
        $proprietario = $this->agregado_proprietario_model->get($proprietario_id);
        $proprietario_update = array();
        if ($proprietario->ABERTO != 2)
        {
            $proprietario_update['ABERTO'] = $aberto;
        }
        foreach ($this->pre['upload_proprietario'] as $label => $nome)
        {
            if ($proprietario->{'ABERTO_' . $label} != 2)
            {
                $proprietario_update['ABERTO_' . $label] = $aberto;
            }
        }
        $this->agregado_proprietario_model->update($proprietario_update, $proprietario_id);
        // VEICULOS
        $veiculos = $this->agregado_veiculo_model->get_all(array('AGREGADO_CADASTRO_ID' => $id));
        if (!empty($veiculos))
        {
            foreach ($veiculos as $veiculo)
            {
                $veiculo_update = array();
                if ($veiculo->ABERTO != 2)
                {
                    $veiculo_update['ABERTO'] = $aberto;
                }
                foreach ($this->pre['upload_veiculo'] as $label => $nome)
                {
                    if ($veiculo->{'ABERTO_' . $label} != 2)
                    {
                        $veiculo_update['ABERTO_' . $label] = $aberto;
                    }
                }
                $this->agregado_veiculo_model->update($veiculo_update, $veiculo->ID);
            }
        }
        // MOTORISTAS
        $motoristas = $this->agregado_motorista_model->get_all(array('AGREGADO_CADASTRO_ID' => $id));
        if (!empty($motoristas))
        {
            foreach ($motoristas as $motorista)
            {
                $motorista_update = array();
                if ($motorista->ABERTO != 2)
                {
                    $motorista_update['ABERTO'] = $aberto;
                }
                foreach ($this->pre['upload_motorista'] as $label => $nome)
                {
                    if ($motorista->{'ABERTO_' . $label} != 2)
                    {
                        $motorista_update['ABERTO_' . $label] = $aberto;
                    }
                }
                $this->agregado_motorista_model->update($motorista_update, $motorista->ID);
            }
        }
        // GERENCIADORES DE RISCO
        // código de adaptação para problema do where (NÃO MEXER NA LINHA ABAIXO)
        $this->agregado_gerenciador_risco_model->get();
        $gerenciador_riscos_where = array('AGREGADO_CADASTRO_ID' => $id);
        $gerenciador_riscos = $this->agregado_gerenciador_risco_model->get_all($gerenciador_riscos_where);
        if (!empty($gerenciador_riscos))
        {
            foreach ($gerenciador_riscos as $gerenciador_risco)
            {
                $gerenciador_risco_update = array();
                if ($gerenciador_risco->ABERTO != 2)
                {
                    $gerenciador_risco_update['ABERTO'] = $aberto;
                }
                $this->agregado_gerenciador_risco_model->update($gerenciador_risco_update, $gerenciador_risco->ID);
            }
        }
    }

    /**
     * _upload_solicitacao
     * Função privada utilizada para enviar arquivos de upload da solicitação
     * @param int $ID
     * @param string $model
     */
    function _upload_solicitacao ($ID, $model)
    {
        $this->load->model('geral_upload_model');
        $config = array();
        $config['allowed_types'] = 'pdf|png|jpg';
        foreach ($this->pre['upload_' . $model] as $upload_label => $upload_nome)
        {
            if (!empty($_FILES['UPLOAD_' . $upload_label]['name']))
            {
                $this->geral_upload_model->excluir($this->{'agregado_' . $model . '_model'}->table, $ID, $upload_label);
                $this->geral_upload_model->anexar($this->{'agregado_' . $model . '_model'}->table, $ID, $this->sessao['usuario_id'], $upload_label, $config);
            }
        }
    }

    /**
     * _nao_e_dono
     * Função privada utilizada para verificar se o usuário é o solicitante
     * @param int $id
     */
    function _nao_e_dono ($id)
    {
        $get = $this->agregado_cadastro_model->get($id);
        if (empty($get))
        {
            exit('Registro inexistente');
        }
        if ($get->USUARIO_ID != $this->sessao['usuario_id'])
        {
            exit('Você não tem autorização sobre esse registro');
        }
    }

    ////////////////////////////////
    // Proprietário
    ////////////////////////////////

    /**
     * _validation_proprietario
     * Função privada utilizada para validar o cadastro de proprietário
     * @param string $acao
     * @param array $get
     */
    function _validation_proprietario ($acao, $get = null)
    {
        $cpf_cnpj_len = strlen($this->input->post('CPF_CNPJ'));
        if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO'] == 1))
        {
            $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
            $this->form_validation->set_rules('CPF_CNPJ', 'CPF/CNPJ', 'trim|required');
            if ($cpf_cnpj_len > 14)
            {
                $this->form_validation->set_rules('INSCRICAO_ESTADUAL', 'Inscrição Estadual', 'trim|required');
            }
            $this->form_validation->set_rules('CEP', 'CEP', 'trim|required');
            $this->form_validation->set_rules('GERAL_MUNICIPIO_ID', 'Município', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('BAIRRO', 'Bairro', 'trim|required');
            $this->form_validation->set_rules('ENDERECO', 'Endereço', 'trim|required');
            $this->form_validation->set_rules('NUMERO', 'Nº/Complemento', 'trim|required');
            $this->form_validation->set_rules('CELULAR', 'Celular', 'trim|required');
            $this->form_validation->set_rules('ANTT', 'ANTT', 'trim|required');
            $this->form_validation->set_rules('FAVORECIDO', 'Favorecido', 'trim|required');
            $this->form_validation->set_rules('FAVORECIDO_INSCRICAO', 'CPF/CNPJ', 'trim|required');
            $this->form_validation->set_rules('BANCO', 'Banco', 'trim|required');
            $this->form_validation->set_rules('AGENCIA', 'Agência', 'trim|required');
            $this->form_validation->set_rules('CONTA', 'Conta', 'trim|required');
            $this->form_validation->set_rules('TP_CONTA', 'Tipo conta', 'trim|required');
        }
        $upload_regras = 'file_required|file_size_max[5000]|file_allowed_type[png,jpg,pdf]';

        $upload_proprietario = $this->pre['upload_proprietario'];
        if ($cpf_cnpj_len == 14)
        {
            unset($upload_proprietario['CONTRATO_SOCIAL']);
            unset($upload_proprietario['DADOS_EMPRESA']);
        }
        foreach ($upload_proprietario as $upload_label => $upload_nome)
        {
            if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO_' . $upload_label] == 1))
            {
                $this->form_validation->set_rules('UPLOAD_' . $upload_label, $upload_nome, $upload_regras);
            }
        }
    }

    /**
     * adicionar_proprietario
     * Tela para adicionar um novo proprietário
     */
    function adicionar_proprietario ()
    {
        $this->_validation_proprietario('adicionar');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['ABERTO'] = 0;
            $ID = $this->agregado_proprietario_model->insert($post);
            if ($ID)
            {
                $this->_upload_solicitacao($ID, 'proprietario');
                $this->session->set_flashdata('sucesso', 'Proprietário gravado com sucesso');
                $this->redirect('frotas_agregados/adicionar_solicitacao');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['upload_campos'] = $this->pre['upload_proprietario'];
        $this->dados['TP_CONTAS'] = [ "POUPANÇA" => "POUPANÇA", "CORRENTE" => "CORRENTE"];
        $this->dados['titulo'] = 'Novo proprietário';
        $this->render('formulario_proprietario');
    }

    /**
     * editar_proprietario
     * Tela para editar um proprietário cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function editar_proprietario ($cadastro_id, $id)
    {
        $get = $this->agregado_proprietario_model->as_array()->get($id);
        $this->_validation_proprietario('editar', $get);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            if ($get['ABERTO'] == 1)
            {
                $update = $this->agregado_proprietario_model->update($post, $id);
            }
            if (!empty($update) || $get['ABERTO'] != 1)
            {
                $this->_upload_solicitacao($id, 'proprietario');
                $this->session->set_flashdata('sucesso', 'Proprietário gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $get;
        $this->dados['cadastro_id'] = $cadastro_id;
        $this->dados['upload_campos'] = $this->pre['upload_proprietario'];
        
        /* Verificar, este trecho não estava permitindo fazer upload 
         * dos documentos em aberto (chamado extranet 64349)
        if ($get['CPF_CNPJ']){
            unset($this->dados['upload_campos']['CONTRATO_SOCIAL']);
            unset($this->dados['upload_campos']['DADOS_EMPRESA']);
        }
        */
        $this->dados['TP_CONTAS'] = [ "POUPANÇA" => "POUPANÇA", "CORRENTE" => "CORRENTE"];
        $this->dados['titulo'] = 'Editar proprietário';
        $this->render('formulario_proprietario');
    }

    ////////////////////////////////
    // Veículo
    ////////////////////////////////

    /**
     * _validation_veiculo
     * Função privada utilizada para validar o cadastro de veículo
     * @param string $acao
     * @param array $get
     */
    function _validation_veiculo ($acao, $get = null)
    {
        if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO'] == 1))
        {
            $this->form_validation->set_rules('PLACA', 'Placa', 'trim|required');
            $this->form_validation->set_rules('CERTIFICADO', 'Nº do certificado', 'trim|required');
            $this->form_validation->set_rules('GERAL_MUNICIPIO_ID', 'Município', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('RENAVAM', 'Nº do RENAVAM', 'trim|required');
            $this->form_validation->set_rules('CHASSIS', 'Nº do chassis', 'trim|required');
            $this->form_validation->set_rules('POTENCIA', 'Potência', 'trim|required');
            $this->form_validation->set_rules('CATEGORIA', 'Categoria', 'trim|required');
            $this->form_validation->set_rules('TIPO_VEICULO', 'Tipo do veículo', 'trim|required');
            $this->form_validation->set_rules('MODELO', 'Modelo', 'trim|required');
            $this->form_validation->set_rules('TARA', 'Tara', 'trim|required');
            $this->form_validation->set_rules('ANO_MODELO', 'Ano do modelo', 'trim|required');
            $this->form_validation->set_rules('ANO_FABRICACAO', 'Ano de fabricação', 'trim|required');
            $this->form_validation->set_rules('COR_PREDOMINANTE', 'Cor predominante', 'trim|required');
            $this->form_validation->set_rules('COMBUSTIVEL', 'Combustível', 'trim|required');
        }
        $upload_regras = 'file_required|file_size_max[5000]|file_allowed_type[png,jpg,pdf]';
        foreach ($this->pre['upload_veiculo'] as $upload_label => $upload_nome)
        {
            if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO_' . $upload_label] == 1))
            {
                $this->form_validation->set_rules('UPLOAD_' . $upload_label, $upload_nome, $upload_regras);
            }
        }
    }

    /**
     * adicionar_veiculo
     * Tela para adicionar um novo veículo
     * @param int $cadastro_id
     */
    function adicionar_veiculo ($cadastro_id)
    {
        $this->_validation_veiculo('adicionar');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['AGREGADO_CADASTRO_ID'] = $cadastro_id;
            $ID = $this->agregado_veiculo_model->insert($post);
            if ($ID)
            {
                $this->_upload_solicitacao($ID, 'veiculo');
                $this->session->set_flashdata('sucesso', 'Veículo gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['id'] = $cadastro_id;
        $this->dados['titulo'] = 'Novo veículo';
        $this->dados['upload_campos'] = $this->pre['upload_veiculo'];
        $this->render('formulario_veiculo');
    }

    /**
     * editar_veiculo
     * Tela para editar um veículo cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function editar_veiculo ($cadastro_id, $id)
    {
        $get = $this->agregado_veiculo_model->as_array()->get($id);
        $this->_validation_veiculo('editar', $get);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            if ($get['ABERTO'] == 1)
            {
                $update = $this->agregado_veiculo_model->update($post, $id);
            }
            if (!empty($update) || $get['ABERTO'] != 1)
            {
                $this->_upload_solicitacao($id, 'veiculo');
                $this->session->set_flashdata('sucesso', 'Veículo gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $get;
        $this->dados['id'] = $cadastro_id;
        $this->dados['upload_campos'] = $this->pre['upload_veiculo'];
        $this->dados['titulo'] = 'Editar veículo';
        $this->render('formulario_veiculo');
    }

    /**
     * excluir_veiculo
     * Tela para excluir um veículo cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function excluir_veiculo ($cadastro_id, $id)
    {
        $this->_nao_e_dono($cadastro_id);
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->agregado_veiculo_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Veículo excluído com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    ////////////////////////////////
    // Motorista
    ////////////////////////////////

    /**
     * _validation_motorista
     * Função privada utilizada para validar o cadastro de motorista
     * @param string $acao
     * @param array $get
     */
    function _validation_motorista ($acao, $get = null)
    {
        if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO'] == 1))
        {
            $this->form_validation->set_rules('CPF', 'CPF', 'trim|required|valid_cpf');
            $this->form_validation->set_rules('INSCRICAO_ESTADUAL', 'Inscrição Estadual', 'trim');
            $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
            $this->form_validation->set_rules('GERAL_MUNICIPIO_ID', 'Município', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('BAIRRO', 'Bairro', 'trim|required');
            $this->form_validation->set_rules('ENDERECO', 'Endereço', 'trim|required');
            $this->form_validation->set_rules('NUMERO', 'Nº/Complemento', 'trim|required');
            $this->form_validation->set_rules('TELEFONE', 'Telefone', 'trim|required');
            $this->form_validation->set_rules('CELULAR', 'Celular', 'trim|required');
            $this->form_validation->set_rules('EMAIL', 'E-mail', 'trim|valid_email');
            $this->form_validation->set_rules('DATA_NASCIMENTO', 'Data de nascimento', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('PIS', 'PIS', 'trim|required');
            $this->form_validation->set_rules('ANTT', 'ANTT', 'trim');
            $this->form_validation->set_rules('CNH_CATEGORIA', 'CNH - Categoria', 'trim|required');
            $this->form_validation->set_rules('CNH_NUMERO', 'CNH - Número', 'trim|required');
            $this->form_validation->set_rules('CNH_PRONTUARIO', 'CNH - Prontuário', 'trim|required');
            $this->form_validation->set_rules('CNH_EMISSAO', 'CNH - Emissão', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('CNH_1HABILITACAO', 'CNH - Data da 1ª hab.', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('CNH_VENCIMENTO', 'CNH - Vencimento', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('CNH_GERAL_MUNICIPIO_ID', 'CNH - Município', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('CNH_ORGAO', 'CNH - Orgão', 'trim|required');
        }
        $upload_regras = 'file_required|file_size_max[5000]|file_allowed_type[png,jpg,pdf]';
        foreach ($this->pre['upload_motorista'] as $upload_label => $upload_nome)
        {
            if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO_' . $upload_label] == 1))
            {
                $this->form_validation->set_rules('UPLOAD_' . $upload_label, $upload_nome, $upload_regras);
            }
        }
    }

    /**
     * _formata_data_motorista
     * Função privada utilizada para formatar as datas do formulário do motorista
     * @param array $post
     */
    function _formata_data_motorista ($post)
    {
        $post['DATA_NASCIMENTO'] = data_web_para_oracle($post['DATA_NASCIMENTO']);
        $post['CNH_EMISSAO'] = data_web_para_oracle($post['CNH_EMISSAO']);
        $post['CNH_VENCIMENTO'] = data_web_para_oracle($post['CNH_VENCIMENTO']);
        $post['CNH_1HABILITACAO'] = data_web_para_oracle($post['CNH_1HABILITACAO']);
        $post['ANTT_VALIDADE'] = data_web_para_oracle($post['ANTT_VALIDADE']);
        return $post;
    }

    /**
     * adicionar_motorista
     * Tela para adicionar um novo motorista
     * @param int $cadastro_id
     */
    function adicionar_motorista ($cadastro_id)
    {
        $this->load->model('geral_upload_model');
        $cadastro = $this->agregado_cadastro_model->get($cadastro_id);
        $proprietario = $this->agregado_proprietario_model->get($cadastro->AGREGADO_PROPRIETARIO_ID);
        $this->_validation_motorista('adicionar');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post = $this->_formata_data_motorista($post);
            $post['AGREGADO_CADASTRO_ID'] = $cadastro_id;
            $ID = $this->agregado_motorista_model->insert($post);
            if ($ID)
            {
                $this->_upload_solicitacao($ID, 'motorista');
                $this->session->set_flashdata('sucesso', 'Motorista gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['id'] = $cadastro_id;
        $this->dados['proprietario'] = $proprietario;
        $this->dados['titulo'] = 'Novo motorista';
        $this->dados['upload_campos'] = $this->pre['upload_motorista'];
        $this->render('formulario_motorista');
    }

    /**
     * editar_motorista
     * Tela para editar um motorista cadastrado
     * @param int $cadastro_id
     * @param int $id	
     */
    function editar_motorista($cadastro_id, $id){
        $get = $this->agregado_motorista_model->as_array()->get($id);
        $cadastro = $this->agregado_cadastro_model->get($cadastro_id);
        $proprietario = $this->agregado_proprietario_model->get($cadastro->AGREGADO_PROPRIETARIO_ID);
        $this->_validation_motorista('editar', $get);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            if ($get['ABERTO'] == 1)
            {
                $post = $this->_formata_data_motorista($post);
                $update = $this->agregado_motorista_model->update($post, $id);
            }
            if (!empty($update) || $get['ABERTO'] != 1)
            {
                $this->_upload_solicitacao($id, 'motorista');
                $this->session->set_flashdata('sucesso', 'Motorista gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $get;
        $this->dados['id'] = $cadastro_id;
        $this->dados['proprietario'] = $proprietario;
        $this->dados['titulo'] = 'Editar motorista';
        $this->dados['upload_campos'] = $this->pre['upload_motorista'];
        $this->render('formulario_motorista');
    }

    /**
     * excluir_motorista
     * Tela para excluir um motorista cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function excluir_motorista ($cadastro_id, $id)
    {
        $this->_nao_e_dono($cadastro_id);
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->agregado_motorista_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Motorista excluído com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    ////////////////////////////////
    // Gerenciador de risco
    ////////////////////////////////

    /**
     * _validation_gerenciador_risco
     * Função privada utilizada para validar o cadastro de gerenciadores de risco
     * @param string $acao
     * @param array $get
     */
    function _validation_gerenciador_risco ($acao, $get = null)
    {
        if ($acao == 'adicionar' || ($acao == 'editar' && $get['ABERTO'] == 1))
        {
            $this->form_validation->set_rules('AGREGADO_MOTORISTA_ID', 'Motorista', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('GERAL_GERENCIADOR_RISCO_ID', 'Gerenciador de risco', 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('DATA_CONSULTA', 'Data da consulta', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('DATA_VALIDADE', 'Data de validade', 'trim|required|valid_date[d/m/Y]');
            $this->form_validation->set_rules('PROTOCOLO', 'Protocolo', 'trim|required');
            $this->form_validation->set_rules('OPERADOR', 'Operador', 'trim|required');
        }
    }

    /**
     * adicionar_gerenciador_risco
     * Tela para adicionar um novo gerenciador de risco
     * @param int $cadastro_id
     */
    function adicionar_gerenciador_risco ($cadastro_id)
    {
        $this->load->model('geral_gerenciador_risco_model');
        $this->_validation_gerenciador_risco('adicionar');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['AGREGADO_CADASTRO_ID'] = $cadastro_id;
            $post['DATA_CONSULTA'] = data_web_para_oracle($post['DATA_CONSULTA']);
            $post['DATA_VALIDADE'] = data_web_para_oracle($post['DATA_VALIDADE']);
            $ID = $this->agregado_gerenciador_risco_model->insert($post);
            if ($ID)
            {
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $motorista_where = array('AGREGADO_CADASTRO_ID' => $cadastro_id);
        $this->dados['motoristas'] = $this->agregado_motorista_model->as_dropdown('NOME')->get_all($motorista_where);
        $this->dados['gerenciadores_risco'] = $this->geral_gerenciador_risco_model->as_dropdown('NOME')->get_all();
        $this->dados['id'] = $cadastro_id;
        $this->dados['titulo'] = 'Novo gerenciador de risco';
        $this->render('formulario_gerenciador_risco');
    }

    /**
     * editar_gerenciador_risco
     * Tela para editar um gerenciador de risco cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function editar_gerenciador_risco ($cadastro_id, $id)
    {
        $this->load->model('geral_gerenciador_risco_model');
        $get = $this->agregado_gerenciador_risco_model->as_array()->get($id);
        $this->_validation_gerenciador_risco('editar', $get);
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            if ($get['ABERTO'] == 1)
            {
                $post['DATA_CONSULTA'] = data_web_para_oracle($post['DATA_CONSULTA']);
                $post['DATA_VALIDADE'] = data_web_para_oracle($post['DATA_VALIDADE']);
                $update = $this->agregado_gerenciador_risco_model->update($post, $id);
            }
            if (!empty($update) || $get['ABERTO'] != 1)
            {
                $this->_upload_solicitacao($id, 'motorista');
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco gravado com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $_POST = $get;
        $motorista_where = array('AGREGADO_CADASTRO_ID' => $cadastro_id);
        $this->dados['motoristas'] = $this->agregado_motorista_model->as_dropdown('NOME')->get_all($motorista_where);
        $this->dados['gerenciadores_risco'] = $this->geral_gerenciador_risco_model->as_dropdown('NOME')->get_all();
        $this->dados['id'] = $cadastro_id;
        $this->dados['titulo'] = 'Editar gerenciador de risco';
        $this->render('formulario_gerenciador_risco');
    }

    /**
     * excluir_gerenciador_risco
     * Tela para excluir um gerenciador de risco cadastrado
     * @param int $cadastro_id
     * @param int $id
     */
    function excluir_gerenciador_risco ($cadastro_id, $id)
    {
        $this->_nao_e_dono($cadastro_id);
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->agregado_gerenciador_risco_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Gerenciador de risco excluído com sucesso');
                $this->redirect('frotas_agregados/ver_solicitacao/' . $cadastro_id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    ////////////////////////////////
    // Modelos
    ////////////////////////////////

    /**
     * listar_modelo
     * Tela para listar os modelos cadastrados
     */
    function listar_modelo ()
    {
        $this->dados['lista'] = $this->agregado_modelo_model->order_by('CHAVE')->get_all();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->render('listar_modelo');
    }

    /**
     * adicionar_modelo
     * Tela para adicionar um novo modelo
     */
    function adicionar_modelo ()
    {
        $this->_formulario_modelo();
    }

    /**
     * editar_modelo
     * Tela para editar um modelo cadastrado
     */
    function editar_modelo ($id)
    {
        $this->_formulario_modelo($id);
    }

    /**
     * _formulario_modelo
     * Função privada utilizada para adicionar e editar um modelo
     */
    function _formulario_modelo ($id = null)
    {
        $this->load->model('geral_upload_model');
        $this->form_validation->set_rules('CHAVE', 'Chave', 'trim|required');
        $this->form_validation->set_rules('DOCUMENTO', 'Documento', 'trim|required');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $documento = converte_caracteres_word($post['DOCUMENTO']);
            unset($post['DOCUMENTO']);
            unset($post['files']);
            if ($id)
            {
                $query = $this->agregado_modelo_model->update($post, $id);
            }
            else
            {
                $id = $this->agregado_modelo_model->insert($post);
                $query = $id;
            }
            if ($query)
            {
                $this->geral_upload_model->criar($this->agregado_modelo_model->table, $id, $this->sessao['usuario_id'], $post['CHAVE'], 'html', $documento);
                $this->session->set_flashdata('sucesso', 'Modelo gravado com sucesso');
                $this->redirect('frotas_agregados/listar_modelo');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        if ($id)
        {
            $_POST = $this->agregado_modelo_model->as_array()->get($id);
            $_POST['DOCUMENTO'] = $this->geral_upload_model->ler($this->agregado_modelo_model->table, $id, $_POST['CHAVE']);
        }
        $this->render('formulario_modelo');
    }

}
