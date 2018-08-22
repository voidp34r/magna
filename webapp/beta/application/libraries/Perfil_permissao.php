<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Perfil_permissao
 *
 * @author Administrador
 */
class Perfil_permissao
{

    var $CI;

    public function __construct ()
    {
        $this->CI = & get_instance();
        $this->CI->load->database();
        $this->CI->load->model('ti_sistema/sistema_modulo_model');
        $this->CI->load->model('ti_sistema/sistema_tela_model');
        $this->CI->load->model('ti_sistema/sistema_perfil_model');
        $this->CI->load->model('ti_sistema/sistema_perfil_tela_model');
        $this->CI->load->model('ti_permissoes/usuario_perfil_model');
    }

    /**
     * verifica_permissao
     * Função que verifica se o usuário tem permissão para acessar o método
     * @param string $controller
     * @param string $metodo
     * @param int $usuario_id
     * @return boolean
     */
    function verifica_permissao ($controller, $metodo, $usuario_id)
    {
        if (!$metodo)
        {
            return TRUE;
        }
        $modulo_where = array(
            'ATIVO' => 1,
            'PASTA' => $controller,
        );
        $modulo = $this->CI->sistema_modulo_model->get($modulo_where);
        if (empty($modulo))
        {
            return FALSE;
        }
        $tela_where = array(
            'ATIVO' => 1,
            'SISTEMA_MODULO_ID' => $modulo->ID,
            'VISAO' => $metodo,
        );
        $tela = $this->CI->sistema_tela_model->get($tela_where);
        if (empty($tela))
        {
            return FALSE;
        }
        $usuario_perfis = $this->CI->usuario_perfil_model->get_all(array('USUARIO_ID' => $usuario_id));
        foreach ($usuario_perfis as $usuario_perfil)
        {
            $perfis[] = $usuario_perfil->SISTEMA_PERFIL_ID;
        }
        $this->CI->db->where('SISTEMA_TELA_ID', $tela->ID);
        $this->CI->db->where_in('SISTEMA_PERFIL_ID', $perfis);
        $perfil_tela = $this->CI->sistema_perfil_tela_model->get_all();
        if (empty($perfil_tela))
        {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * retorna_telas_autorizadas
     * Função utilizada pelo retorna_modulos_autorizados para verificar as telas
     * que o usuário tem permissão de acesso
     * @param int $usuario_id
     * @return array
     */
    function retorna_telas_autorizadas ($usuario_id)
    {
        $usuario_perfis = $this->CI->usuario_perfil_model->get_all(array('USUARIO_ID' => $usuario_id));
        if (empty($usuario_perfis))
        {
            return array();
        }
        foreach ($usuario_perfis as $usuario_perfil)
        {
            $perfis[] = $usuario_perfil->SISTEMA_PERFIL_ID;
        }
        $this->CI->db->where_in('SISTEMA_PERFIL_ID', $perfis);
        $telas = array();
        $perfil_telas = $this->CI->sistema_perfil_tela_model->get_all();
        if (!empty($perfil_telas))
        {
            foreach ($perfil_telas as $perfil_tela)
            {
                $telas[] = $perfil_tela->SISTEMA_TELA_ID;
            }
        }
        return array_unique($telas);
    }

    /**
     * retorna_modulos_autorizados
     * Função utilizada pelo construtor para montar o menu lateral conforme as
     * permissões atribuídas ao usuário
     * @return array
     */
    function retorna_modulos_autorizados ($usuario_id, $mobile = NULL)
    {
        $telas_autorizadas = $this->retorna_telas_autorizadas($usuario_id);
        if (empty($telas_autorizadas))
        {
            return NULL;
        }
        $telas = $this->CI->sistema_tela_model
                ->where('ATIVO', 1)
                ->where('ID', $telas_autorizadas)
                ->get_all();
        if (empty($telas))
        {
            return NULL;
        }
        foreach ($telas as $tela)
        {
            $modulos_where[] = $tela->SISTEMA_MODULO_ID;
        }
        if ($mobile)
        {
            $this->CI->sistema_modulo_model->where('DISPONIVEL_MOBILE', 1);
        }else{
            $this->CI->sistema_modulo_model->where('DISPONIVEL_MOBILE <> ', 1);
        }
        $modulos_autorizados = $this->CI->sistema_modulo_model
                ->where('ATIVO', 1)
                ->where('ID', $modulos_where)
                ->order_by('NOME')
                ->get_all();
        if (empty($modulos_autorizados))
        {
            return NULL;
        }
        $retorno = array();
        foreach ($modulos_autorizados as $modulo_autorizado)
        {
            $modulos_pai_where[] = $modulo_autorizado->SISTEMA_MODULO_ID;
            $retorno['submenu'][$modulo_autorizado->SISTEMA_MODULO_ID][] = $modulo_autorizado;
        }
        $retorno['menu'] = $this->CI->sistema_modulo_model
                ->where('ATIVO', 1)
                ->where('ID', $modulos_pai_where)
                ->order_by('NOME')
                ->get_all();
        return $retorno;
    }

}
