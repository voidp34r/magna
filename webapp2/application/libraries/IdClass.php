<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class IdClass
{

    private $id;
    private $ip;
    private $user;
    private $password;
    private $session;
    private $CI;
    private $protocol;
    
    public function __construct($arrConstruct)
    {
        $this->CI = &get_instance();        

        //Carrega o model de equipamentos
        $this->CI->load->model('ti_biometria/biometria_registro_model');

        $this->id = isset($arrConstruct['id']) ? $arrConstruct['id'] : null;
        $this->protocol = isset($arrConstruct['protocol']) ? $arrConstruct['protocol'] : 'https';
        $this->ip = $arrConstruct['ip'];
        $this->user = $arrConstruct['user'];
        $this->password = $arrConstruct['password'];
    }

    public function setSession($session){
        $this->session = $session;
    }

    public function getSession(){
        return $this->session;
    }


    public function authenticate()
    {   

        $isOk = true;

        //Objeto de Usuário : {login : "admin" , password : "admin"}
        $userLogin = new \StdClass();
        $userLogin->login = $this->user;
        $userLogin->password = $this->password;

        if($result = $this->callRequest('login',$userLogin))
        {
            $returnObj = json_decode($result);

            $returnObj->session ? $this->setSession($returnObj->session)  
                                : $isOk = false; 
                
        }else{
            $isOk = false;
        }

        return $isOk;
    }

    public function getAccessLogs($day = 20,$month = 03, $year = 2018){
        $timestamp = strtotime($day.'-'.$month.'-'.$year.' 00:00:00');
        //Template do json para pegar os logs de acesso
        $json = '{  
                    "join":"LEFT",
                    "object":"access_logs",
                    "fields":[  
                        "id",
                        "user_id",
                        "time"
                    ],
                    "where":[  
                    {  
                        "field":"event",
                        "value":7,
                        "operator":"=",
                        "connector":") AND ("
                    },
                    {  
                        "field":"time",
                        "value":'.$timestamp.',
                        "operator":">",
                        "connector":") AND ("
                    }
                    ],
                    "order":[  
                        "time",
                        "ascending"
                    ]
                }';
        $data = json_decode($json);
        if($result = $this->callRequest('load_objects',$data,$this->getSession()))
        {
            //Transfoma em array
            $arrDados = json_decode($result)->access_logs;
           

            $arrBatidas = []; 
            foreach($arrDados as $dataLine){
                date_default_timezone_set('UTC');
                $date = date('d/m/Y H:i:s', $dataLine->time);
                //Template do json para pegar o cpf do usuário
                $jsonUser = '{  
                            "join":"LEFT",
                            "object":"c_users",
                            "fields":[  
                                "user_id",
                                "cpf"
                            ],
                            "where":[  
                            {  
                                "object":"users",
                                "field":"id",
                                "value":'.$dataLine->user_id.',
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
                if($resultUser = $this->callRequest('load_objects',$dataUser,$this->getSession())){
                     //Transfoma em array    
                    $arrUser = json_decode($resultUser)->c_users;  
                                                            
                    $batidaObj = new \StdClass();
                    $batidaObj->DTBATIDA = substr($date,0,2).'/'.substr($date,3,2).'/'.substr($date,6,4);
                    $batidaObj->HRBATIDA = substr($date,11,2).':'.substr($date,14,2).':'.substr($date,17,2);
                    if(strlen($arrUser[0]->cpf) > 11){
                        $batidaObj->CPF = substr($arrUser[0]->cpf,3);
                    }else{
                        $batidaObj->CPF = $arrUser[0]->cpf;
                    }
                    
                    array_push($arrBatidas,$batidaObj);
                }                            
            }
            
        }
        return $arrBatidas;
        //print_r($arrBatidas);
    }

    public function getAFD($day = 11,$month = 8 ,$year = 2017)
    {   
        //Cria o objeto com a data conforme requisitado pelo método
        $dateObj = new \StdClass();
        $dateObj->day = $day;
        $dateObj->month = $month;
        $dateObj->year = $year;

        //Cria objeto de requisição
        $sendObj = new \StdClass();
        $sendObj->session = $this->getSession();
        $sendObj->initial_date = $dateObj;

        if($result = $this->callRequest('get_afd',$sendObj))
        {   
            //Da o Explode e transofrma os dados em array
            $arrDados = explode(PHP_EOL,$result);

            $arrBatidas = [];      
            
            foreach($arrDados as $dataLine)
            {

                //Recebe o tipo da ação
                $tpAcao = intval(substr($dataLine,9,1));
                
                //Tipo 3 é igual batida de ponto
                if($tpAcao == 3)
                {   
                    //Cria a classe com atributos necessários para consulta no banco de dados
                    $batidaObj = new \StdClass();
                    $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                    $batidaObj->HRBATIDA = substr($dataLine,18,2).':'.substr($dataLine,20,2);
                    $batidaObj->CPF = substr($dataLine,23,11);
                    array_push($arrBatidas,$batidaObj);
                }
            } 

        }

        return $arrBatidas;

    }

    public function getAFDRefeicao($day = 11,$month = 8 ,$year = 2017, $tipo = 0)
    {   
        //Cria o objeto com a data conforme requisitado pelo método
        $dateObj = new \StdClass();
        $dateObj->day = $day;
        $dateObj->month = $month;
        $dateObj->year = $year;

        //Cria objeto de requisição
        $sendObj = new \StdClass();
        $sendObj->session = $this->getSession();
        $sendObj->initial_date = $dateObj;

        if($result = $this->callRequest('get_afd',$sendObj))
        {   
            //Da o Explode e transofrma os dados em array
            $arrDados = explode(PHP_EOL,$result);

            $arrBatidas = [];      
            
            foreach($arrDados as $dataLine){

                //Recebe o tipo da ação
                $tpAcao = intval(substr($dataLine,9,1));
                
                //Tipo 3 é igual batida de ponto
                if($tpAcao == 3 && $day == substr($dataLine,10,2) && $month == substr($dataLine,12,2) ){  
                    //Cria a classe com atributos necessários para consulta no banco de dados]
                    $hora = substr($dataLine,18,2).':'.substr($dataLine,20,2);
                    switch ($tipo) {
                        case 1:
                            //12h
                            if($hora >= '04:45' && $hora <= '08:33'){
                                $batidaObj = new \StdClass();
                                $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                                $batidaObj->HRBATIDA = $hora;
                                $batidaObj->PIS = substr($dataLine,22,12);
                                array_push($arrBatidas,$batidaObj);
                            }
                            break;   
                        case 2:
                            //19h
                            if($hora >= '11:45' && $hora <= '13:45'){
                                $batidaObj = new \StdClass();
                                $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                                $batidaObj->HRBATIDA = $hora;
                                $batidaObj->PIS = substr($dataLine,22,12);
                                array_push($arrBatidas,$batidaObj);
                            }
                            break; 
                        case 3:
                            //22h
                            if($hora >= '17:30' && $hora <= '20:35'){
                                $batidaObj = new \StdClass();
                                $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                                $batidaObj->HRBATIDA = $hora;
                                $batidaObj->PIS = substr($dataLine,22,12);
                                array_push($arrBatidas,$batidaObj);
                            }
                            break;
                        case 4:
                            //00h
                            if($hora >= '21:30' && $hora <= '23:20'){
                                $batidaObj = new \StdClass();
                                $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                                $batidaObj->HRBATIDA = $hora;
                                $batidaObj->PIS = substr($dataLine,22,12);
                                array_push($arrBatidas,$batidaObj);
                            }
                            break; 
                        default:
                                $batidaObj = new \StdClass();
                                $batidaObj->DTBATIDA = substr($dataLine,10,2).'/'.substr($dataLine,12,2).'/'.substr($dataLine,14,4);
                                $batidaObj->HRBATIDA = $hora;
                                $batidaObj->PIS = substr($dataLine,22,12);
                                array_push($arrBatidas,$batidaObj);
                            break;                      
                    }                                
                }
            } 
        }

        return $arrBatidas;

    }

    public function callRequest($method,$postData,$useSession = false){
        $ch = curl_init();

        //Transforma em Json o objeto
        $jsonString = json_encode($postData);   

        $addUrl = $useSession ? "?session=".$this->getSession() : '';
        //Monta a Url de Requisição
        $url = $this->protocol.'://'.$this->ip.'/'.$method.'.fcgi'.$addUrl;

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,1); 
        curl_setopt($ch,CURLOPT_HEADER,false);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonString); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("content-type: application/json"));

        //Executa a requisição
        $postResult = curl_exec($ch);

        $error = curl_error ($ch);

        //Fecha a conexão
        curl_close($ch); 

        return $postResult;
    }

    public function atualizaHoraIdAccess(){
        date_default_timezone_set("Brazil/East");
        $data = date('d/m/Y H:i:s');
        $d = substr($data, 0, 2);   
        $m = substr($data, 3, 2);  
        $a = substr($data, 6, 4); 
        $h = substr($data, 11, 2); 
        $mm = substr($data, 14, 2); 
        $s = substr($data, 17, 2); 

        $jsonData = new StdClass();        
        $jsonData->day = intval($d);
        $jsonData->month = intval($m);
        $jsonData->year = intval($a);
        $jsonData->hour = intval($h);
        $jsonData->minute = intval($mm);
        $jsonData->second = intval($s);

        $result = $this->callRequest('set_system_time',$jsonData,$this->getSession());
    }


    public function atualizarPontosDb($tipo){
       //Verifica se tem registro de batida no banco para este equipamento
        $arrRegistros = $this->CI->biometria_registro_model->get_all(["EQUIPAMENTOID" => $this->id]);     
       
        //Pega as batidas do equipamento, apartir da data padrão que é 24/07/2017
        if($tipo){
            $batidas = $this->getAccessLogs();
            $this->atualizaHoraIdAccess();
        }else{
            $batidas = $this->getAFD();
        }

       //Verifica se já teve registro para este ponto
        if(empty($arrRegistros)){
            //Pega todas as batidas e insere no banco
            foreach($batidas as $batida){
                //Cria objeto de insert
                $insertObj = $this->buildInsertObj($this->id,$batida->CPF,$batida->DTBATIDA,$batida->HRBATIDA);                 
                $this->CI->biometria_registro_model->insert($insertObj);
            }
        }else{                
            //Seleciona o ultimo registro de batida do ponto salvo no banco
            $dtRegistroDb = $this->CI->db->query("SELECT MAX(DTREGISTRO) AS DTULTIMO FROM BIOMETRIA_REGISTRO")->result()[0]->DTULTIMO;           
            $dtRegistroDb = data_oracle_para_web($dtRegistroDb);
            $dtRegistroDb = DateTime::createFromFormat('d/m/Y H:i', $dtRegistroDb);
          
            //Pega o ultimo registro cadastrado no ponto
            $ultimoRegistro = $batidas[count($batidas) - 1];
            if($tipo){
                $dtRegistroPonto  = $ultimoRegistro->DTBATIDA.' '.substr($ultimoRegistro->HRBATIDA,0,5);
            }else{
                $dtRegistroPonto  = $ultimoRegistro->DTBATIDA.' '.$ultimoRegistro->HRBATIDA;
            }               
            $dtRegistroPonto = DateTime::createFromFormat('d/m/Y H:i',$dtRegistroPonto);      

            //Verifica se o ultimo registro do ponto é maior que o ultimo salvado no banco
            if($dtRegistroPonto > $dtRegistroDb){   
                $chaveComeco = 0;

                //Aqui verifica apartir de qual registro é para ser inserido   
                foreach($batidas as $chave => $valor){
                    if($tipo){
                        $dtRegistro  = $valor->DTBATIDA.' '.substr($valor->HRBATIDA,0,5);
                    }else{
                        $dtRegistro = $valor->DTBATIDA.' '.$valor->HRBATIDA;
                    }                   
                    $dtRegistro = DateTime::createFromFormat('d/m/Y H:i',$dtRegistro);   

                    //Verifica se é o ultimo registro inserido no banco
                    if($dtRegistro == $dtRegistroDb)
                    {   
                        //Pega o indice e incrementa um, pois é apartir do proximo registro que é pra ser inserido
                        $chaveComeco = $chave  + 1 ;
                    }
                }

                //Faz um "for" apartir do registro que é pra ser inserido, e adiciona para um array de objeto para inserir no banco
                for($i = $chaveComeco; $i < count($batidas) ; $i++){   
                    //Carrega o objeto de insert no banco
                    $insertObj = $this->buildInsertObj($this->id,$batidas[$i]->CPF,$batidas[$i]->DTBATIDA,$batidas[$i]->HRBATIDA);
                    
                    //Insere no banco
                    $this->CI->biometria_registro_model->insert($insertObj);    
                }
                
            }
                        
        }
    }


    private function buildInsertObj($equipamentoId,$cpfUsuario,$dtBatida,$hrBatida){
        $insertObj = new StdClass();
        
        $insertObj->EQUIPAMENTOID = $equipamentoId;
        //WORKAROUND PARA JOIN COM A SISFUN DA SOFTRAN
        $insertObj->USUARIOCPF = '000'.$cpfUsuario;
        $insertObj->DTREGISTRO = data_web_para_oracle($dtBatida.' '.$hrBatida);

        return $insertObj;
    } 
        
    public function filterUser($userName){

        $tableUsers = new StdClass();
        $tableUsers->join = "LEFT";
        $tableUsers->object = "users";
        $tableUsers->fields = ["id","registration","name"];
        $where = new StdClass();
        $where->object = "users";
        $where->field = "name";
        $userName = str_replace('20', '', $userName);
        $userName = str_replace('%', ' ', $userName);
        $where->value = "%$userName%";
        $where->connector = ") AND (";
        $tableUsers->where = [$where];
        
        if($result = $this->callRequest('load_objects',$tableUsers,true)){
            $jsonObj = json_decode($result);
            return $jsonObj;
        }
    }
    
    public function integrationUserIdBox(){
        $tableUsers = new StdClass();
        $tableUsers->join = "LEFT";
        $tableUsers->object = "users";
        $tableUsers->fields = ["id","registration","name"];
        $motoristas = [];
    
        if($result = $this->callRequest('load_objects',$tableUsers,true)){

            $jsonObj = json_decode($result);

            foreach($jsonObj->users as $user){

                if($user = $this->returnValidateUser($user)){
                    
                    $index = false;
                    
                    //Percorre o array para ver se existe um motorista com este CPF, caso tenha atribui o indice dele no array para a variavel
                    foreach($motoristas as $chaveMot => $motorista){

                        if($motorista->CPF == $user->CPF){
                            $index = $chaveMot;  
                        }

                    }
                    

                    //Caso exista, e o id for menor que o usuário atual apaga o usuário atual que está no arrray e adiciona o novo
                    if($index){
                        
                        if(intval($motoristas[$index]->id) < intval($user->id)){
                            unset($motoristas[$index]);
                            array_push($motoristas,$user);
                        }
                        
                    }else{
                        array_push($motoristas,$user);
                    }
                     
               }

            }

            $motoristas = $this->addTemplateToUsers($motoristas);
            $this->insertUsersDb($motoristas);
      
        }
       
    }

    public function addNewUser($cpf,$id){
        //Init array instace
        $users = [];
        
        //Add user parameters
        $mototorista = new StdClass();
        $mototorista->id = intval($id);
        $mototorista->CPF = $cpf;

        //Push on array
        array_push($users, $mototorista);

        //Add Templates of biometry
        $users = $this->addTemplateToUsers($users);

        //Insert on database
        $this->insertUsersDb($users);
    }

    private function insertUsersDb($arrUsers){

        $this->CI->load->model('ti_biometria/biometria_usuario_digital_model');
        $this->CI->load->model('ti_biometria/biometria_usuario_model');
        $this->CI->load->model('ti_biometria/biometria_fila_model');
        $this->CI->load->model('ti_biometria/biometria_usuario_equipamento_model');
        $this->CI->load->model('ti_biometria/biometria_equipamento_model');
        
        $ArrnewUsers = [];

        foreach ($arrUsers as $motorista){   
            $newUser = $this->CI->db->query("SELECT * FROM BIOMETRIA_USUARIO WHERE CPF = '$motorista->CPF' ")->num_rows() <= 0 ? true : false;
                                                   
            if($newUser && count($motorista->templates) > 0){

                $userInsert = ["CPF" => $motorista->CPF, "DSCOMENTARIO" => "INTEGRADO VIA WEB"];
                $this->CI->biometria_usuario_model->insert($userInsert);
    
                foreach($motorista->templates as $finger){
    
                    $fingerInsert = $this->dividirTemplate($finger->template);
                    $fingerInsert['USUARIOCPF'] = $motorista->CPF;
                    $this->CI->biometria_usuario_digital_model->insert($fingerInsert);
                }

                array_push($ArrnewUsers,$motorista->CPF);
    
            }
        }

        if(count($ArrnewUsers) > 0 ){
            $arrEquipamentos = $this->CI->biometria_equipamento_model->get_all(["INTEGRADO <>" => 0]);
            
            foreach($arrEquipamentos as $equipamento){
                
                foreach($ArrnewUsers as $cpf){
                    $this->CI->biometria_fila_model->insert(["EQUIPAMENTOID" => $equipamento->ID, "USUARIOCPF" => $cpf, "OPERACAO" => "CADASTRO"]);
                }
            }
        }

    }


    private function addTemplateToUsers($motoristas){

        foreach($motoristas as $motorista){   
            $motorista->templates = [];

            $tableTemplates = new StdClass();
            $tableTemplates->join = "LEFT";
            $tableTemplates->object = "templates";
            $tableTemplates->fields = ["template"];
            $tableTemplates->where = [];

            $whereTable = new StdClass();
            $whereTable->object = "users";
            $whereTable->field = "id";
            $whereTable->value = $motorista->id;

            array_push($tableTemplates->where,$whereTable);


            if($result = $this->callRequest('load_objects',$tableTemplates,true)){

                $arrTemplates = json_decode($result);

                foreach($arrTemplates->templates as $template){
                    array_push($motorista->templates,$template);
                }

            }

        }

        return $motoristas;
    }

    private function returnValidateUser($user){   

        if($user->registration){
        
            //Verifica se o cadastro é de um freteiro ou motorista da casa
            if(strlen($user->registration) == 11){
                
                //Concatena com 3 zeros para fazer a consula no SOFtran
                $cpfUser = '000'.$user->registration;

                //Prepara query de consulta
                $queryFrete = $this->CI->db->query("SELECT DSNOME,CDFRETEIRO FROM SOFTRAN_MAGNA.GTCFRETE WHERE CDFRETEIRO = '$cpfUser' AND ININATIVO = 0");
                
                //Verifica se existe um freteiro para este cpf
                if($queryFrete->num_rows() > 0){

                    //Adiciona o nome para o motorista
                    $user->name = $queryFrete->result()[0]->DSNOME;
                    $user->CPF = $queryFrete->result()[0]->CDFRETEIRO;
                    return $user;
                }                
            }else{
                return $this->existsMotoristaDb($user);            
            }                    
        }else{
            return $this->existsMotoristaDb($user,false);
        }
    }

    private function existsMotoristaDb($user,$numSearch = true){

        if($numSearch){

            //Remove caracter '\t' que vem alguns registros
            $user->registration = str_replace("\t", '', $user->registration);            

            if(is_numeric($user->registration)){
                
                $queryFunc = $this->CI->db
                                      ->query("SELECT A.DSNOME as DSNOME, A.NRCPF
                                                    FROM SOFTRAN_MAGNA.SISFUN A
                                                    LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
                                                                                            AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
                                                    WHERE UPPER(B.DSDESCRICAO) LIKE '%MOTORISTA%' 
                                                    AND A.FGDEMITIDO = 0
                                                    AND A.CDMATRICULA = $user->registration");
    
                $queryMatricula = $this->CI->db
                                           ->query("SELECT A.DSNOME as DSNOME, A.NRCPF
                                                        FROM SOFTRAN_MAGNA.SISFUN A
                                                        LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
                                                                                                AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
                                                        WHERE UPPER(B.DSDESCRICAO) LIKE '%MOTORISTA%' 
                                                        AND A.FGDEMITIDO = 0
                                                        AND A.CDFUNCIONARIO = $user->registration");
    
                if($queryFunc->num_rows() > 0){   
                    $user->name = $queryFunc->result()[0]->DSNOME;
                    $user->CPF = '000'.$queryFunc->result()[0]->NRCPF;
                    return $user;
                }else if($queryMatricula->num_rows() > 0){
                    $user->name = $queryMatricula->result()[0]->DSNOME;
                    $user->CPF = '000'.$queryMatricula->result()[0]->NRCPF;
                    return $user;
                }else{
                    return false;
                }
            }

        }else{

            if(substr($user->name,0,6) == "MOT - "){

                $user->name  =   strtoupper(substr($user->name,6));

                $queryName = $this->CI->db
                                      ->query("SELECT UPPER(A.DSNOME) as DSNOME, A.NRCPF
                                                FROM SOFTRAN_MAGNA.SISFUN A
                                                LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
                                                                                        AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
                                                WHERE B.DSDESCRICAO LIKE '%Motorista%' 
                                                AND A.FGDEMITIDO = 0
                                                AND UPPER(A.DSNOME) like '%$user->name%' ");

                if($queryName->num_rows() > 0){  
                    $user->name = $queryName->result()[0]->DSNOME;  
                    $user->CPF = '000'.$queryName->result()[0]->NRCPF;   
                    return $user; 
                }else{
                    return false;
                }                                
            }

        }
       
    }


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
    
}