<?php


class Ti_mobile extends MY_Controller{
    
    public function __construct(){
        $this->dados['modulo_nome'] = 'TI > Mobile';
        $this->dados['modulo_menu'] = array("Checklist Configurações" => "checklist_config",
                                            "Usuários" => "listar_usuario"
                                            );
        $this->load->model('usuario_portaria_model');
        $this->load->model('Portaria_foto_config');
        $this->load->model('ti_permissoes/usuario_model');
        $this->load->model('ti_permissoes/usuario_perfil_model');
        $this->load->model('portaria_foto_categoria_model');
        $this->load->library('softran_oracle');
        parent::__construct();
    }
    
    function index(){
        $this->checklist_config(); 
    }
    
    public function listar_usuario($page = 1){
        //Seta o where duas vezes para cada select realizado no banco
        $this->usuario_portaria_model->where('ISATIVO',1);
        $total = $this->usuario_portaria_model->count_rows();
        $this->usuario_portaria_model->where('ISATIVO',1);
        $lista = $this->usuario_portaria_model->order_by('USUARIO_NOME','DESC')->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->usuario_portaria_model->all_pages;
        $this->render('listar_usuario');
    }

    public function editar_usuario($id){
        
        $this->form_validation->set_rules('USUARIO_NOME', 'Usuário Nome', 'trim|required');
        $this->form_validation->set_rules('USUARIO', 'Usuário', 'trim|required');
        $this->form_validation->set_rules('CDEMPRESA', 'Filial do usuário', 'trim|required'); 
        
        if ($this->form_validation->run()){
            $post = $this->input->post();
            $post['USUARIO'] = str_replace('-','',str_replace(".", "", $post['USUARIO']));
            $post['ISATIVO'] = isset($post['ISATIVO']) ? 1 : 0; 
            
            
            if($this->usuario_portaria_model->update($post, $id)){
                $update_data = array();

                //remove a mascara do CPF 123.456.789-00 para 12345678900 
                $update_data['USUARIO'] = str_replace('-','',str_replace(".", "", $post['USUARIO']));
                $update_data['NOME'] = strtoupper($post['USUARIO_NOME']) ;
                $update_data['CDEMPRESA'] = $post['CDEMPRESA'];

                $this->usuario_model->where('USUARIO_PORTARIA_ID',$id);
                if($this->usuario_model->update($update_data)){
                   $this->redirect('ti_usuario/listar_usuario', 'sucesso', 'Usuário alterado com sucesso!'); 
                }else{
                    $this->dados['erro'] = 'Erro ao editar a vinculação do usuário.';
                }

            }else{
                $this->dados['erro'] = 'Erro ao editar o usuário da portaria.';
            }            
                
        }
        
        $usuario =  $this->usuario_portaria_model->get(array("ID" => $id));
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        //Popula o Post com os campos
        foreach ($usuario as $campo => $valor){
            $_POST[$campo] = $valor;
        }
        
        $this->render('editar_usuario');
    }
    
    public function excluir_usuario($id){
        
        try
        {
            $this->usuario_model->where('USUARIO_PORTARIA_ID',$id);
            $this->usuario_model->update(array("ISATIVO" => 0));
            $this->usuario_portaria_model->where('ID',$id);
            $this->usuario_portaria_model->update(array("ISATIVO" => 0));            
            $this->redirect('ti_usuario/listar_usuario', 'sucesso', 'Usuário excluido com sucesso!');
            
        }catch(Exception $err){
            $this->dados['erro'] = 'Erro ao excluir usuário';
        }
        
        
    }
    
    /**
    * @param string $userName
    * @description retorna o objeto com o usuário passado por parametro, caso não exista retorna false 
    **/
    private function validaUsuarioExistente($userName){
         
        return $this->usuario_portaria_model->get(array("USUARIO" => $userName));;
    }
    
    public function alterar_senha(){
        
        $jsonRet = new stdClass();
        $post = $this->input->post();
        $senha = password_hash($post['pass'], PASSWORD_DEFAULT);
        $id = $post['id'];
        
        $retorno = $this->usuario_portaria_model->update(array('SENHA' => $senha), $id);
        
        if($retorno){
           $jsonRet->ok = true; 
           echo json_encode($jsonRet); 
        }else{
            $jsonRet->ok = false;
            echo json_encode($jsonRet); 
        }
    }
    
    public function cadastar_usuario(){
        
        $this->form_validation->set_rules('USUARIO_NOME', 'Usuário Nome', 'trim|required');
        $this->form_validation->set_rules('USUARIO', 'Usuário', 'trim|required'); 
        $this->form_validation->set_rules('SENHA', 'Usuário Senha', 'trim|required'); 
        $this->form_validation->set_rules('CDEMPRESA', 'Filial do usuário', 'trim|required'); 
 		
        if ($this->form_validation->run()){
            $post = $this->input->post();
            $post['USUARIO'] = str_replace('-','',str_replace(".", "", $post['USUARIO']));
            $post['SENHA']   = password_hash($post['SENHA'], PASSWORD_DEFAULT);
            $post['ISATIVO'] = 1;
            
            $usuario = $this->validaUsuarioExistente($post['USUARIO']);
            
            if(!$usuario){
              
               $idUsuPor =  $this->usuario_portaria_model->insert($post);
               if($idUsuPor){
                   $insert_data = array();
                   //remove a mascara do CPF 123.456.789-00 para 12345678900 
                   $insert_data['USUARIO'] = str_replace('-','',str_replace(".", "", $post['USUARIO']));
                   $insert_data['NOME'] = strtoupper($post['USUARIO_NOME']);
                   $insert_data['CDEMPRESA'] = $post['CDEMPRESA'];
                   $insert_data['USUARIO_PORTARIA_ID'] = $idUsuPor;
                   $insert_data['ISATIVO'] = '1';

                   if($this->usuario_model->insert($insert_data)){
                       $this->redirect('ti_usuario/listar_usuario', 'sucesso', 'Usuário cadastrado com sucesso!'); 
                   }else{
                       $this->dados['erro'] = 'Erro ao vincular o usuário da portaria';
                   }

               }else{
                   $this->dados['erro'] = 'Erro ao gravar o usuário da portaria';
               }   
                
            }else{
                $html = "Já existe um usuário com este login:<br>
                         <a href='ti_usuario/editar_usuario/{$usuario->ID}'>{$usuario->USUARIO_NOME}</a>
                         ";
                
                $this->dados['erro'] = $html;
                
            }
            

                
            
        }
        $this->dados['filiais'] = $this->softran_oracle->empresas();
        $this->render('cadastrar_usuario');
    }
    
    public function salvar_foto_config(){
        
        $id = $this->Portaria_foto_config->insert($this->input->post());
        echo json_encode($id ? true : false);
    }
    
    public function atualizar_foto_config($id = null){
        
        $idRet = $this->Portaria_foto_config->update($this->input->post(),$id);
        echo json_encode($idRet ? true : false);
    }    
    
    public function remover_foto_config($id = null){
        
        $idRet = $this->Portaria_foto_config->delete(array('ID' => $id));
        echo json_encode($idRet ? true : false);
    }
    
    public function load_foto_config(){
        $obj = new stdClass();
        $obj->arr = $this->db->query('select A.*,B.NOME AS CATEGORIA from PORTARIA_FOTO_CONFIG A LEFT JOIN PORTARIA_FOTO_CATEGORIA B ON B.ID = A.FOTO_CATEGORIA_ID ')->result();
        echo json_encode($obj);
        
    }
    
    public function checklist_config() {
        $this->dados['categoria'] = $this->portaria_foto_categoria_model->as_dropdown('NOME')->get_all();
        $this->render('checklist_config');
    }
    
    public function salvar_categoria(){
        
        $retid = $this->portaria_foto_categoria_model->insert($this->input->post());
        echo json_encode($retid) ? true : false;
    }
    
    public function load_categoria(){
        
       echo json_encode($this->portaria_foto_categoria_model->get_all());
        
    }
    
    public function remover_categoria($id = null){
        
        $idRet = $this->portaria_foto_categoria_model->update(array('flInativo' => 1),$id);
        echo json_encode($idRet ? true : false);    
    }
    
    public function atualizar_categoria($id = null){
        
        $idRet = $this->portaria_foto_categoria_model->update($this->input->post(),$id);
        echo json_encode($idRet ? true : false);
    }    
    
    public function checklist_sequencia(){
        
        $obj =  $this->portaria_foto_categoria_model->where('FLINATIVO',0)
                     ->order_by('SEQUENCIA', 'ASC')
                     ->get_all();
        
        echo json_encode($obj);
    }
    
    public function atualiza_checklist_sequencia(){
        $obj = $this->input->post();
        $ok = true;
        foreach ($obj[arr] as $key => $value) {
            $objUpd = new stdClass();
            $objUpd->ID = $value[ID];
            $objUpd->SEQUENCIA = $value[SEQUENCIA];
            
            $ret =  $this->portaria_foto_categoria_model->update(array('SEQUENCIA' => $objUpd->SEQUENCIA),$objUpd->ID);
            if(!$ret)
                $ok = false;
        }
        
        echo json_encode($ok);
    }
}
