<?php

/**
 * @author andretimm
 */
class Rh_biometria extends MY_Controller{



    public function __construct(){

        $this->load->model('ti_biometria/biometria_equipamento_model');
        $this->load->model('ti_biometria/biometria_usuario_digital_model');
        $this->load->model('ti_biometria/biometria_usuario_model');
        $this->load->model('ti_permissoes/usuario_model');
        $this->load->model('ti_biometria/biometria_fila_model');
        $this->load->library('pagination');

        $this->dados['modulo_nome'] = 'RH > Biometroa';
        $this->dados['modulo_menu'] = array('Usuários' => 'lista_usuarios', 'Usuários - ID Secure' => 'lista_usuario_idSecure');
        
        parent::__construct();
    }

    function index(){
        $this->redirect('rh_biometria/cadastro_usuario');
    }

    function lista_usuario_idSecure(){
			$equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE','SINCRONIZAR' => 1));
			if($equipamento){
					foreach($equipamento as $index => $valor){
						$constructIdSecure = ["ip" => $equipamento[$index]->IP, "port" => $equipamento[$index]->PORTA, "user" => $equipamento[$index]->USUARIO, "password" => $equipamento[$index]->SENHA,"protocol"=>$equipamento[$index]->PROTOCOLO];
						$this->load->library('IdSecure',$constructIdSecure);
						if($this->idsecure->authenticate()){
								// print_r("autenticado");
                                // $users = $this->idsecure->getAllUsersIdSecure();
                                $users = $this->idsecure->getAllUsers();
								$total = count($users);
								$lista = $users;
						} else {
								// print_r("NAO autenticado");
						}
					}
			}
				$this->dados['lista'] = $lista;
				$this->dados['total'] = $total;
				$this->render('view_usuario_idSecure'); 
				// $this->render('listar_usuario_idSecure');
		}
      
		function listar_usuario_idSecure(){
			$temp = [];
			$temparray = [];
			$filtro = $_GET['filtro'];
			$nome = $filtro[like][nome];
			$cpf = $filtro[like][cpf];
			$status = $_GET['status'];
			
			$equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE','SINCRONIZAR' => 1));
			if($equipamento){
				foreach($equipamento as $index => $valor){
					$constructIdSecure = ["ip" => $equipamento[$index]->IP, "port" => $equipamento[$index]->PORTA, "user" => $equipamento[$index]->USUARIO, "password" => $equipamento[$index]->SENHA,"protocol"=>$equipamento[$index]->PROTOCOLO];
					$this->load->library('IdSecure',$constructIdSecure);
					if($this->idsecure->authenticate()){
						// print_r("autenticado");
						if ($nome === '' && $cpf === '') {
                            // $users = $this->idsecure->getAllUsersIdSecure();
                            $users = $this->idsecure->getUsers();
							
						} else {
							if ($cpf) {
                                // $users = $this->idsecure->getUserIdSecure($cpf);
                                $users = $this->idsecure->getUser($cpf);
								$total = count($users);
								$lista = $users;
							}
							if ($nome) {
								$users = $this->idsecure->getUser($nome);
								$total = count($users);
								$lista = $users;
							}
						}
					} else {
						// print_r("NAO autenticado");
					}
				}
			}
				$this->dados['lista'] = $lista;
				$this->dados['total'] = $total;
				$this->render('listar_usuario_idSecure');
		}

		function excluir_usuario($user) {
			$equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE','SINCRONIZAR' => 1));
			if($equipamento){
				foreach($equipamento as $index => $valor){
					$constructIdSecure = ["ip" => $equipamento[$index]->IP, "port" => $equipamento[$index]->PORTA, "user" => $equipamento[$index]->USUARIO, "password" => $equipamento[$index]->SENHA,"protocol"=>$equipamento[$index]->PROTOCOLO];
					$this->load->library('IdSecure',$constructIdSecure);
					if($this->idsecure->authenticate()){
						$del = $this->idsecure->removeUser($user);
					} else {
					}
				}
			}
			$this->redirect('rh_biometria/lista_usuario_idSecure');
		}

    function lista_usuarios($page = 0){
        if($page <> 0) $page = (($page - 1) * 9) + 1;
        $this->db->from("BIOMETRIA_USUARIO");
        $this->db->join('SOFTRAN_MAGNA.GTCFUNDP', 'SOFTRAN_MAGNA.GTCFUNDP.NRCPF = BIOMETRIA_USUARIO.CPF'); 
        $this->_filtro_where();
        $this->_filtro_like();
        $total = $this->db->get()->num_rows();        

        $this->db->from("BIOMETRIA_USUARIO");
        $this->db->join('SOFTRAN_MAGNA.GTCFUNDP', 'SOFTRAN_MAGNA.GTCFUNDP.NRCPF = BIOMETRIA_USUARIO.CPF'); 
        $this->_filtro_where();
        $this->_filtro_like();
        $this->db->limit(10,$page);
        $this->db->order_by('DSNOME','ASC');

        $lista = $this->db->get()->result();    

        foreach ($lista as $usr){
            $usr->QTDIGITAIS = $this->db->query("SELECT * FROM BIOMETRIA_USUARIO_DIGITAL WHERE USUARIOCPF = '$usr->CPF'")->num_rows();
        }
        
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'rh_biometria/lista_usuarios');
        $this->render('lista_usuario');
    }

   

    function listar_usuario($page = 0){

        $this->db->from("BIOMETRIA_USUARIO");
        $this->db->join('SOFTRAN_MAGNA.GTCFUNDP', 'SOFTRAN_MAGNA.GTCFUNDP.NRCPF = BIOMETRIA_USUARIO.CPF'); 
        $this->_filtro_where();
        $this->_filtro_like();
        $total = $this->db->get()->num_rows();        

        $this->db->from("BIOMETRIA_USUARIO");
        $this->db->join('SOFTRAN_MAGNA.GTCFUNDP', 'SOFTRAN_MAGNA.GTCFUNDP.NRCPF = BIOMETRIA_USUARIO.CPF'); 
        $this->_filtro_where();
        $this->_filtro_like();
        $this->db->limit(10,$page);
        $this->db->order_by('DSNOME','ASC');

        $lista = $this->db->get()->result();    

        foreach ($lista as $usr){
            $usr->QTDIGITAIS = $this->db->query("SELECT * FROM BIOMETRIA_USUARIO_DIGITAL WHERE USUARIOCPF = '$usr->CPF'")->num_rows();
        }

        
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'rh_biometria/listar_usuario');


        $this->render('lista_usuario');
    }  

    function configurePagination($perPage = 10 ,$totalRows = 100,$BaseUrl = ""){
        $config = array();
        $config["per_page"] = $perPage;
        $config["base_url"] = base_url() . $BaseUrl;
        $config["total_rows"] = $totalRows;
        $config['use_page_numbers'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = '1';
        $config['last_link'] = ceil($totalRows/$perPage);
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);  	
        return $this->pagination->create_links();
    }

    function cadastro_usuario($page = 0){    
        $this->dados['titulo'] = 'Adicionar';   
        $this->dados['tipo'] = '1';  
        $this->dados['departamentos'] = $this->getDepartment();
        $this->render('cadastro_usuario');
    }
    
    function editar_usuario($id){
        $this->dados['titulo'] = 'Editar';
        
        $this->dados['departamentos'] = $this->getDepartment();
        if($user = $this->biometria_usuario_model->get(["ID" => $id])){
            $this->dados['tipo'] = $user->TIPO;
            //$this->form_validation->set_rules('PORTA', 'Porta', 'trim');
        }
        $this->render('cadastro_usuario');  
    }

    public function retornaUsuario(){   
        $data = $this->input->post();
        $ID = $data['ID'];
        if($user = $this->biometria_usuario_model->get(["ID" => $ID])){  
            $user->CPF = substr($user->CPF, 3, strlen($user->CPF));
            $this->load->library('SoftranUsuario');
            if(!empty($consultaRet = $this->softranusuario->getFuncionario($user->CPF))){
                $user->error = false;
                $user->funcionario = $consultaRet;
                echo json_encode($user); 
            }                     
        }else{
            echo json_encode([]);
        }
    }

    public function valida_usuarioCpf($func = true){
        if($_POST['CPF']){            
                $cpfLabel = $_POST['CPF']; 
                $this->load->library('SoftranUsuario');
                $responseObj = new StdClass();            
                $cpf  = str_replace('-','',str_replace('.','',$_POST['CPF']));
                //Verifica se o usuário consta no sistema
                if($func){
                    if(!empty($consultaRet = $this->softranusuario->getFuncionario($cpf))){    
                        if(!$this->biometria_usuario_digital_model->get(["USUARIOCPF" => '000'.$cpf])){
                            $responseObj->error = false;
                            $responseObj->funcionario =  $consultaRet;
                        }else{
                            $responseObj->error = true;
                            $responseObj->errorMg = "O CPF $cpfLabel está cadastrado para: $consultaRet->DSNOME ";
                        }  
                    }else{
                        $responseObj->error = true;
                        $responseObj->errorMg = "O CPF $cpfLabel não consta no sistema";
                    }    
                }else{                    
                    if(!empty($consultaRet = $this->softranusuario->getFuncionarioFreteiro('000'.$cpf))){    
                        if(!$this->biometria_usuario_digital_model->get(["USUARIOCPF" => '000'.$cpf])){
                            $responseObj->error = false;
                            $responseObj->funcionario =  $consultaRet;
                        }else{
                            $responseObj->error = true;
                            $responseObj->errorMg = "O CPF $cpfLabel está cadastrado para: $consultaRet->DSNOME ";
                        }  
                    }else{
                        $responseObj->error = true;
                        $responseObj->errorMg = "O CPF $cpfLabel não consta no sistema";
                    }  
                }        
            echo json_encode($responseObj);
        }
    }

    public function addUser(){
        $post = $this->input->post();
        $tipo = $post["tipo"];
        $this->usuario_model->where("ID",$this->sessao['usuario_id']);
        $empresa = $this->usuario_model->get()->CDEMPRESA;
        $retorno = array("status"=>true,"msg"=>""); 
        // Adiciona no IdSecure 
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE','SINCRONIZAR' => 1));
        if($equipamento){
          foreach($equipamento as $index => $valor){
            $constructIdSecure = ["ip" => $equipamento[$index]->IP, "port" => $equipamento[$index]->PORTA, "user" => $equipamento[$index]->USUARIO, "password" => $equipamento[$index]->SENHA,"protocol"=>$equipamento[$index]->PROTOCOLO];
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){
                $users = [];
                $user = new StdClass(); 
                if(isset($post["pis"])){
                    if($post["pis"] != ''){
                        $user->pis = $post["pis"];
                        $user->rg = str_replace('-','',str_replace('.','',$_POST['rg']));
                    }
                }
                $user->cpf = $post["cpf"];
                $user->registration = $post["registration"];
                $user->nome = $post["nome"];
                $user->departamento = $post["departamento"];
                $user->tipo = $tipo;
                $user->templates = $post["templates"];
                array_push($users, $user);
                if($this->idsecure->addUser($users,true)){
                    $retorno['status'] = true;
                    $retorno['msg'] = "Sucesso";
                }else{
                    $retorno['status'] = false;
                    $retorno['msg'] = "Não foi possivel cadastrar no IdSecure";
                }
            }else{
                $retorno['status'] = false;
                $retorno['msg'] = "Não foi possivel logar no IdSecure";
            }
          }
        }
        // Adiciona nos reps
        if($retorno['status'] && $tipo == 1){
            $equipamento = $this->biometria_equipamento_model->get_all(array("CDEMPRESA" => $empresa, "TIPO"=>"IDCLASS", "SINCRONIZAR" => 1)); //"SINCRONIZAR" => 1
            if($equipamento){
                $retornoEquip = new StdClass();
                
                $userRep = new StdClass(); 
                $userRep->users = [] ;
                $datRep = new StdClass(); 
                $datRep->admin = false;
                $datRep->name = $post["nome"];
                $datRep->pis = $post["pis"] * 1;
                $datRep->bars = "";
                $datRep->rfid = 0;
                $datRep->code = 0;
                $datRep->password = "";
                $datRep->templates = $post["templates"];
                array_push($userRep->users, $datRep);
                $retornoEquip->json = $userRep;
                $retornoEquip->equipamento = [];
                foreach ($equipamento as $key => $value) {
                    $constructIdClass = ["ip" => $value->IP, "port" => $value->PORTA, "user" => $value->USUARIO, "password" => $value->SENHA,"protocol"=>$value->PROTOCOLO];
                    $this->load->library('IdClass',$constructIdClass);
                    if($this->idclass->authenticate()){
                        $retornoEquip->status = true;
                        array_push($retornoEquip->equipamento, $constructIdClass);
                        
                        $retorno['status'] = true;
                        $retorno['msg'] = "Sucesso";
                    }else{
                        $retornoEquip->status = false;
                        $retorno['status'] = false;
                        $retorno['msg'] = "Não foi possivel logar no IdClass {$value->IP}";
                    }
                }
                $this->biometria_fila_model->delete('*');
                // $arrEquipamentos = $this->biometria_equipamento_model->get_all(array("CDEMPRESA" => $empresa, "INTEGRADO" => 1));
                $arrEquipamentos = $this->biometria_equipamento_model->get_all(["CDEMPRESA" => $empresa, "TIPO"=>"IDACCESS"]);
                foreach($arrEquipamentos as $equipamento){
                  $this->biometria_fila_model->insert(["EQUIPAMENTOID" => $equipamento->ID, "USUARIOCPF" => "000".$post["cpf"], "OPERACAO" => "CADASTRO"]);                
                }
            }
        }
        // if($retorno['status'] || $tipo == 2){  
        //     $arrEquipamentos = $this->biometria_equipamento_model->get_all(["INTEGRADO <>" => 0]);            
        //     foreach($arrEquipamentos as $equipamento){            
        //         $this->biometria_fila_model->insert(["EQUIPAMENTOID" => $equipamento->ID, "USUARIOCPF" => "000".$post["cpf"], "OPERACAO" => "CADASTRO"]);                
        //     }
        //     echo json_encode($retornoEquip);
        //     //echo json_encode(array("status"=>true));
        // }else{
        //     echo json_encode($retorno);
        // }
        
        //echo json_encode($retorno);
        echo json_encode($retornoEquip);
    }

    public function getDepartment(){
        $ret = array("status"=>false);
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE','SINCRONIZAR' => 1));
        if($equipamento){
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){
                if($departments = $this->idsecure->getDepartments()){
                    $ret = $departments;
                }
            }
        }
        return $ret;        
    }

}