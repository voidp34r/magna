<?php

/**
 * Description of Sac_enviosms
 *
 * @author Administrador
 */
class Ti_permissoes extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('usuario_model');
        $this->load->model('usuario_departamento_model');
        $this->load->model('usuario_filial_model');
        $this->load->model('usuario_perfil_model');
        $this->load->model('usuario_gatilho_model');

        $this->dados['modulo_nome'] = 'TI > Permissões';
        $this->dados['modulo_menu'] = array(
            'Usuários' => 'listar_usuario',
        );

        parent::__construct();
    }

    function index ()
    {
        $this->redirect('ti_permissoes/listar_usuario');
    }

    /*
     * Usuários
     */
    function listar_usuario($page = 1){
        $this->_filtro_like();
        $total = $this->usuario_model->count_rows();

        $this->_filtro_like();
        
        $lista = $this->usuario_model
        	->paginate(10, $total, $page);

        $this->dados['total'] = $total;
        $this->dados['lista'] = $lista;
        $this->dados['paginacao'] = $this->usuario_model->all_pages;
        
        $this->render('listar_usuario');
    }

    function adicionar_usuario ()
    {
        $this->form_validation->set_rules('USUARIO', 'Usuário', 'trim|required|alpha_dash');
        if ($this->form_validation->run())
        {
            $post = $this->input->post();
            $post['USUARIO'] = strtolower('transmagna\\' . $post['USUARIO']);
            $id = $this->usuario_model->insert($post);
            if ($id)
            {
                $this->session->set_flashdata('sucesso', 'Usuário gravado com sucesso');
                $this->redirect('ti_permissoes/editar_usuario/' . $id);
            }
            else
            {
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('adicionar_usuario');
    }

    function editar_usuario ($id)
    {
        if (($post = $this->input->post()))
        {
            $perfis = !empty($post['PERFIS']) ? $post['PERFIS'] : array();
            $this->usuario_perfil_model->delete(array('USUARIO_ID' => $id));
            $usuario_perfil = array();
            $usuario_perfil['USUARIO_ID'] = $id;
            foreach ($perfis as $perfil)
            {
                $usuario_perfil['SISTEMA_PERFIL_ID'] = $perfil;
                $this->usuario_perfil_model->insert($usuario_perfil);
            }
            $gatilhos = !empty($post['GATILHOS']) ? $post['GATILHOS'] : array();
            $this->usuario_gatilho_model->delete(array('USUARIO_ID' => $id));
            $usuario_gatilho = array();
            $usuario_gatilho['USUARIO_ID'] = $id;
            foreach ($gatilhos as $gatilho)
            {
                $usuario_gatilho['SISTEMA_GATILHO_ID'] = $gatilho;
                $this->usuario_gatilho_model->insert($usuario_gatilho);
            }
            $this->session->set_flashdata('sucesso', 'Usuário gravado com sucesso');
            $this->redirect('ti_permissoes/listar_usuario');
        }
        $this->dados['perfis'] = $this->_perfis();
        $this->dados['perfis_selecionados'] = $this->_perfis_selecionados($id);
        $this->dados['gatilhos'] = $this->_gatilhos();
        $this->dados['gatilhos_selecionados'] = $this->_gatilhos_selecionados($id);
        $_POST = $this->usuario_model->as_array()->get($id);
        $this->render('editar_usuario');
    }

    function _perfis ()
    {
        $this->load->model('ti_sistema/sistema_perfil_model');
        return $this->sistema_perfil_model
                        ->order_by('NOME')
                        ->as_dropdown('NOME')
                        ->get_all();
    }

    function _perfis_selecionados ($usuario_where)
    {
        $perfis = $this->_perfis();
        $retorno = array();
        if (is_array($usuario_where))
        {
            foreach ($usuario_where as $item)
            {
                $retorno[$item] = array();
            }
        }
        else
        {
            $retorno[$usuario_where] = array();
        }
        $usuario_perfis = $this->usuario_perfil_model
                ->where('USUARIO_ID', $usuario_where)
                ->get_all();
        if (!empty($usuario_perfis))
        {
            foreach ($usuario_perfis as $usuario_perfil)
            {
                $retorno[$usuario_perfil->USUARIO_ID][$usuario_perfil->SISTEMA_PERFIL_ID] = $perfis[$usuario_perfil->SISTEMA_PERFIL_ID];
            }
        }
        return (is_array($usuario_where)) ? $retorno : $retorno[$usuario_where];
    }

    function _gatilhos ()
    {
        $this->load->model('ti_sistema/sistema_gatilho_model');
        return $this->sistema_gatilho_model
                        ->order_by('NOME')
                        ->as_dropdown('NOME')
                        ->get_all();
    }

    function _gatilhos_selecionados ($usuario_where)
    {
        $gatilhos = $this->_gatilhos();
        $retorno = array();
        if (is_array($usuario_where))
        {
            foreach ($usuario_where as $item)
            {
                $retorno[$item] = array();
            }
        }
        else
        {
            $retorno[$usuario_where] = array();
        }
        $usuario_gatilhos = $this->usuario_gatilho_model
                ->where('USUARIO_ID', $usuario_where)
                ->get_all();
        if (!empty($usuario_gatilhos))
        {
            foreach ($usuario_gatilhos as $usuario_gatilho)
            {
                $retorno[$usuario_gatilho->USUARIO_ID][$usuario_gatilho->SISTEMA_GATILHO_ID] = $gatilhos[$usuario_gatilho->SISTEMA_GATILHO_ID];
            }
        }
        return (is_array($usuario_where)) ? $retorno : $retorno[$usuario_where];
    }

}
