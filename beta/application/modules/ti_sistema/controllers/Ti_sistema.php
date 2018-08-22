<?php

/**
 * Description of Sac_enviosms
 *
 * @author Administrador
 */
class Ti_sistema extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('sistema_modulo_model');
        $this->load->model('sistema_tela_model');
        $this->load->model('sistema_perfil_model');
        $this->load->model('sistema_perfil_tela_model');
        $this->load->model('sistema_gatilho_model');

        $this->dados['modulo_nome'] = 'TI > Sistema';
        $this->dados['modulo_menu'] = array(
            'Perfis' => 'listar_perfil',
            'Gatilhos' => 'listar_gatilho',
            'Módulos' => 'listar_modulo',
            'Telas' => 'listar_tela',
        );

        parent::__construct();
    }

    function index ()
    {
        $this->redirect('ti_sistema/listar_perfil');
    }

    /*
     * Perfil
     */

    function listar_perfil ()
    {
        $this->dados['lista'] = $this->sistema_perfil_model->order_by('NOME')->get_all();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;

        $this->render('listar_perfil');
    }

    function adicionar_perfil ()
    {
        $this->_formulario_perfil();

        $this->dados['telas_selecionadas'] = array();
        $this->_modulo_tela_campos();
        $this->render('formulario_perfil');
    }

    function editar_perfil ($id)
    {
        $this->_formulario_perfil($id);

        $this->dados['item'] = $this->sistema_perfil_model->get(array('ID' => $id));
        $telas = $this->sistema_perfil_tela_model->get_all(array('SISTEMA_PERFIL_ID' => $id));

        $this->dados['telas_selecionadas'] = array();
        if (!empty($telas))
        {
            foreach ($telas as $tela)
            {
                $this->dados['telas_selecionadas'][] = $tela->SISTEMA_TELA_ID;
            }
        }

        $this->_modulo_tela_campos();
        $this->render('formulario_perfil');
    }

    function excluir_perfil ($id)
    {
        $this->load->model('ti_permissoes/usuario_perfil_model');
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $this->sistema_perfil_tela_model->delete(array('SISTEMA_PERFIL_ID' => $id));
            $this->usuario_perfil_model->delete(array('SISTEMA_PERFIL_ID' => $id));
            $delete = $this->sistema_perfil_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Perfil excluído com sucesso');
                $this->redirect('ti_sistema/listar_perfil');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('excluir_perfil');
    }

    function _formulario_perfil ($id = NULL)
    {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');

        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['ATIVO'] = !$id || isset($post['ATIVO']) ? TRUE : FALSE;
            $telas = $post['TELAS'];
            unset($post['TELAS']);
            if ($id)
            {
                $query = $this->sistema_perfil_model->update($post, $id);
            }
            else
            {
                $query = $this->sistema_perfil_model->insert($post);
                $id = $query;
            }
            if ($query)
            {
                $this->sistema_perfil_tela_model->delete(array('SISTEMA_PERFIL_ID' => $id));
                if (!empty($telas))
                {
                    $perfil_tela = array();
                    $perfil_tela['SISTEMA_PERFIL_ID'] = $id;
                    foreach ($telas as $tela)
                    {
                        $perfil_tela['SISTEMA_TELA_ID'] = $tela;
                        $this->sistema_perfil_tela_model->insert($perfil_tela);
                    }
                }
                $this->dados['sucesso'] = 'Perfil gravado com sucesso';
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
    }

    function _modulo_tela_campos ()
    {
        $telas = $this->sistema_tela_model
                ->order_by('NOME')
                ->get_all(array('ATIVO' => 1));

        foreach ($telas as $tela)
        {
            $this->dados['telas'][$tela->SISTEMA_MODULO_ID][] = $tela;
        }

        $this->dados['modulos'] = $this->sistema_modulo_model
                ->order_by('NOME')
                ->get_all(array('ATIVO' => 1));
    }

    /*
     * Gatilhos
     */

    function listar_gatilho ()
    {
        $this->dados['lista'] = $this->sistema_gatilho_model->order_by('NOME')->get_all();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;

        $this->render('listar_gatilho');
    }

    /*
     * Módulos
     */

    function listar_modulo ()
    {
        $this->dados['lista'] = $this->sistema_modulo_model->order_by('PASTA')->get_all();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;

        $this->render('listar_modulo');
    }

    function adicionar_modulo ()
    {
        $this->_formulario_modulo();

        $modulo_where = array('ATIVO' => 1, 'SISTEMA_MODULO_ID IS NULL' => NULL);
        $this->dados['modulos'] = array('') + $this->sistema_modulo_model->as_dropdown('NOME')->get_all($modulo_where);

        $this->render('formulario_modulo');
    }

    function editar_modulo ($id)
    {
        $this->_formulario_modulo($id);

        $modulo_where = array('ATIVO' => 1, 'SISTEMA_MODULO_ID IS NULL' => NULL);
        $this->dados['modulos'] = array('') + $this->sistema_modulo_model->as_dropdown('NOME')->get_all($modulo_where);
        $this->dados['item'] = $this->sistema_modulo_model->get(array('ID' => $id));

        $this->render('formulario_modulo');
    }

    function excluir_modulo ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $delete = $this->sistema_modulo_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Módulo excluído com sucesso');
                $this->redirect('ti_sistema/listar_modulo');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('excluir_modulo');
    }

    function _formulario_modulo ($id = NULL)
    {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('PASTA', 'Pasta', 'trim|required');

        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['SISTEMA_MODULO_ID'] = !empty($post['SISTEMA_MODULO_ID']) ? $post['SISTEMA_MODULO_ID'] : NULL;
            $post['ATIVO'] = !$id || isset($post['ATIVO']) ? TRUE : FALSE;
            if ($id)
            {
                $query = $this->sistema_modulo_model->update($post, $id);
            }
            else
            {
                $query = $this->sistema_modulo_model->insert($post);
                $id = $query;
            }
            if ($query)
            {
                $this->dados['sucesso'] = 'Módulo gravado com sucesso';
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
    }

    /*
     * Telas
     */

    function listar_tela ()
    {
        $this->dados['lista'] = $this->sistema_tela_model->order_by('SISTEMA_MODULO_ID, NOME')->get_all();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->dados['modulos'] = $this->sistema_modulo_model->as_dropdown('NOME')->get_all();

        $this->render('listar_tela');
    }

    function adicionar_tela ()
    {
        $this->_formulario_tela();
        $this->_campos_tela();

        $this->render('formulario_tela');
    }

    function editar_tela ($id)
    {
        $this->_formulario_tela($id);
        $this->_campos_tela();

        $this->dados['item'] = $this->sistema_tela_model->get(array('ID' => $id));

        $this->render('formulario_tela');
    }

    function excluir_tela ($id)
    {
        $confirmacao = $this->input->post('confirmacao');
        if ($confirmacao)
        {
            $where = array('SISTEMA_TELA_ID' => $id);
            $this->sistema_perfil_tela_model->delete($where);
            $delete = $this->sistema_tela_model->delete($id);
            if ($delete)
            {
                $this->session->set_flashdata('sucesso', 'Módulo excluído com sucesso');
                $this->redirect('ti_sistema/listar_tela');
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('excluir_tela');
    }

    function _formulario_tela ($id = NULL)
    {
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('VISAO', 'Visão', 'trim|required');
        $this->form_validation->set_rules('SISTEMA_MODULO_ID', 'Módulo', 'trim|required');

        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['SISTEMA_MODULO_ID'] = !empty($post['SISTEMA_MODULO_ID']) ? $post['SISTEMA_MODULO_ID'] : NULL;
            $post['ATIVO'] = !$id || isset($post['ATIVO']) ? TRUE : FALSE;
            if ($id)
            {
                $query = $this->sistema_tela_model->update($post, $id);
            }
            else
            {
                $query = $this->sistema_tela_model->insert($post);
                $id = $query;
            }
            if ($query)
            {
                $this->dados['item'] = new stdClass();
                $this->dados['item']->SISTEMA_MODULO_ID = $post['SISTEMA_MODULO_ID'];
                $this->dados['sucesso'] = 'Módulo gravado com sucesso';
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
    }

    function _campos_tela ()
    {
        $modulos_where = array('ATIVO' => 1, 'SISTEMA_MODULO_ID IS NULL' => NULL);
        $modulos_pai = $this->sistema_modulo_model->order_by('NOME')->get_all($modulos_where);

        $modulos_where = array('ATIVO' => 1, 'SISTEMA_MODULO_ID IS NOT NULL' => NULL);
        $modulos_filho = $this->sistema_modulo_model->order_by('NOME')->get_all($modulos_where);

        foreach ($modulos_pai as $modulo_pai)
        {
            $this->dados['modulos'][$modulo_pai->NOME] = array();
            if (!empty($modulos_filho))
            {
                foreach ($modulos_filho as $modulo_filho)
                {
                    if ($modulo_filho->SISTEMA_MODULO_ID == $modulo_pai->ID)
                    {
                        $this->dados['modulos'][$modulo_pai->NOME][$modulo_filho->ID] = $modulo_filho->NOME;
                    }
                }
            }
        }
    }

}
