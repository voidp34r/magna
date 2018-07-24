<?php

/**
 * Description of Sac_enviosms
 *
 * @author Administrador
 */
class Sac_enviosms extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('sms_agendamento_model');

        $this->dados['modulo_nome'] = 'SAC > Envio de SMS';
        $this->dados['modulo_menu'] = array(
            'Pendentes' => 'listagem',
            'Disparados' => 'listagem_disparados',
            'Respostas' => 'listagem_respostas',
        );
        $this->publico = array(
            'leitura_respostas',
        );
        parent::__construct();
    }

    function index ()
    {
        $this->redirect('sac_enviosms/listagem');
    }


    ////////////////////////////////
    // SMS
    ////////////////////////////////

    /**
     * listagem
     * Listagem de SMS pendentes de envio
     * @param int $page
     */
    function listagem ($page = 1)
    {
        $where = array('STATUS IS NULL' => NULL);
        $this->_listagem($page, $where, 'pendentes');
        $this->render('listagem');
    }

    /**
     * listagem_disparados
     * Listagem de SMS disparados pelo iAgente
     * @param int $page
     */
    function listagem_disparados ($page = 1)
    {
        $where = array('STATUS IS NOT NULL' => NULL);
        $this->_listagem($page, $where, 'disparados');
        $this->render('listagem');
    }

    /**
     * listagem_respostas
     * Listagem das respostas obtidas pelo callback do iAgente
     * @param int $page
     */
    function listagem_respostas ($page = 1)
    {
        $where = array('RESPOSTA IS NOT NULL' => NULL);
        $this->_listagem($page, $where, '');
        $this->render('listagem_respostas');
    }

    /**
     * _listagem
     * Função privada utilizada para todas as listagens de SMS
     * @param int $page
     * @param array $where
     * @param string $titulo
     */
    function _listagem ($page, $where, $titulo)
    {
        $gatilho_visualizacao = $this->verifica_gatilho('ENVIOSMS_VER_TUDO');

        $this->_filtro_where();
        $this->_filtro_like();
        $where = $this->_softran_entrega_empresas($where, $gatilho_visualizacao);
        $total = $this->sms_agendamento_model->where($where)->count_rows();

        $this->_filtro_where();
        $this->_filtro_like();
        $where = $this->_softran_entrega_empresas($where, $gatilho_visualizacao);
        $lista = $this->sms_agendamento_model->where($where)->paginate(10, $total, $page);

        $this->dados['total'] = $total;
        $this->dados['lista'] = $lista;
        $this->dados['titulo'] = $titulo;
        $this->dados['paginacao'] = $this->sms_agendamento_model->all_pages;
    }

    /**
     * _softran_entrega_empresas
     * Função privada para verificar se o usuário tem permissão para visualizar 
     * apenas as empresas vinculadas a ele no softran ou todas conforme gatilho
     * @param int $page
     * @param array $where
     * @param string $titulo
     */
    function _softran_entrega_empresas ($where, $gatilho_visualizacao = null)
    {
        if (!$gatilho_visualizacao)
        {
            $result = $this->softran_oracle->empresas_vinculadas_romaneio_entrega($this->sessao['usuario']);
            $empresas = array();
            foreach ($result as $item)
            {
                $empresas[] = $item->CDEMPRESA;
            }
            $where['SOFTRAN_DESTINO'] = ($empresas) ? $empresas : '0';
        }
        return $where;
    }

    /**
     * editar
     * Tela para editar uma SMS cadastrada
     * @param int $id
     */
    function editar ($id)
    {
        $this->form_validation->set_rules('CELULAR', 'Número', 'required|trim');
        $this->form_validation->set_rules('MENSAGEM', 'Mensagem', 'required|trim');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $update = $this->sms_agendamento_model->update($post, $id);
            if ($update)
            {
                $this->dados['sucesso'] = 'SMS gravada com sucesso';
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['item'] = $this->sms_agendamento_model->where('ID', $id)->get();
        $this->render('editar');
    }

    /**
     * excluir
     * Tela para excluir uma SMS cadastrada
     * @param int $id
     */
    function excluir ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->sms_agendamento_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'SMS excluído com sucesso');
                $this->redirect('sac_enviosms/listagem');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

    /**
     * responder
     * Tela para responder uma SMS e enviar pelo iAgente
     * @param int $id
     */
    function responder ($id)
    {
        $this->load->library('sms_webservice');
        $item = $this->sms_agendamento_model->where('ID', $id)->get();
        $this->form_validation->set_rules('MENSAGEM', 'Mensagem', 'required|trim');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['DATAHORA'] = date('YmdHis');
            $post['CELULAR'] = $item->CELULAR;
            $post['SOFTRAN_CDEMPRESA'] = $item->SOFTRAN_CDEMPRESA;
            $post['SOFTRAN_NRDOCTOFISCAL'] = $item->SOFTRAN_NRDOCTOFISCAL;
            $post['SOFTRAN_DESTINATARIO'] = $item->SOFTRAN_DESTINATARIO;
            $post['SOFTRAN_REMETENTE'] = $item->SOFTRAN_REMETENTE;
            $post['SOFTRAN_DESTINO'] = $item->SOFTRAN_DESTINO;
            $post['MENSAGEM'] = remove_acento($post['MENSAGEM']);
            $insert = $this->sms_agendamento_model->insert($post);
            if ($insert)
            {
                $envio = $this->sms_webservice->enviar_sms($post['CELULAR'], $post['MENSAGEM'], '', $insert);
                if (!empty($envio))
                {
                    $this->sms_agendamento_model->update(array('STATUS' => ($envio[0] == 1) ? 'OK' : 'FALHA'), $insert);
                    $this->sms_agendamento_model->update(array('RESPONDIDO' => '1'), $id);
                }
                $this->session->set_flashdata('sucesso', 'SMS enviada com sucesso');
                $this->redirect('sac_enviosms/listagem_respostas');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->dados['item'] = $item;
        $this->render('responder');
    }

    /**
     * disparar
     * Tela para disparar as SMS pendentes pelo iAgente
     */
    function disparar ()
    {
        $this->load->library('sms_webservice');
        $gatilho_visualizacao = $this->verifica_gatilho('ENVIOSMS_VER_TUDO');
        $where = $this->_softran_entrega_empresas(array(), $gatilho_visualizacao);
        $where['STATUS IS NULL'] = NULL;
        $lista = $this->sms_agendamento_model->where($where)->get_all();
        if (!empty($lista))
        {
            $i = 0;
            $enviados = array();
            foreach ($lista as $item)
            {
                $celular = (int) $item->CELULAR;
                $envio = $this->sms_webservice->enviar_sms($celular, $item->MENSAGEM, '', $item->ID);
                if (!empty($envio))
                {
                    $enviados[$i] = $item;
                    $enviados[$i]->STATUS = ($envio[0] == 1) ? 'OK' : 'FALHA';
                    $dados['STATUS'] = $lista[$i]->STATUS;
                    $this->sms_agendamento_model->update($dados, $item->ID);
                }
                $i++;
            }
        }
        $this->dados['lista'] = $enviados;
        $this->render('disparar');
    }

    /**
     * leitura_respostas
     * Função utilizada pelo iAgente para retornar as respostas dos SMS e 
     * gravar no banco de dados conforme o codigosms
     */
    function leitura_respostas ()
    {
        $get = $this->input->get();
        if (!empty($get['codigosms']))
        {
            $dados = array();
            $dados['STATUS'] = $get['status'];
            if (!empty($get['mensagem']))
            {
                $dados['RESPOSTA'] = $get['mensagem'];
                $dados['RESPOSTA_DATAHORA'] = date('YmdHis');
            }
            $this->sms_agendamento_model->update($dados, $get['codigosms']);
        }
        exit();
    }

}
