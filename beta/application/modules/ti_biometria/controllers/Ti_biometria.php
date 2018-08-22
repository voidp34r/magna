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
        $this->load->model('biometria_erro_model');
        $this->load->model('biometria_fila_model');
        $this->load->model('biometria_usu_equip_model');
        $this->load->model('ti_permissoes/usuario_model');
        $this->load->library('pagination');

        $this->dados['modulo_nome'] = 'TI > Biometria';
        $this->dados['modulo_menu'] = array('Usuários' => 'listar_usuario',
                                            'Equipamentos' => 'listar_equipamento',
                                            'Fila' => 'listar_fila',
                                            'Erros' => 'listar_erro');

        $this->publico = array('combinar_template','valida_usuarioCpf','retornaEquipamento','retornaEquipamentoPadraoFilial','salvarOperacaoBd',
                               'retornaIdClass','salvar_usuario','retornaEquipamentoUsuario','retornaUsuario','removerUsuario','processar_fila');
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
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'ti_biometria/listar_usuario');


        $this->render('listar_usuario');
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

    function listar_fila($page = 1){

        $this->_filtro_where();
        $this->_filtro_like();
        $total = $this->biometria_fila_model->count_rows();

        $this->_filtro_where();
        $this->_filtro_like();
        $lista = $this->biometria_fila_model->paginate(10, $total, $page);

        if(!empty($lista)){
            foreach ($lista as $fila){
                $fila->USUARIONOME =   $this->joinEquipamento($fila->USUARIOCPF);
                $fila->EQUIPAMENTODESC   = $this->joinUsuario($fila->EQUIPAMENTOID);                               
            }
        }        

        
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_fila_model->all_pages;
        
        $this->render('listar_fila');
    }

    function listar_erro($page = 1){

        $this->_filtro_where();
        $this->_filtro_like();
        $total = $this->biometria_erro_model->count_rows();

        $this->_filtro_where();
        $this->_filtro_like();
        $lista = $this->biometria_erro_model->paginate(10, $total, $page);

        if(!empty($lista)){
            foreach ($lista as $fila){
                $fila->USUARIONOME       =   $this->joinEquipamento($fila->USUARIOCPF);
                $fila->EQUIPAMENTONOME   =  $this->joinUsuario($fila->EQUIPAMENTOID);                                        
            }        
        }

        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->biometria_erro_model->all_pages;
        
        $this->render('listar_erro');
    }


    function joinUsuario($equipID){
        
        $rs = $this->db->query(" SELECT NOME FROM BIOMETRIA_EQUIPAMENTO WHERE ID = $equipID  ");
                    
        
        if($rs->num_rows()){
            return $rs->result()[0]->NOME;
        }
        
    }

    function joinEquipamento($cpf){

       $rs = $this->db->query(" SELECT DSNOME FROM SOFTRAN_MAGNA.GTCFUNDP WHERE NRCPF = '$cpf' ");
       if($rs){
             return $rs->result()[0]->DSNOME;
       }         
    }

    function adicionar_usuario(){           
        $this->dados['titulo'] = 'Adicionar';
        $this->render('formulario_usuario');
    }


    function salvar_usuario(){   
        $edit = false;
        $post = $this->input->post();

        $usuario = new StdClass();
        $usuario->CPF = $post['usuario']['cpf'];

        $this->addLog($post);
        $this->addBiometria($post);

        if(isset($post['usuario']['comentario'])){
            $usuario->DSCOMENTARIO = $post['usuario']['comentario'];
        }

        if(!isset($post['usuario']['edicao'])){
            $this->biometria_usuario_model->insert($usuario);
        }else{
            $edit = true;
            $this->biometria_usuario_model->where('CPF',$usuario->CPF);
            
            if(isset($usuario->DSCOMENTARIO))
                $arrUpd['DSCOMENTARIO'] = $usuario->DSCOMENTARIO;
            
            $this->biometria_usuario_model->update($arrUpd);
        }

        echo json_encode(["status" => "ok","edit" => $edit]);
       
    }

    function importToIdbox(){
        $userObj = [];
        $constructParams = ["ip" => '192.168.99.250', "user" => 'admin', "password" => 'mgn1409',"protocol"=>'http'];
        $this->load->library('IdClass',$constructParams);
        if($this->idclass->authenticate()){
            $json = '{  
                        "join":"LEFT",
                        "object":"users",
                        "fields":[  
                        ],
                        "where":[  
                        ],
                        "order":[  

                        ]
                    }';
            $data = json_decode($json);
            $users = json_decode($this->idclass->callRequest('load_objects',$data,true));
            set_time_limit(1500);
            foreach($users->users as $user){
                $jsonGroup = '{  
                                "join":"LEFT",
                                "object":"groups",
                                "fields":[  
                                    "id",
                                    "name"
                                ],
                                "where":[  
                                {  
                                    "object":"users",
                                    "field":"id",
                                    "value":'.$user->id.',
                                    "connector":") AND ("
                                }
                                ],
                                "order":[  
                                    "id"
                                ],
                                "limit":30,
                                "offset":0
                            }';
                
                $dataGroup = json_decode($jsonGroup);
                $groupName = [];
                if($groups = json_decode($this->idclass->callRequest('load_objects',$dataGroup,true))){
                    foreach($groups->groups as $group){
                        array_push($groupName , $group->name);
                    }
                }
                $jsonUser = '{  
                                "join":"LEFT",
                                "object":"c_users",
                                "fields":[  
                                    "user_id",
                                    "column_1"
                                ],
                                "where":[  
                                {  
                                    "object":"users",
                                    "field":"id",
                                    "value":'.$user->id.',
                                    "connector":") AND ("
                                }
                                ],
                                    "order":[  
                                    "id"
                                ],
                                "limit":1,
                                "offset":0
                            }';
                $dataUser = json_decode($jsonUser); 
                $c_user = json_decode($this->idclass->callRequest('load_objects',$dataUser,true)); 
                $jsonTemplate = '{  
                                    "join":"LEFT",
                                    "object":"templates",
                                    "fields":[                                  
                                    ],
                                    "where":[ 
                                    {  
                                        "object":"templates",
                                        "field":"user_id",
                                        "value":'.$user->id.',
                                        "connector":") AND ("
                                    }
                                    ],
                                    "order":[                              
                                    ]
                                }';
                $dataTemplate = json_decode($jsonTemplate);
                $templates = json_decode($this->idclass->callRequest('load_objects',$dataTemplate,true));
                if($templates->templates){                    
                    $u = new StdClass();
                    if($c_user->c_users){
                        if($c_user->c_users[0]->column_1){
                            $user->registration = str_replace('-','',str_replace('.','',$user->registration));
                            $c_user->c_users[0]->column_1 = str_replace('-','',str_replace('.','',$c_user->c_users[0]->column_1));                            
                            $result = $this->db->query("SELECT CDFUNCIONARIO, NRPIS, NRCPF, NRRG, FGDEMITIDO, CDMATRICULA, DSNOME FROM SOFTRAN_MAGNA.SISFUN WHERE NRCPF = ".$c_user->c_users[0]->column_1." AND DSNOME LIKE '%".trim(substr($user->name, strpos ($user->name, '-')+1))."%'");
                            if($result->num_rows() > 0){
                                $result = $result->result()[0];
                                $u->registration = $user->registration;
                                $u->cpf = $result->NRCPF;                                   
                                $u->pis = $result->NRPIS; 
                                $u->rg = $result->NRRG;
                                //$u->nome = $result->DSNOME;
                                $u->demitido = $result->FGDEMITIDO;                                     
                            }else{
                                $u->registration = $user->registration;
                                $u->cpf = $c_user->c_users[0]->column_1;                                   
                                $u->pis = ''; 
                                $u->rg = '';
                                //$u->nome = '';
                                $u->demitido = 1;   
                            }
                        }else{
                            if($user->registration){
                                if(strlen($user->registration) == 11){
                
                                    //Concatena com 3 zeros para fazer a consula no SOFtran
                                    $cpfUser = '000'.$user->registration;
                    
                                    //Prepara query de consulta
                                    $queryFrete = $this->db->query("SELECT DSNOME,CDFRETEIRO, DSNOME FROM SOFTRAN_MAGNA.GTCFRETE WHERE CDFRETEIRO = '$cpfUser' AND ININATIVO = 0");
                                    
                                    //Verifica se existe um freteiro para este cpf
                                    if($queryFrete->num_rows() > 0){
                    
                                        //Adiciona o nome para o motorista
                                        $u->registration = $user->registration;
                                        $u->cpf = substr($queryFrete->result()[0]->CDFRETEIRO,3);
                                        $u->pis = 'FRETE';
                                        $u->rg = '';
                                        //$u->nome = $queryFrete->result()[0]->DSNOME;
                                        $u->demitido = 0;                                       
                                    }else{
                                        $u->registration = $user->registration;
                                        $u->cpf = '';
                                        $u->pis = '';
                                        $u->rg = '';
                                        //$u->nome = '';
                                        $u->demitido = 1; 
                                    }                                                        
                                }else{
                                    $user->registration = str_replace('-','',str_replace('.','',$user->registration));
                                    //$result = $this->db->query("SELECT NRCPF FROM SOFTRAN_MAGNA.GTCFUNDP where CDFUNCIONARIO = ".$user->registration);                                    
                                    $result = $this->db->query("SELECT CDFUNCIONARIO, NRPIS, NRCPF, NRRG, FGDEMITIDO, CDMATRICULA, DSNOME FROM SOFTRAN_MAGNA.SISFUN WHERE CDMATRICULA = ".$user->registration." AND DSNOME LIKE '%".trim(substr($user->name, strpos ($user->name, '-')+1))."%'");
                                    if($result->num_rows() > 0){
                                        $result = $result->result()[0];
                                        $u->registration = $user->registration;
                                        $u->cpf = $result->NRCPF;
                                        $u->pis = $result->NRPIS;
                                        $u->rg = $result->NRRG;
                                        //$u->nome = $result->DSNOME;
                                        $u->demitido = $result->FGDEMITIDO;
                                    }else{
                                        $result = $this->db->query("SELECT CDFUNCIONARIO, NRPIS, NRCPF, NRRG, FGDEMITIDO, CDMATRICULA, DSNOME FROM SOFTRAN_MAGNA.SISFUN WHERE CDFUNCIONARIO = ".$user->registration." AND DSNOME LIKE '%".trim(substr($user->name, strpos ($user->name, '-')+1))."%'");
                                        if($result->num_rows() > 0){
                                            $result = $result->result()[0];
                                            $u->registration = $user->registration;
                                            $u->cpf = $result->NRCPF;
                                            $u->rg = $result->NRRG;
                                            $u->pis = $result->NRPIS;
                                            //$u->nome = $result->DSNOME;
                                            $u->demitido = $result->FGDEMITIDO;
                                        }else{
                                            $u->registration = $user->registration;
                                            $u->cpf = $c_user->c_users[0]->column_1;
                                            $u->pis = '';
                                            $u->rg = '';
                                            //$u->nome = '';
                                            $u->demitido = 1;
                                        }
                                    }
                                }
                            }else{
                                $u->registration = '';
                                $u->cpf = '';
                                $u->pis = '';
                                $u->rg = '';
                                //$u->nome = '';
                                $u->demitido = 1 ;
                            }                            
                        }
                    }else{
                        if($user->registration){
                            $user->registration = str_replace('-','',str_replace('.','',$user->registration));
                            if(strlen($user->registration) == 11){
                
                                //Concatena com 3 zeros para fazer a consula no SOFtran
                                $cpfUser = '000'.$user->registration;
                
                                //Prepara query de consulta
                                $queryFrete = $this->db->query("SELECT DSNOME,CDFRETEIRO, DSNOME FROM SOFTRAN_MAGNA.GTCFRETE WHERE CDFRETEIRO = '$cpfUser' AND ININATIVO = 0");
                                
                                //Verifica se existe um freteiro para este cpf
                                if($queryFrete->num_rows() > 0){        
                                    //Adiciona o nome para o motorista
                                    $u->registration = $user->registration;
                                    $u->cpf = substr($queryFrete->result()[0]->CDFRETEIRO,3);
                                    $u->pis = 'FRETE';
                                    $u->rg = '';
                                    //$u->nome = $queryFrete->result()[0]->DSNOME;
                                    $u->demitido = 0;                                       
                                }else{
                                    $u->registration = $user->registration;
                                    $u->cpf = '';
                                    $u->pis = '';
                                    $u->rg = '';
                                    //$u->nome = '';
                                    $u->demitido = 1; 
                                }                                                        
                            }else{
                                //$result = $this->db->query("SELECT NRCPF FROM SOFTRAN_MAGNA.GTCFUNDP where CDFUNCIONARIO = ".$user->registration);                                
                                $result = $this->db->query("SELECT CDFUNCIONARIO, NRPIS, NRCPF, NRRG, FGDEMITIDO, CDMATRICULA, DSNOME FROM SOFTRAN_MAGNA.SISFUN WHERE CDMATRICULA = ".$user->registration." AND DSNOME LIKE '%".trim(substr($user->name, strpos ($user->name, '-')+1))."%'");
                                if($result->num_rows() > 0){
                                    $result = $result->result()[0];
                                    $u->registration = $user->registration;
                                    $u->cpf = $result->NRCPF;
                                    $u->pis = $result->NRPIS;
                                    $u->rg = $result->NRRG;
                                    //$u->nome = $result->DSNOME;
                                    $u->demitido = $result->FGDEMITIDO;
                                }else{
                                    $result = $this->db->query("SELECT CDFUNCIONARIO, NRPIS, NRCPF, NRRG, FGDEMITIDO, CDMATRICULA, DSNOME FROM SOFTRAN_MAGNA.SISFUN WHERE CDFUNCIONARIO = ".$user->registration." AND DSNOME LIKE '%".trim(substr($user->name, strpos ($user->name, '-')+1))."%'");
                                    if($result->num_rows() > 0){
                                        $result = $result->result()[0];
                                        $u->registration = $user->registration;
                                        $u->cpf = $result->NRCPF;
                                        $u->pis = $result->NRPIS;
                                        $u->rg = $result->NRRG;
                                        //$u->nome = $result->DSNOME;
                                        $u->demitido = $result->FGDEMITIDO;
                                    }else{
                                        $u->registration = $user->registration;
                                        $u->cpf = '';
                                        $u->pis = '';
                                        $u->rg = '';
                                       // $u->nome = '';
                                        $u->demitido = 1;
                                    }
                                }
                            }                       
                        }else{
                            $u->registration = '';
                            $u->cpf = '';
                            $u->pis = '';
                            $u->rg = '';
                            //$u->nome = '';
                            $u->demitido = 1; 
                        }
                    }
                    $u->template = [$templates->templates[0]->template];
                    $u->id = $user->id;
                    $u->name = $user->name;
                    $u->groups = $groupName;
                    $u->telefone = '';                
                    $u->cargo = '';
                    $u->password = '';
                    $u->cards = '';
                }/*else{
                    $u = new StdClass();
                    $u->registration = '';
                    $u->cpf = '';
                    $u->pis = '';
                    $u->rg = '';
                    //$u->nome = '';                    
                    $u->demitido = ''; 
                    $u->template = [];
                }*/
                
                
               // if(!$u->demitido == 1){
                    array_push($userObj, $u);
                /*}*/
                
            }            
            //$this->createidSecure($userObj);
            echo json_encode($userObj);
        }else{
            echo json_encode('Erro ao autenticar');
        }
    }

    function createRep(){
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        $retorno = [];
        if($equipamento){            
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
             //Carrega a instancia da classe do equipamento
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){                
                $retorno = $this->idsecure->getUsers();
                //$retorno["retorno"]="success";           
            }else{
                $retorno["msg"]="Não foi possivel logar no equipamento";
                $retorno["retorno"]="error";   
            }           
        }else {
            $retorno["msg"]="Não encontrado equipamento cadastrado";
            $retorno["retorno"]="error";   
        }
        echo json_encode($retorno);
    }

    function createidSecure($users){
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        $teste = 0;        
        $dataUsers = [];
        foreach ($users as $key => $value) {
            $grupos = [];
            foreach($value->groups as $gp){
                if($equipamento){            
                    $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
                     //Carrega a instancia da classe do equipamento
                    $this->load->library('IdSecure',$constructIdSecure);
                    if($this->idsecure->authenticate()){
                        if($group = $this->idsecure->getGroupID($gp)){
                            array_push($grupos, $group->id);
                        }                
                    }
                }                                    
            }
            $data = new StdClass(); 
            $data->inativo = false ;
            $data->blackList = false;
            $data->contingency = false;
            $data->cards = [] ;
//            $data->groups = $value->group;
            $data->groups = [];
            $data->groupsList = [];
            if($value->demitido == 1){
                if($grupoInfo = $this->idsecure->getGroups(1036)){
                    array_push($data->groups,intval($g));
                    array_push($data->groupsList,$grupoInfo);
                }
            }else{
                foreach ($grupos as $key => $g) {
                    if($grupoInfo = $this->idsecure->getGroups(intval($g))){
                        array_push($data->groups,intval($g));
                        array_push($data->groupsList,$grupoInfo);
                    }                    
                }  
            }           
            $data->shelfLifeDate = "";
            $data->shelfEndLifeDate = "";
            $data->name = $value->name;
            if($value->pis != ""){
                $value->pis = str_replace('-','',str_replace('.','',$value->pis));                
                $data->id = $value->pis * - 1;  
                $data->pis = $value->pis * 1;               
            }
            else{
                $data->id = $value->id * - 1;                
                $data->pis = 0;
            }  
            if($value->rg != ""){
                $data->rg = $value->rg;                 
            }                                
            $data->registration = $value->registration;
            if($value->cpf != ""){
                $data->cpf = $value->cpf;                
            }            
            $data->templates = $value->template;
            $data->templatesImages = [null] ;           
            $data->shelfLife = null;
            $data->shelfEndLife = null;
            $data->foto = null;
            $data->fotoDoc = null; 
            array_push($dataUsers, $data);
            //array_push($dataUsers, array($data->id => $this->idsecure->addUserImport($data)));
            //sleep(3);
        }
        echo json_encode($dataUsers);
    }

    /**
     * Cria um csv com os dados e uma digital do usuario
     */
    function csvIdBox(){    
        //Adiciona os parametros do IdBox
        //$this->createidSecure(null);
        //$this->createRep();
       /* $constructParams = ["ip" => '192.168.99.250', "user" => 'admin', "password" => 'mgn1409',"protocol"=>'http'];
        
        //Carrega a instancia da classe do equipamento
        $this->load->library('IdClass',$constructParams);

        $userObj = []; 

        //Autentica com o equipamento
        if($this->idclass->authenticate()){   
            $json = '{  
                        "join":"LEFT",
                        "object":"users",
                        "fields":[  
                        ],
                        "where":[  
                        ],
                        "order":[  

                        ]
                    }';
            $data = json_decode($json);
            $users = json_decode($this->idclass->callRequest('load_objects',$data,true));
            foreach($users->users as $user){
                $jsonUser = '{  
                                "join":"LEFT",
                                "object":"c_users",
                                "fields":[  
                                    "user_id",
                                    "column_1"
                                ],
                                "where":[  
                                {  
                                    "object":"users",
                                    "field":"id",
                                    "value":'.$user->id.',
                                    "connector":") AND ("
                                }
                                ],
                                    "order":[  
                                    "id"
                                ],
                                "limit":1,
                                "offset":0
                            }';
                $dataUser = json_decode($jsonUser); 
                $c_user = json_decode($this->idclass->callRequest('load_objects',$dataUser,true)); 
                $jsonTemplate = '{  
                                    "join":"LEFT",
                                    "object":"templates",
                                    "fields":[                                  
                                    ],
                                    "where":[ 
                                    {  
                                        "object":"templates",
                                        "field":"user_id",
                                        "value":'.$user->id.',
                                        "connector":") AND ("
                                    }
                                    ],
                                    "order":[                              
                                    ]
                                }';
                $dataTemplate = json_decode($jsonTemplate);
                $templates = json_decode($this->idclass->callRequest('load_objects',$dataTemplate,true));
                if($templates->templates){                    
                    $u = new StdClass();
                    if($c_user->c_users){                       
                        if($c_user->c_users[0]->column_1){
                            $u->registration = $user->registration;
                            $u->cpf = $c_user->c_users[0]->column_1;                            
                        }else{ 
                            if($user->registration){
                                $result = $this->db->query("SELECT NRCPF FROM SOFTRAN_MAGNA.GTCFUNDP where CDFUNCIONARIO = ".$user->registration);
                                if($result->num_rows() > 0){
                                    $u->cpf = substr($result->result()[0]->NRCPF,3);                                   
                                }else{
                                    $u->cpf = '';   
                                }
                            }else{
                                $u->cpf = '';
                            }
                            $u->registration = $user->registration;                                                               
                        }
                    }else{
                        if($user->registration){
                            $result = $this->db->query("SELECT NRCPF FROM SOFTRAN_MAGNA.GTCFUNDP where CDFUNCIONARIO = ".$user->registration);
                            if($result->num_rows() > 0){
                                $u->cpf = substr($result->result()[0]->NRCPF,3);                                   
                            }else{
                                $u->cpf = '';   
                            }
                        }else{
                            $u->cpf = '';
                        }
                        $u->registration = $user->registration;                                                
                    }

                    $u->id = $user->id;
                    $u->name = $user->name;
                    $u->telefone = '';
                    $u->cargo = '';
                    $u->password = '';
                    $u->cards = '';
                    $u->template = $templates->templates[0]->template;
                    array_push($userObj, $u);
                } 
            }     
            $this->outputCSV($userObj);                        
        }else{
            echo json_encode('Erro ao autenticar');
        }*/
    }

    function outputCSV($data,$file_name = 'file.csv', $download = false) {
        if(!$download){
            if(!is_dir('exports')){
                mkdir('exports', 0777, true);
            }  
            $output = fopen('exports/'.$file_name, "w");

            $titulos = array('id','registration','name','cpf','telefone','cargo','password','cards','templates');
            fputcsv($output, $titulos);
            foreach ($data as $user) {
                $linhas = array($user->id,$user->registration,$user->name,$user->cpf,'','','','',$user->template.'|0');
                fputcsv($output, $linhas);
            }       
            fclose($output);
        }else{
            header("Cache-Control: no-cache, no-store, must-revalidate"); 
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=usuarios.csv');
            //header('Content-Length: ' . filesize($zipname));
            readfile('exports/'.$file_name); 
        }        
     }

    function integrarIdSecure(){
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        $retorno = [];
        if($equipamento){            
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
             //Carrega a instancia da classe do equipamento
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){                
                $this->idsecure->integrationUserIdSecure();
                $retorno["retorno"]="success";           
            }else{
                $retorno["msg"]="Não foi possivel logar no equipamento";
                $retorno["retorno"]="error";   
            }           
        }else {
            $retorno["msg"]="Não encontrado equipamento cadastrado";
            $retorno["retorno"]="error";   
        }
        echo json_encode($retorno);
    }

    function integrar_idbox($user = null){   
        //Adiciona os parametros do IdBox
        $constructParams = ["ip" => '192.168.99.250', "user" => 'admin', "password" => 'mgn1409',"protocol"=>'http'];
        
        //Carrega a instancia da classe do equipamento
        $this->load->library('IdClass',$constructParams);

        //Autentica com o equipamento
        if($this->idclass->authenticate()){   
            if($user == null){
                $this->idclass->integrationUserIdBox();
            }else{
                $data = $this->idclass->filterUser($user);
                echo json_encode($data); 
            }            
        }
    }

    function reiniciarPortasSp(){
        
        /**
        * Função Aninhada que permite realiza a requisição para os aparelhos - (temporário)
        **/
        function execCurl($url,$json = ""){

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json))
            );

            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result);
        }

        static $urlSac = "http://192.168.2.54";
        static $urlExp = "http://192.168.2.55"; 
        static $loginJson = '{"login":"admin","password":"mgn1409"}'; 
        $error = false;

        $retornoSac = execCurl($urlSac."/login.fcgi",$loginJson);

        if($retornoSac->session){
            $sacParam = $urlSac."/reboot.fcgi?session=".$retornoSac->session;
            execCurl($sacParam);
        }else{
            $error = true;
        }

        $retornoExp = execCurl($urlExp."/login.fcgi",$loginJson);
        if($retornoExp->session){
            $expParam = $urlExp."/reboot.fcgi?session=".$retornoExp->session;
            execCurl($expParam);
        }else{
            $error = true;
        }

        if($error){
            $this->dados['erro'] = 'Erro ao reniciar os aparelhos!';
        }else{
            $this->dados['sucesso'] = 'Aparelhos reiniciados com sucesso!';
        }
        
        $this->render('listar_equipamento');
    }    

    function updateEquipmentStatus($id){        
        $this->biometria_equipamento_model->update(["INTEGRADO" => 1],$id);
    }


    function retornaUserIntegation($id){
        $usersArr = $this->biometria_usuario_model->get_all();
        $arrClearInfo = ["userData" => [],"equipData" => $this->biometria_equipamento_model->get($id)];

        foreach($usersArr as $user){
            
            $userClear = new StdClass();
            $userClear->admin = false;
            $userClear->bars = '';
            $userClear->code = 0;
            $userClear->name = $this->db->query("SELECT DSNOME FROM SOFTRAN_MAGNA.GTCFUNDP WHERE NRCPF = '$user->CPF' ")->result()[0]->DSNOME;
            $userClear->password = '';
            $userClear->pis = $user->CPF;
            $userClear->rfid = 0;
            $userClear->templates = [];

            $userTemplate =  $this->db->query("SELECT * FROM BIOMETRIA_USUARIO_DIGITAL WHERE USUARIOCPF = '$user->CPF' ")->result();

            if(!empty($userTemplate)){
                foreach($userTemplate as $template){
                    $digitalConcat = $template->TEMPLATE1;
                    $digitalConcat .=  isset($template->TEMPLATE2) ? $template->TEMPLATE2 : '' ;
                    $digitalConcat .=  isset($template->TEMPLATE3) ? $template->TEMPLATE3 : '' ;
                    array_push($userClear->templates,$digitalConcat);
                }
            }

            array_push($arrClearInfo["userData"],$userClear);
            
        }

        echo json_encode($arrClearInfo);
    }

    function addBiometria($post){
        
        if($post['usuario']['fingers']){           
            foreach($post['usuario']['fingers'] as $digital){   
               $dbParam = $this->dividirTemplate($digital);
               $dbParam['USUARIOCPF'] = $post['usuario']['cpf'];
               $this->biometria_usuario_digital_model->insert($dbParam);               
            }
        }

    }

    function addLog($post){
        if(isset($post['objInfo'])){

            if(isset($post['objInfo']['ok'])){    
                foreach ($post['objInfo']['ok'] as $obj){
                    $this->biometria_usu_equip_model->insert($obj);        
                }
            }

            if(isset($post['objInfo']['fila'])){
                foreach ($post['objInfo']['fila'] as $obj ){
                  $this->biometria_fila_model->insert($obj);  
                }
            }

            if(isset($post['objInfo']['erro'])){
                foreach($post['objInfo']['erro'] as $obj){
                    $this->biometria_erro_model->insert($obj);
                }
            
            }

        }
    }

    function removerUsuario(){   
        $post = $this->input->post(); 

        //Começa uma transação
        $this->db->trans_begin();

        //Deleta todos dados do usuário que estiverem na fila
        $where = ['USUARIOCPF' => $post['CPF'], 'OPERACAO' => 'CADASTRO'];
        $this->biometria_fila_model->delete($where);
        
        //Adiciona info no LOG
        $this->addLog($post);

        //Deleta as cardinalidades do usuário
        $this->biometria_usu_equip_model->delete(["USUARIOCPF" => $post['CPF']]);
        $this->biometria_usuario_digital_model->delete(["USUARIOCPF" => $post['CPF']]);
        $this->biometria_usuario_model->delete(["CPF" => $post['CPF']]);

        //Finaliza a transção  
        $this->db->trans_complete();
           
        //Workaround por causa da validação feito no Front
        $statusOp = $this->db->trans_status() ? 'ok' : 'error';
       
        echo json_encode(["status" => $statusOp]);
                
    }

    function editar_usuario($id){
        $this->dados['titulo'] = 'Editar';
        $this->render('formulario_usuario');  
    }
    
    function retornaIdClass(){
        echo json_encode($this->biometria_equipamento_model->get_all(array('TIPO'=>'IDCLASS')));
    }

    function retornaIdSecure(){
        echo json_encode($this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE')));
    }

    function retornaIdAcess(){
        echo json_encode($this->biometria_equipamento_model->get_all(array('TIPO'=>'IDACCESS')));
    }

    ////////////////////////////////
    // Funções de interação com o formulário
    ////////////////////////////////

    public function valida_usuarioCpf(){


        if($_POST['CPF']){

            $cpfLabel = $_POST['CPF']; 
            $this->load->library('SoftranUsuario');
            $responseObj = new StdClass();
            
            //******* concatena com três zeros já que no SOFtran o campo de join da gtcfundp preenche com zero a esquerda *******
            $cpf  =  '000'.str_replace('-','',str_replace('.','',$_POST['CPF']));
            
            //Verifica se o usuário consta no sistema
            if(!empty($consultaRet = $this->softranusuario->getFuncionarioFreteiro($cpf))){    
                if(!$this->biometria_usuario_digital_model->get(["USUARIOCPF" => $cpf])){
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

            echo json_encode($responseObj);

        }

    }

    public function retornaUsuario(){   
        $data = $this->input->post();
        $ID = $data['ID'];

        if($user = $this->biometria_usuario_model->get(["ID" => $ID])){            
            //Pega o usuário
            $user->DSNOME =  $this->db->query("SELECT DSNOME FROM SOFTRAN_MAGNA.GTCFUNDP WHERE NRCPF = '$user->CPF' ")->result()[0]->DSNOME;
            $user->QTDIG =  $this->db->query("SELECT COUNT(*) AS QT FROM BIOMETRIA_USUARIO_DIGITAL WHERE USUARIOCPF  = '$user->CPF' ")->result()[0]->QT;

            echo json_encode($user);    
        }else{
            echo json_encode([]);
        }
        
    }


    /**
    * Este método retorna a separação de um template, ou seja, é passado a string e é retornada duas strings 
    * é utilizada para salvar no banco, já que o Oracle suporte no máximo 4000 caracteres em um varchar ou varchar2 
    **/
    public function dividirTemplate($digital){   

        $usuario_template = [];
        
        $usuario_template['TEMPLATE1'] = substr($digital, 0, 4000);
                        
        if (strlen($digital) > 4000){
            $usuario_template['TEMPLATE2'] = substr($digital, 4000, 8000);
        }
                        
        if (strlen($digital) > 8000){
            $usuario_template['TEMPLATE3'] = substr($digital, 8000);
        }

        return $usuario_template;
        
    }

    ////////////////////////////////
    // Equipamento
    ////////////////////////////////

    public function retornaEquipamento(){        
        
        //$this->usuario_model->where("ID",$this->sessao['usuario_id']);
        
        //$arrWhere = ["CDEMPRESA" => $this->usuario_model->get()->CDEMPRESA,"IP"=>"192.168.98.13"];
        
        $equipamento = $this->biometria_equipamento_model->get(array('TIPO'=>'IDCLASS','SINCRONIZAR' => 1));

        echo json_encode($equipamento);

    }

    public function processar_fila(){
        $arrFila = $this->biometria_fila_model->get_all();
        
        $objetoFila = [];

        if(!empty($arrFila)){   
            foreach($arrFila as $objFila){  
                $rsQuery = $this->db->query("SELECT * FROM SOFTRAN_MAGNA.GTCFUNDP WHERE NRCPF = '$objFila->USUARIOCPF' ");

                if($rsQuery->num_rows()){
                    //Carrega as informações do equipamento
                    $equipInfo = $this->biometria_equipamento_model->get($objFila->EQUIPAMENTOID);     

                    //Cria a instancia da classe e Seta os dados para integração ao REP
                    $ControlId = new \StdClass();
                    $ControlId->IP = $equipInfo->IP;
                    $ControlId->PROTOCOLO = $equipInfo->PROTOCOLO;
                    $ControlId->TIPO = $equipInfo->TIPO;
                    $ControlId->LOGIN = $equipInfo->USUARIO;
                    $ControlId->SENHA = $equipInfo->SENHA;
                    $ControlId->NOME = $rsQuery->result()[0]->DSNOME;
                    $ControlId->USUARIOCPF = $objFila->USUARIOCPF;
                    $ControlId->OPERACAO = $objFila->OPERACAO;
                    $ControlId->IDEQUIP = $equipInfo->ID;
                    $ControlId->IDFILA =  $objFila->ID;

                    //Se a operação for cadastrado "carrega" as digitais
                    if($objFila->OPERACAO == 'CADASTRO'){
                        $arrDigUser = $this->biometria_usuario_digital_model->get_all(['USUARIOCPF' => $objFila->USUARIOCPF]);
                        $arrConcat = [];

                        if(!empty($arrDigUser)){
                            foreach($arrDigUser as $digital){   
                                $digconcat = "";

                                if($digital->TEMPLATE1 && $digital->TEMPLATE2 && $digital->TEMPLATE3){
                                    $digconcat = $digital->TEMPLATE1.$digital->TEMPLATE2.$digital->TEMPLATE3;
                                }else if($digital->TEMPLATE1 && $digital->TEMPLATE2){
                                    $digconcat = $digital->TEMPLATE1.$digital->TEMPLATE2;
                                }else if($digital->TEMPLATE1){
                                    $digconcat = $digital->TEMPLATE1;
                                }    

                                array_push($arrConcat,$digconcat);
                            }                    
                        }

                        $ControlId->DIGITAIS = $arrConcat;
                    }

                    array_push($objetoFila,$ControlId);

                }else{
                    //Remove da Fila já que o usuário não consta mais na GTCFUNDP
                    $this->biometria_fila_model->delete(["USUARIOCPF" => $objFila->USUARIOCPF]);
                }       
             
            }
        }

        echo json_encode($objetoFila);
    }

    public function verificar_cpf($idUser, $cpf){
    
        $cpfConcat = '000'.$cpf; // concatena com 3 zeros pois é o formato no banco

        $retorno = new StdClass();
        $retorno->ok = true;

        //Verifica se já existe esse usuário cadastrado,
        if($this->biometria_usuario_model->count_rows(['CPF' => $cpfConcat])){
            $retorno->ok = false;
            $retorno->msg = "Ja existe um usuario cadastrado com este CPF, exclua primeiro";
        }

        if(!$this->db->query("SELECT * FROM SOFTRAN_MAGNA.GTCFUNDP WHERE NRCPF = '$cpfConcat' ")->num_rows()){
            $retorno->ok = false;
            $retorno->msg = "CPF nao encontrado, Certifique-se que este CPF consta no Softran";    
        }

        if($this->biometria_fila_model->count_rows(['USUARIOCPF' => $cpfConcat])){
            $retorno->ok = false;
            $retorno->msg = "Ja existe um usuário na fila com essas digitais";                
        }

        //Caso esteja tudo ok, adiciona o usuário
        if($retorno->ok){  

            //Adiciona os parametros do IdBox
            $constructParams = ["ip" => '192.168.99.250', "user" => 'admin', "password" => 'mgn1409',"protocol"=>'http'];
        
            //Carrega a instancia da classe do equipamento
            $this->load->library('IdClass',$constructParams);

            if($this->idclass->authenticate()){   
                $this->idclass->addNewUser($cpfConcat,$idUser);
            }else{
                $retorno->ok = false;
                $retorno->msg = "Problema ao tentar logar no IDBOX";   
            }             
        }
        echo json_encode($retorno);
    }

    public function salvarOperacaoBd(){   
         $data = $this->input->post();
         
         if(isset($data['ok'])){
            foreach($data['ok'] as $obj){
                $this->biometria_fila_model->delete($obj['filaID']);

                if($obj['OP'] == "CADASTRO"){
                    $this->biometria_usu_equip_model->insert(["EQUIPAMENTOID" =>  $obj['EQUIPAMENTOID'] ,"USUARIOCPF" =>  $obj['USUARIOCPF'] ]);
                }

            }
         }

         if(isset($data['erro'])){
            foreach($data['erro'] as $obj){
                $this->biometria_erro_model->insert($obj);
            }
         }

         //Workaround
         echo json_encode(["data" => "ok"]);
    }

    public function retornaEquipamentoUsuario(){

        $data = $this->input->post();
        $cpf = $data['CPF'];

        $this->biometria_usu_equip_model->where("USUARIOCPF",$cpf);

        if($rs = $this->biometria_usu_equip_model->get_all()){
    
            foreach($rs as $equip){  
              $result = $this->db->query("SELECT * FROM BIOMETRIA_EQUIPAMENTO WHERE ID = $equip->EQUIPAMENTOID")->result()[0]; 
              $equip->ID = $equip->EQUIPAMENTOID;   
              $equip->IP = $result->IP;
              $equip->USUARIO = $result->USUARIO;
              $equip->SENHA = $result->SENHA;
            }

            echo json_encode($rs);
        }else{
            echo json_encode([]);
        }
    }

    /*
        -- Atenção---
        Este método busca o equipamento padrão da filial joinville,
        caso o equipamento da filial que estiver cadastrando a biometria estiver fora.   
    */
    public function retornaEquipamentoPadraoFilial(){

        $arrWhere = ["CDEMPRESA" => 6,"PADRAO"=>"1", "TIPO"=>"IDCLASS"];
        
        $equipamento = $this->biometria_equipamento_model->get($arrWhere);
 
        echo json_encode($equipamento);
    }

    /**
     * listar_equipamento
     * Tela para listar todos os iDClass e iDAccess
     * @param int $page
     */
    function listar_equipamento ($page = 1){
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
    function _formulario_equipamento ($id = null){
        $this->form_validation->set_rules('NOME', 'Nome', 'trim|required');
        $this->form_validation->set_rules('CDEMPRESA', 'Filial', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('IP', 'IP', 'trim|required|valid_ip');
        $this->form_validation->set_rules('USUARIO', 'Usuário', 'trim|required');
        $this->form_validation->set_rules('SENHA', 'Senha', 'trim|required');
        $this->form_validation->set_rules('PORTA', 'Porta', 'trim');
        if ($this->form_validation->run()){
            $post = $this->input->post();
            $post['PROTOCOLO'] = ($post['TIPO'] == 'IDACCESS') ? 'http' : 'https';
            if ($id){                
                if(!isset($post['PADRAO'])) $post['PADRAO'] = 0;                 
                if(!isset($post['SINCRONIZAR'])) $post['SINCRONIZAR'] = 0; 
                $this->biometria_equipamento_model->update($post, $id);
            }
            else{
                $id = $this->biometria_equipamento_model->insert($post);
            }
            if ($id){
               $this->redirect('ti_biometria/listar_equipamento', 'sucesso', 'Equipamento gravado com sucesso');
            }
        }
    }

    /**
     * adicionar_equipamento
     * Tela para adicionar um novo equipamento
     */
    function adicionar_equipamento (){
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
    function editar_equipamento ($id){
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
    function excluir_equipamento ($id){
        $confirmacao = $this->input->post('confirmacao');
        if($confirmacao){
            $delete = $this->biometria_equipamento_model->delete($id);
            if($delete){
                $this->redirect('ti_biometria/listar_equipamento', 'sucesso', 'Equipamento excluído com sucesso');
            }
            else{
                $this->dados['erro'] = 'Erro ao gravar';
            }
        }
        $this->render('_generico/excluir');
    }

}
