<?php

/**
 * Description of Principal_mobile
 *
 * @author Administrador
 */
class Autenticacao extends MX_Controller
{

    function __construct (){
        $this->load->library('auth_ldap');
        $this->load->library('portariaLogin');
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method == "OPTIONS") {
            die();
        }
        parent::__construct();
    }

    function index_options (){
        return $this->response(NULL, 200);
    }

    function index (){
        if ($this->auth_ldap->is_authenticated()){
            redirect('principal/privado');
        } else {
            $this->login();
        }
    }

    function login ($mobile = NULL){
        $this->load->library('perfil_permissao');
        
        $this->form_validation->set_rules('usuario', 'Usuário', 'required|alpha_dash|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|trim');

        if ($this->form_validation->run()){
        	$usuario = strtolower('transmagna\\' . $this->input->post('usuario'));
        	$senha = $this->input->post('senha');
        	
        	$ldap_login = $this->auth_ldap->login($usuario, $senha);
        	
        	if (!empty($ldap_login)){
        	    $status_id = 0;
        	} else {
                    
                    //Caso nao ache o usuário procura no cadastro da portaria - Cadastro utilizado no mobile - Seta somente o usuáriom não "transmagna\\usuario"
                    if(!empty($this->portarialogin->logarPortaria(strtolower($this->input->post('usuario')),$senha)) ){
                        $usuario = $this->input->post('usuario');
                        $status_id = 0;
                    }else{
                        $status_id = 1;
                    }
                        
        	}
                
        } else {
        	$status_id = 2;
        }
        
        $status = array(
            'OK',
            'Usuario ou senha incorretos',
            'Usuario e senha obrigatorios',
        );
        
        if ($status_id == 0){
            $get = $this->_verifica_usuario($usuario);
            if ($get == null){
            	$status_id = 1;
            }
        }
        if ($mobile){
            if ($status_id == 0){
                $retorno['usuario'] = $get;
                $retorno['permissao_modulos'] = $this->perfil_permissao->retorna_modulos_autorizados($get->ID, TRUE);
                $retorno['permissao_telas'] = $this->perfil_permissao->retorna_telas_autorizadas($get->ID);
            }
            $retorno['status'] = $status[$status_id];
            
            echo json_encode($retorno);
        } else {
            $data = array();
            if ($status_id == 0){
                redirect('principal/privado');
            }
            if ($status_id == 1){
                $data['erro'] = $status[$status_id];
            }
            $this->load->view('login', $data);
        }
    }

    function sair (){
        if ($this->session->userdata('logado')){
            $this->auth_ldap->logout();
        }
        redirect('');
    }

    function _verifica_usuario ($usuario = NULL){
        $this->load->library('softran_oracle');
        $this->load->model('ti_permissoes/usuario_model');
        $this->load->model('ti_permissoes/usuario_sessao_model');
        $this->sessao = $this->session->all_userdata();
        if (!$usuario && !empty($this->sessao['usuario'])){
            $usuario = $this->sessao['usuario'];
        }
        
        if (!$usuario){
            return NULL;
        }
        
        $usuarioWhere = [
        		'USUARIO' => $usuario,
        		'ISATIVO' => 1
                        ];
        
        $get = $this->usuario_model
                    ->where($usuarioWhere)
                    ->get();
         
        //Verifica se é mobile ou não
         if(strpos($usuario, "transmagna") !== false){
            $funcionario = $this->softran_oracle->dados_funcionario($usuario);      
            $filial = !empty($funcionario->EMPRESA_ID) ? $funcionario->EMPRESA_ID : '';
            $centro_custo = !empty($funcionario->CENTRO_CUSTO_ID) ? $funcionario->CENTRO_CUSTO_ID : '';
         }else{ 
            $filial = $this->portarialogin->getFilialUsuarioPortaria($usuario); 
            $centro_custo = '';
        }
        
        $agora = date('YmdHis');
        // Quando for desenvolvimento fixar uma filial
        if(ENVIRONMENT == 'development') $filial = 6;        
        if ($get == FALSE){
            $insert_data = array();
            $insert_data['ULTIMO_ACESSO'] = $agora;
            $insert_data['USUARIO'] = $this->sessao['usuario'];
            $insert_data['NOME'] = $this->sessao['usuario_nome'];
            $insert_data['EMAIL'] = $this->sessao['usuario_email'];
            $insert_data['CDEMPRESA'] = $filial;
            $insert_data['CDCENTROCUSTO'] = $centro_custo;
            $insert_data['ISATIVO'] = 1;
            
            $id = $this->usuario_model->insert($insert_data);
            if (!$id){
            	return null;
            }
            
            $get = $this->usuario_model->get($id);
            $this->session->set_userdata('usuario_id', $id);
        } else {
            $update_data = array();
            $update_data['ULTIMO_ACESSO'] = $agora;
            $update_data['CDEMPRESA'] = $filial;
            $update_data['CDCENTROCUSTO'] = $centro_custo;
            if ($this->sessao['usuario_nome']){
                $update_data['NOME'] = $this->sessao['usuario_nome'];
            }
            
            if ($this->sessao['usuario_email']){
                $update_data['EMAIL'] = $this->sessao['usuario_email'];
            }
            
            $this->usuario_model->update($update_data, $get->ID);
            $this->session->set_userdata('usuario_id', $get->ID);
        }
        $sessao_where = array(
            'USUARIO_ID' => $get->ID,
            'IP' => $_SERVER['REMOTE_ADDR'],
            'USER_AGENT' => $_SERVER["HTTP_USER_AGENT"]);
        
        $sessao = $this->usuario_sessao_model->where($sessao_where)->get();
        
        if (!empty($sessao)){
            $um_mes = (60 * 60 * 24 * 30);
            $uma_semana = (60 * 60 * 24 * 7);
            if (($agora - $sessao->DATAHORA) > $um_mes || ($agora - $sessao->ULTIMO_ACESSO) > $uma_semana){
                $this->usuario_sessao_model->delete(array('USUARIO_ID' => $get->ID));
            } else {
                $get->TOKEN = $sessao->TOKEN;
                $this->usuario_sessao_model->update(array('ULTIMO_ACESSO' => $agora), $sessao->ID);
            }
        }
        if (!isset($get->TOKEN)){
            $sessao_insert = $sessao_where;
            $sessao_insert['DATAHORA'] = $agora;
            $sessao_insert['ULTIMO_ACESSO'] = $agora;
            $sessao_id = $this->usuario_sessao_model->insert($sessao_insert);
            $get->TOKEN = sha1($sessao_id);
            $this->usuario_sessao_model->update(array('TOKEN' => $get->TOKEN), $sessao_id);
        }
        
        $get->HASH = password_hash($get->TOKEN, PASSWORD_DEFAULT);

        $this->session->set_userdata('token', $get->TOKEN);
        $this->session->set_userdata('filial', $filial);
        $this->session->set_userdata('centro_custo', $centro_custo);
        
        return $get;
    }
}
