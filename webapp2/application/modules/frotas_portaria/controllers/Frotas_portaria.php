<?php

/**
 * Description of Frotas_agregados
 *
 * @author Administrador
 */
class Frotas_portaria extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('portaria_checklist_model');
        $this->load->model('portaria_checklist_documento_model');
        $this->load->model('portaria_checklist_foto_model');
        $this->load->model('portaria_foto_categoria_model');
        $this->load->model('portaria_motorista_model');

        $this->dados['modulo_nome'] = 'Frotas > Portaria';
        $this->dados['modulo_menu'] = array(
            'Motoristas' => 'listar_motorista',
            'Checklists' => 'listar_checklist'
        );
        $this->publico = array(
            'obter_documentos'
        );
        parent::__construct();
    }

    function index ()
    {
        $this->redirect('frotas_portaria/listar_motorista');
    }

    ////////////////////////////////
    // Motoristas
    ////////////////////////////////

    /**
     * listar_motorista
     * Listagem dos motoristas que estão aguardando validação na portaria
     * @param int $page
     */
    function listar_motorista ($page = 1)
    {
       $this->_atualizar_lista_motorista();
        $total = $this->portaria_motorista_model
                ->where('CDEMPRESA', $this->sessao['filial'])
                ->where('ATENDIDO', 0)
                ->where('ATIVO', 1)
                ->count_rows();
        $lista = $this->portaria_motorista_model
                ->where('CDEMPRESA', $this->sessao['filial'])
                ->where('ATENDIDO', 0)
                ->where('ATIVO', 1)
                ->order_by('DATAHORA', 'DESC')
                ->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->portaria_motorista_model->all_pages;
        $this->render('listar_motorista');
    }

    /**
     * _atualizar_lista_motorista
     * Função para obter dados do relógio ponto e atualizar a tabela de motoristas
     */
    function _atualizar_lista_motorista ()
    {
        $this->load->library('softran_oracle');
        $this->load->library('controlid_webservice');
        $this->load->model('ti_biometria/biometria_equipamento_model');
        if (!$this->sessao['filial'])
        {
            return NULL;
        }
        $equipamento_get = $this->biometria_equipamento_model
                ->where('CDEMPRESA', $this->sessao['filial'])
                ->where('TIPO', 'IDCLASS')
                ->get();
        if (!$equipamento_get)
        {
            return NULL;
        }
        $this->controlid_webservice->config($equipamento_get->IP, $equipamento_get->PROTOCOLO);
        $sessao = $this->controlid_webservice->login($equipamento_get->USUARIO, $equipamento_get->SENHA);
        if (!$sessao)
        {
            return NULL;
        }
        $data = array(
            'day' => (int) date('d'),
            'month' => (int) date('m'),
            'year' => (int) date('Y'),
        );
        $afd = $this->controlid_webservice->get_afd($data);
        if (empty($afd))
        {
            return NULL;
        }
        $linhas = explode("\n", $afd);
        if (empty($linhas))
        {
            return NULL;
        }
        foreach ($linhas as $linha)
        {
            if (strlen($linha) > 25)
            {
                $cpf = substr($linha, (strlen($linha) > 39) ? 24 : 23, 11);
                $cpfcnpj = str_pad($cpf, 14, '0', STR_PAD_LEFT);
                $pessoas_where = "AND CPFCNPJ = '$cpfcnpj'";
                $pessoa = $this->softran_oracle->pessoas(0, 1, $pessoas_where);
                if (!empty($pessoa[0]))
                {
                    $motorista_arr = array();
                    $motorista_arr['DATAHORA'] = substr($linha, 14, 4);
                    $motorista_arr['DATAHORA'] .= substr($linha, 12, 2);
                    $motorista_arr['DATAHORA'] .= substr($linha, 10, 2);
                    $motorista_arr['DATAHORA'] .= substr($linha, 18, 2);
                    $motorista_arr['DATAHORA'] .= substr($linha, 20, 2) . '00';
                    $motorista_arr['HASH'] = substr($linha, 0, 9);
                    $motorista_arr['NOME'] = $pessoa[0]->NOME;
                    $motorista_arr['CPF'] = formata_cpf($cpf);
                    $motorista_arr['CDEMPRESA'] = $this->sessao['filial'];
                    $this->portaria_motorista_model->insert($motorista_arr);
                }
            }
        }
    }

    function obter_documentos ()
    {
        $this->load->library('softran_oracle');
        $id = $this->input->post('ID');
        $get = $this->portaria_motorista_model->get($id);
        if (!$get || !$id)
        {
            $retorno['tipo'] = 'Erro';
            $retorno['mensagem'] = 'Motorista não foi registrado no relógio-ponto';
        }
        $cpf = $get->CPF;
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $cpf = str_pad($cpf, 14, '0', STR_PAD_LEFT);
        $pessoas_where = "AND CPFCNPJ = '$cpf'";
        $pessoas = $this->softran_oracle->pessoas(0, 10, $pessoas_where);
        if (!empty($pessoas))
        {
            foreach ($pessoas as $pessoa)
            {
                if ($pessoa->ATIVO)
                {
                    $tipo = ($pessoa->TIPO == 'FUNCIONARIO') ? 1 : 0;
                }
            }
        }
        if (isset($tipo))
        {
            $documentos = $this->softran_oracle->documentos_motorista($tipo, $cpf);
        }
        if (!empty($documentos))
        {
            //documentos para teste de sistema
            if ($documentos->NRCPF == '00007927889907')
            {
                $documentos->MANIFESTOS = 'JV/RI-000123/16, JV/RI-000542/16';
                $documentos->ROMANEIOS = '7-1521-55481';
            }
            if (!$documentos->MANIFESTOS && !$documentos->ROMANEIOS && !$documentos->COLETAS)
            {
                $retorno['tipo'] = 'Erro';
                $retorno['mensagem'] = 'Esse motorista não tem documentos de viagem';
            }
        }
        else
        {
            $retorno['tipo'] = 'Erro';
            $retorno['mensagem'] = 'Motorista não encontrado no banco de dados';
        }
        if (empty($retorno))
        {
            $documentos->MANIFESTOS = array_map('trim', explode(',', $documentos->MANIFESTOS));
            $documentos->ROMANEIOS = array_map('trim', explode(',', $documentos->ROMANEIOS));
            $documentos->COLETAS = array_map('trim', explode(',', $documentos->COLETAS));
            $retorno = $documentos;
        }
        echo json_encode($retorno);
    }

    function ocultar_motorista ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $update = $this->portaria_motorista_model->update(array('ATIVO' => 0), $id);
            if ($update)
            {
                $this->redirect('frotas_portaria/listar_motorista', 'sucesso', 'Motorista ocultado com sucesso');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/ocultar');
    }

    ////////////////////////////////
    // Checklist
    ////////////////////////////////

    function listar_checklist ($page = 1)
    {
        $total = $this->portaria_checklist_model
                ->where('USUARIO_ID', $this->sessao['usuario_id'])
                ->count_rows();
        $lista = $this->portaria_checklist_model
                ->where('USUARIO_ID', $this->sessao['usuario_id'])
                ->order_by('DATAHORA', 'DESC')
                ->paginate(10, $total, $page);
        $i = 0;
        if (!empty($lista))
        {
            foreach ($lista as $item)
            {
                $this->dados['documentos'][$item->ID] = $this->portaria_checklist_documento_model
                        ->where('PORTARIA_CHECKLIST_ID', $item->ID)
                        ->get_all();
                $fotos = $this->portaria_checklist_foto_model
                        ->where('PORTARIA_CHECKLIST_ID', $item->ID)
                        ->get_all();
                $this->dados['fotos'][$item->ID] = 0;
                if (!empty($fotos))
                {
                    foreach ($fotos as $foto)
                    {
                        $this->dados['fotos'][$item->ID] += $foto->QUANTIDADE;
                    }
                }
                $motorista = $this->portaria_motorista_model->get($item->PORTARIA_MOTORISTA_ID);
                $lista[$i]->MOTORISTA_NOME = $motorista->NOME;
                $lista[$i]->MOTORISTA_CDEMPRESA = $motorista->CDEMPRESA;
                $i++;
            }
        }
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->portaria_checklist_model->all_pages;
        $this->render('listar_checklist');
    }

    function ver_checklist ($id)
    {
        $this->load->model('geral_upload_model');
        $this->load->model('ti_permissoes/usuario_model');
        $cadastro = $this->portaria_checklist_model->get($id);
        $motorista = $this->portaria_motorista_model->get($cadastro->PORTARIA_MOTORISTA_ID);
        $fotos = $this->portaria_checklist_foto_model->get_all(array('PORTARIA_CHECKLIST_ID' => $id));
        $fotos_tipos = array();
        $fotos_uploads = array();
        $fotos_uploads_where = array('TABELA' => $this->portaria_checklist_foto_model->table);
        if (!empty($fotos))
        {
            foreach ($fotos as $foto)
            {
                $fotos_tipos[] = $foto->TIPO;
                $fotos_uploads_where['TABELA_ID'] = $foto->ID;
                $fotos_uploads[$foto->TIPO] = $this->geral_upload_model
                        ->order_by('LABEL, DATAHORA')
                        ->get_all($fotos_uploads_where);
            }
        }
        $documentos = $this->portaria_checklist_documento_model->get_all(array('PORTARIA_CHECKLIST_ID' => $id));
        $documentos_tipos = array();
        if (!empty($documentos))
        {
            foreach ($documentos as $documento)
            {
                $documentos_tipos[] = $documento->TIPO;
            }
        }
        $this->dados['fotos'] = $fotos_uploads;
        $this->dados['fotos_tipos'] = array_unique($fotos_tipos);
        $this->dados['documentos'] = $documentos;
        $this->dados['documentos_tipos'] = array_unique($documentos_tipos);
        $this->dados['cadastro'] = $cadastro;
        $this->dados['motorista_nome'] = $motorista->NOME;
        $this->dados['motorista_cdempresa'] = $motorista->CDEMPRESA;
        $this->dados['usuario_nome'] = $this->usuario_model->get($cadastro->USUARIO_ID)->NOME;
        $this->render('ver_checklist');
    }

    function adicionar_checklist ()
    {
        $post = $this->input->post();
        $checklist_arr = array();
        $checklist_arr['PORTARIA_MOTORISTA_ID'] = $post['MOTORISTA'];
        $checklist_arr['USUARIO_ID'] = $this->sessao['usuario_id'];
        $checklist_arr['DATAHORA'] = date('YmdHis');
        $checklist_arr['OBSERVACOES'] = !empty($post['OBSERVACOES']) ? $post['OBSERVACOES'] : NULL;
        $checklist_id = $this->portaria_checklist_model->insert($checklist_arr);
        if ($checklist_id)
        {
            $documento_arr = array();
            $documento_arr['PORTARIA_CHECKLIST_ID'] = $checklist_id;
            $documentos = json_decode($post['DOCUMENTOS']);
            if (!empty($documentos))
            {
                foreach ($documentos as $tipo_documento => $documento)
                {
                    $documento_arr['TIPO'] = $tipo_documento;
                    foreach ($documento as $numero => $deferido)
                    {
                        $documento_arr['NUMERO'] = $numero;
                        $documento_arr['DEFERIDO'] = $deferido;
                        $this->portaria_checklist_documento_model->insert($documento_arr);
                    }
                }
            }
            $foto_arr = array();
            $foto_arr['PORTARIA_CHECKLIST_ID'] = $checklist_id;
            $tipos_fotos = json_decode($post['FOTOS']);
            if (!empty($tipos_fotos))
            {
                foreach ($tipos_fotos as $tipo_foto => $fotos)
                {
                    if (!empty($fotos))
                    {
                        $foto_arr['TIPO'] = $tipo_foto;
                        $foto_arr['QUANTIDADE'] = count($fotos);
                        $foto_id = $this->portaria_checklist_foto_model->insert($foto_arr);
                        foreach ($fotos as $foto)
                        {
                            $this->load->model('geral_upload_model');
                            $config = array();
                            $config['allowed_types'] = 'jpg';
                            $label = 'FOTO-' . $foto->id . '-' . $foto->tipo;
                            $this->geral_upload_model->criar($this->portaria_checklist_foto_model->table, $foto_id, $this->sessao['usuario_id'], $label, 'jpg', base64_decode($foto->src));
                        }
                    }
                }
            }
            $this->portaria_motorista_model->update(array('ATENDIDO' => 1), $post['MOTORISTA']);
            $this->redirect('frotas_portaria/listar_checklist', 'sucesso', 'Checklist gravado com sucesso');
        }
        else
        {
            $this->redirect('frotas_portaria/adicionar_checklist', 'erro', 'Erro ao gravar');
        }
    }

}
