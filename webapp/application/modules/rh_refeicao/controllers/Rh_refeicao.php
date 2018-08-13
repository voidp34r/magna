<?php

/**
 * @author andretimm,msouz4
 */
class Rh_refeicao extends MY_Controller{

    const ALMOCO12 = 1;
    const ALMOCO19 = 2;
    const ALMOCO22 = 3;
    const ALMOCO00 = 4;

    const GRUPO12 = 1005;
    const GRUPO19 = 1006;
    const GRUPO22 = 1007;
    const GRUPO00 = 1008;


    public function __construct(){

        $this->load->model('rh_refeicao_model');
        $this->load->model('ti_biometria/biometria_equipamento_model');
        $this->load->library('My_PHPMailer');
        $this->load->library('pagination');

        $this->dados['modulo_nome'] = 'RH > Refeição';
        $this->dados['modulo_menu'] = array('Importados' => 'import_refeicao',
                                            'Erros' => 'import_erros',
                                            'Limpar Reservas IDSECURE' => 'import_remove',
                                            'Relatórios' => 'import_relatorio',
                                            'Importação Manual' => 'import_manual');
        
        parent::__construct();
    }

    function import_remove($page = 0){
        $this->render('import_remove');
        //$this->removeAcessoGeral();
    }

    function import_manual($page = 0){
        $get  = $this->input->get();
        $ref = $get[int][HORARIO_REFEICAO];
        $PIS = $get[filtro][like][NOME];
        if (strlen($PIS) >= 10){
            if ($ref > 0){
                $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
                $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
                $this->load->library('IdSecure',$constructIdSecure);
                if($this->idsecure->authenticate()){   
                    $PIS = $get[filtro][like][NOME];

                    switch ($ref) {
                        case '1':
                            $turn = Rh_refeicao::ALMOCO12;
                            $grupoCode = Rh_refeicao::GRUPO12;
                            break;
                        case '2':
                            $turn = Rh_refeicao::ALMOCO19;
                            $grupoCode = Rh_refeicao::GRUPO19;
                            break;
                        case '3':
                            $turn = Rh_refeicao::ALMOCO22;
                            $grupoCode = Rh_refeicao::GRUPO22;
                            break;
                        case '4':
                            $turn = Rh_refeicao::ALMOCO00;
                            $grupoCode = Rh_refeicao::GRUPO00;
                            break;
                    } 
                    $usuario = new StdClass();                        
                    $user = $this->idsecure->searchUser($PIS); 
                    $usuario->DATA = date('d/m/Y');
                    $usuario->NOME = $user->name;
                    if($user){                            
                        $usuario->USER_ID_IDSECURE = $user->id;
                        $group = $this->idsecure->getGroups($grupoCode);
                        if($group){
                            array_push($user->groups, $grupoCode);
                            array_push($user->groupsList, $group);
                            $usuario->STATUS = "Sucesso";
                            $totSucc++;
                            if($this->idsecure->setUserGroup($user)){
                                $usuario->STATUS = "Sucesso";
                                $totSucc++;
                                $usuario->HORARIO_REFEICAO = $turn;
                                $this->rh_refeicao_model->insert($usuario);
                            }else{
                                $totError++;
                                $usuario->STATUS = "Erro so salvar no IdSecure";
                            }                            
                        }                                                    
                    }else{
                            $totError++;
                            $usuario->STATUS = "PIS não encontrado no IdSecure.";
                        }
                }else{
                    $usuario->STATUS = "Erro ao autenticar no IDSECURE, verifique com a T.I se o serviço está funcional";
                }
            }
        }
        $this->dados['retorno'] = $usuario; 
        $this->render('import_manual');
    }

     function import_relatorio($page = 0){
        // Lança valor nas refeições que estão com status de NAO ACESSOU porém por algum erro o sistema nao lançou automaticamente.
        $this->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.VLREFEICAO = '11.19' WHERE RH_REFEICAO.ACESSOU = 2 AND RH_REFEICAO.VLREFEICAO IS NULL"); 
        $get  = $this->input->get();
        $v1 = $get[filtro][like][NOME];
        $limit = true;
        if($page <> 0) $page = (($page - 1) * 9) + 1;
        if (!$v1){

             $dtiFixo = $get[filtro][date][DTREFEICAOI];
             $dtfFixo = $get[filtro][date][DTREFEICAOF];

             $total = $this->db->select("COUNT(RH_REFEICAO.ID) AS QTRESERVA,SUM(RH_REFEICAO.VLREFEICAO) AS VLREFEICAO,
                                         (SELECT COUNT(A.ID) FROM RH_REFEICAO A WHERE A.STATUS is null or A.STATUS = 'Sucesso' AND A.DATA BETWEEN '$dtiFixo' AND '$dtfFixo' AND A.NOME = RH_REFEICAO.NOME AND ACESSOU = 1) AS QTACESSOU,
                                         (SELECT COUNT(A.ID) FROM RH_REFEICAO A WHERE A.STATUS is null or A.STATUS = 'Sucesso' AND A.DATA BETWEEN '$dtiFixo' AND '$dtfFixo' AND A.NOME = RH_REFEICAO.NOME AND ACESSOU = 2) AS QTNAOACESSOU,
                                        RH_REFEICAO.NOME")
                            ->from("RH_REFEICAO ")  
                            ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")  
                            ->group_by("RH_REFEICAO.NOME")                               
                            ->order_by("RH_REFEICAO.NOME", "ASC");
        }
        else{
            $total = $this->db->select('RH_REFEICAO.*')
                                ->from('RH_REFEICAO ')  
                                ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                
                                ->order_by("RH_REFEICAO.ID", "desc");
        }

        if($this->filtroDate2($get)) {
            $limit = false;
            $total->where($this->filtroDate2($get));
        }

        if($this->filtroInt($get)) {
            $limit = false;
            $total->where($this->filtroInt($get));
        }

        if($this->filtroLike($get)) {
            $limit = false;
            $total->where($this->filtroLike($get));
        }

        $total = $total->get()->num_rows();

        if($limit){
            $query = $this->db->select('RH_REFEICAO.*')
                            ->from('RH_REFEICAO ')  
                            ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                 
                            ->order_by("RH_REFEICAO.ID", "desc")
                            ->limit(10,$page);
            $this->dados['paginacao'] = $this->configurePagination(10,$total,'rh_refeicao/import_refeicao');
        }else{
            if (!$v1){
                 $dtiFixo = $get[filtro][date][DTREFEICAOI];
                 $dtfFixo = $get[filtro][date][DTREFEICAOF];

             $query = $this->db->select("COUNT(RH_REFEICAO.ID) AS QTRESERVA,SUM(RH_REFEICAO.VLREFEICAO) AS VLREFEICAO,
                                         (SELECT COUNT(A.ID) FROM RH_REFEICAO A WHERE A.STATUS is null or A.STATUS = 'Sucesso' AND TO_DATE(A.DATA,'DD/MM/YYYY') BETWEEN TO_DATE('$dtiFixo','DD/MM/YYYY') AND TO_DATE('$dtfFixo','DD/MM/YYYY') AND A.NOME = RH_REFEICAO.NOME AND ACESSOU = 1) AS QTACESSOU,
                                         (SELECT COUNT(A.ID) FROM RH_REFEICAO A WHERE A.STATUS is null or A.STATUS = 'Sucesso' AND TO_DATE(A.DATA,'DD/MM/YYYY') BETWEEN TO_DATE('$dtiFixo','DD/MM/YYYY') AND TO_DATE('$dtfFixo','DD/MM/YYYY') AND A.NOME = RH_REFEICAO.NOME AND ACESSOU = 2) AS QTNAOACESSOU,
                                        RH_REFEICAO.NOME")
                            ->from("RH_REFEICAO ")  
                            ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")  
                            ->group_by("RH_REFEICAO.NOME")                               
                            ->order_by("RH_REFEICAO.NOME", "ASC");
            }
            else{
                $query = $this->db->select('RH_REFEICAO.*')
                               ->from('RH_REFEICAO ')  
                               ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                 
                               ->order_by("RH_REFEICAO.ID", "desc");
            }
            $this->dados['paginacao'] = "";
        }        

        if($this->filtroDate2($get)) $query->where($this->filtroDate2($get));
                
        if($this->filtroInt($get)) $query->where($this->filtroInt($get));

        if($this->filtroLike($get)) $query->where($this->filtroLike($get));
        
        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();  
        $this->dados['total'] = $total;
        
        $this->dados['filtro'] = [];
        $this->render('import_relatorio');
    }

    function index(){
        $this->redirect('rh_refeicao/import_refeicao');
    }

    function import_refeicao($page = 0){  
        
        $get  = $this->input->get();
        $limit = true;
        if($page <> 0) $page = (($page - 1) * 9) + 1;
        $total = $this->db->select('RH_REFEICAO.*')
                            ->from('RH_REFEICAO ')  
                            ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                
                            ->order_by("RH_REFEICAO.ID", "desc");

        if($this->filtroDate($get)) {
            $limit = false;
            $total->where($this->filtroDate($get));
        }

        if($this->filtroInt($get)) {
            $limit = false;
            $total->where($this->filtroInt($get));
        }

        if($this->filtroLike($get)) {
            $limit = false;
            $total->where($this->filtroLike($get));
        }

        $total = $total->get()->num_rows();

        if($limit){
            $query = $this->db->select('RH_REFEICAO.*')
                            ->from('RH_REFEICAO ')  
                            ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                 
                            ->order_by("RH_REFEICAO.ID", "desc")
                            ->limit(10,$page);
            $this->dados['paginacao'] = $this->configurePagination(10,$total,'rh_refeicao/import_refeicao');
        }else{
            $query = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.STATUS is null or RH_REFEICAO.STATUS = 'Sucesso'")                                 
                           ->order_by("RH_REFEICAO.ID", "desc");
            $this->dados['paginacao'] = "";
        }        

        if($this->filtroDate($get)) $query->where($this->filtroDate($get));
                
        if($this->filtroInt($get)) $query->where($this->filtroInt($get));

        if($this->filtroLike($get)) $query->where($this->filtroLike($get));
        
        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();  
        $this->dados['total'] = $total;
        
        $this->dados['filtro'] = [];
        $this->render('import_refeicao');
    } 

    function import_erros($page = 0){

        $get  = $this->input->get();

        if($page <> 0) $page = (($page - 1) * 9) + 1;
        $total = $this->db->select('RH_REFEICAO.*')
                          ->from('RH_REFEICAO ')  
                          ->where("RH_REFEICAO.STATUS <> 'Sucesso'")                                 
                          ->order_by("RH_REFEICAO.ID", "desc");

        if($this->filtroDate($get)) $total->where($this->filtroDate($get));

        if($this->filtroInt($get)) $total->where($this->filtroInt($get));

        if($this->filtroLike($get)) $total->where($this->filtroLike($get));

        $total = $total->get()->num_rows();

        $query = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.STATUS <> 'Sucesso' ")      
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->limit(10,$page);

        if($this->filtroDate($get)) $query->where($this->filtroDate($get));
                
        if($this->filtroInt($get)) $query->where($this->filtroInt($get));

        if($this->filtroLike($get)) $query->where($this->filtroLike($get));

        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();  
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'rh_refeicao/import_erros');
        $this->dados['filtro'] = [];
        $this->render('import_error');
    }

    function filtroDate($get = null){

        $retorno = "";
        
        if(isset($get['filtro']['date'])){
            
            if($get['filtro']['date']['DTREFEICAO']){
                //Data do SQL
                $dt = $get['filtro']['date']['DTREFEICAO'];
                $retorno = "RH_REFEICAO.DATA = '{$dt}'";
            }
        }        
        return $retorno;
    }

    function filtroDate2($get = null){

        $retorno = "";
        
        if(isset($get['filtro']['date'])){
            
            if($get['filtro']['date']['DTREFEICAOI']){
                //Data do SQL
                $dti = $get['filtro']['date']['DTREFEICAOI'];
                $dtf = $get['filtro']['date']['DTREFEICAOF'];
                $retorno = "TO_DATE(RH_REFEICAO.DATA,'DD/MM/YYYY') BETWEEN TO_DATE('{$dti}','DD/MM/YYYY') and TO_DATE('{$dtf}','DD/MM/YYYY')";
            }
        }        
        return $retorno;
    }

    function filtroInt($get = null){ 
        
        $arr = [];
        
        if(isset($get['int'])){  
            foreach ($get['int'] as $key => $value ){
                 $arr[$key] = $value; 
            }            
            
        }        
       return count($arr) > 0 ? $arr : false;
    }

    function filtroLike($get = null){
        $where = "";
        if(isset($get['filtro']['like']['NOME'])){
            if($get['filtro']['like']['NOME']){
                $where =  "UPPER(RH_REFEICAO.NOME) LIKE '%".strtoupper($get['filtro']['like']['NOME'])."%'";
            }
        }    
        return $where;
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
    
    function removeAcessoGeral($ref){
        set_time_limit(2500);
        date_default_timezone_set("Brazil/East");
        $data = date('d/m/Y H:i:s');
        $d = intval(substr($data, 0, 2));
        $m = intval(substr($data, 3, 2));
        $y = intval(substr($data, 6, 4));
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        if($equipamento){ 
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO]; 
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){
                $this->idsecure->removeGeral($ref);
            }
        }
    }   

    function removeAcess(){
        set_time_limit(2500);
        date_default_timezone_set("Brazil/East");
        $data = date('d/m/Y H:i:s');
        $d = intval(substr($data, 0, 2));
        $m = intval(substr($data, 3, 2));
        $y = intval(substr($data, 6, 4));
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
        $this->load->library('IdClass',$constructParams);
        if($equipamento){            
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
            $this->load->library('IdClass',$constructParams);    
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){
                if($this->idclass->authenticate()){
                    $afd = $this->idclass->getAFDRefeicao($d,$m,$y,0);
                    foreach ($afd as $key => $value) {
                        $usuario = new StdClass();                        
                        $user = $this->idsecure->searchUser(substr($value->PIS, 1));
                        if($user){                           
                            /* Remove os grupos de todos */
                            foreach ( $user->groups as $key => $value) {
                                if($value == Rh_refeicao::GRUPO12 ||
                                $value == Rh_refeicao::GRUPO19 ||
                                $value == Rh_refeicao::GRUPO22 ||
                                $value == Rh_refeicao::GRUPO00 ){                                    
                                    unset($user->groups[$key]);
                                    unset($user->groupsList[$key]);
                                    unset($user->userGroupsList[$key]);
                                }                                
                            }
                            $this->idsecure->setUserGroup($user);
                            /*$pos = array_search(Rh_refeicao::GRUPO12, $user->groups);
                            if($pos){  
                                //print_r("REMOVE")//
                                if($t){
                                    print_r($user);
                                    echo "<br>";
                                    echo $pos;
                                    echo "<br>";
                                }
                                unset($user->groups[$pos]);
                                unset($user->groupsList[$pos]);
                                unset($user->userGroupsList[$pos]);
                                //$this->idsecure->setUserGroup($user);
                                if($t){
                                echo(json_encode($user->groups));
                                $t = false;
                                }
                                //print_r(json_encode($this->idsecure->setUserGroup($user)));
                            }
                            $pos = array_search(Rh_refeicao::GRUPO19, $user->groups);
                            if($pos){
                                unset($user->groups[$pos]);
                                unset($user->groupsList[$pos]);
                                unset($user->userGroupsList[$pos]);                                
                                $this->idsecure->setUserGroup($user);
                            }
                            $pos = array_search(Rh_refeicao::GRUPO22, $user->groups);
                            if($pos){
                                unset($user->groups[$pos]);
                                unset($user->groupsList[$pos]);
                                unset($user->userGroupsList[$pos]);
                                $this->idsecure->setUserGroup($user);
                            }
                            $pos = array_search(Rh_refeicao::GRUPO00, $user->groups);
                            if($pos){
                                unset($user->groups[$pos]);
                                unset($user->groupsList[$pos]);
                                unset($user->userGroupsList[$pos]);
                                $this->idsecure->setUserGroup($user);
                            }  */                                                    
                        }
                    }
                }
            }
        }
    }

    function importAcess($today = false){
        date_default_timezone_set("Brazil/East");        
        $diasemana_numero = date('w', time());
        if($diasemana_numero  == 1){
            $data = date('d/m/Y', strtotime('-3 days'));
        }else{
            $data = date('d/m/Y', strtotime('-1 days'));            
        } 
        if($today){
            $data = date('d/m/Y');
        }     
        $d = substr($data, 0, 2);  
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d.'/'.$m.'/'.$y;
        $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        if($equipamento){
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $this->load->library('IdSecure',$constructIdSecure);
            if($this->idsecure->authenticate()){
                $users = $this->idsecure->reportDepartment($d,$m,$y);
                if($users = $users->data){
                    foreach ($users as $key => $users) {
                        $resultado = $this->db->select('RH_REFEICAO.*')
                                          ->from('RH_REFEICAO ')  
                                          ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.USER_ID_IDSECURE = {$users->user_id}")      
                                          ->order_by("RH_REFEICAO.ID", "desc")
                                          ->get()
                                          ->result();
                        
                        if($resultado){                            
                            $operationOk = $this->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.ACESSOU = 1 WHERE RH_REFEICAO.ID = {$resultado[0]->ID}");
                            $operationOk2 = $this->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.VLREFEICAO = '2.24' WHERE RH_REFEICAO.ID = {$resultado[0]->ID}");
                        }                        
                    }
                } 
                $this->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.ACESSOU = 2 WHERE RH_REFEICAO.ACESSOU is null AND RH_REFEICAO.DATA = '{$data}'");
                $this->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.VLREFEICAO = '11.19' WHERE RH_REFEICAO.ACESSOU is null AND RH_REFEICAO.DATA = '{$data}'");              
            }
        }
    }

    function deleteImport($ref){
        date_default_timezone_set("Brazil/East");
        $data = date('d/m/Y H:i:s');
        $d = substr($data, 0, 2);
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d.'/'.$m.'/'.$y;
        $resultado = $this->db->select('RH_REFEICAO.ID')
                          ->from('RH_REFEICAO ')  
                          ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$ref}'")      
                          ->order_by("RH_REFEICAO.ID", "desc")
                          ->get()
                          ->result();
        if($resultado){
            foreach ($resultado as $key => $value) {
                $this->db->query("DELETE FROM RH_REFEICAO WHERE RH_REFEICAO.ID = {$value->ID} AND RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$ref}'");              
            }
        }
    }

    function servico(){
        echo "teste";
    }
    
    function importAfd($ref, $relat){        
        if($relat == 1){
            switch ($ref) {
                case '1':
                    $turn = Rh_refeicao::ALMOCO12;
                    $grupoCode = Rh_refeicao::GRUPO12;
                    break;
                case '2':
                    $turn = Rh_refeicao::ALMOCO19;
                    $grupoCode = Rh_refeicao::GRUPO19;
                    break;
                case '3':
                    $turn = Rh_refeicao::ALMOCO22;
                    $grupoCode = Rh_refeicao::GRUPO22;
                    break;
                case '4':
                    $turn = Rh_refeicao::ALMOCO00;
                    $grupoCode = Rh_refeicao::GRUPO00;
                    break;
            }
            $this->importAcess(true);
            $this->relatorioAcessos($turn);
            $ret = new StdClass();
            $ret->status = true;
            $ret->msg = "Relatório enviado via email"; 
            echo json_encode($ret);
        }else{
            set_time_limit(2500);
            $this->importAcess();
            $this->deleteImport($ref);
            date_default_timezone_set("Brazil/East");
            $data = date('d/m/Y H:i:s');
            $d = intval(substr($data, 0, 2));
            $m = intval(substr($data, 3, 2));
            $y = intval(substr($data, 6, 4));
            $retorno = new StdClass();
            $tot = 0;
            $totSucc = 0;
            $totError = 0;
            $turn = 1;
            $grupoCode = 1005;
            switch ($ref) {
                case '1':
                    $turn = Rh_refeicao::ALMOCO12;
                    $grupoCode = Rh_refeicao::GRUPO12;
                    break;
                case '2':
                    $turn = Rh_refeicao::ALMOCO19;
                    $grupoCode = Rh_refeicao::GRUPO19;
                    break;
                case '3':
                    $turn = Rh_refeicao::ALMOCO22;
                    $grupoCode = Rh_refeicao::GRUPO22;
                    break;
                case '4':
                    $turn = Rh_refeicao::ALMOCO00;
                    $grupoCode = Rh_refeicao::GRUPO00;
                    break;
            }
            $equipamento = $this->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));                

            if($equipamento){            
                $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
                $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
                $this->load->library('IdClass',$constructParams);    
                $this->load->library('IdSecure',$constructIdSecure);
                if($this->idsecure->authenticate()){      
                    if($this->idclass->authenticate()){
                        //$this->removeAcess();
                        $afd = $this->idclass->getAFDRefeicao($d,$m,$y,$turn);
                        $tot = count($afd);
                        foreach ($afd as $key => $value) {
                            $usuario = new StdClass();                        
                            $user = $this->idsecure->searchUser(substr($value->PIS, 1));
                            $usuario->DATA = $value->DTBATIDA;
                            if($user){                            
                                $usuario->NOME = $user->name;
                                $usuario->USER_ID_IDSECURE = $user->id;
                                $group = $this->idsecure->getGroups($grupoCode);
                                if($group){
                                    array_push($user->groups, $grupoCode);
                                    array_push($user->groupsList, $group);
                                    $usuario->STATUS = "Sucesso";
                                    $totSucc++;
                                    if($this->idsecure->setUserGroup($user)){
                                        $usuario->STATUS = "Sucesso";
                                        $totSucc++;
                                    }else{
                                        $totError++;
                                        $usuario->STATUS = "Erro so salvar no IdSecure";
                                    }                            
                                }                                                    
                            }else{
                                $totError++;
                                $usuario->NOME = $value->PIS;
                                $usuario->STATUS = "Usuário não encontrado no IdSecure.";
                            }
                            $usuario->HORARIO_REFEICAO = $turn;
                            $this->rh_refeicao_model->insert($usuario);
                        }
                    }else{
                        $retorno->status = false;
                        $retorno->msg = "Erro ao autencicar no relógio refeição.";
                    }                
                }else{
                    $retorno->status = false;
                    $retorno->msg = "Erro ao autencicar no IdSecure.";
                }           
            }else {
                $retorno->status = false;
                $retorno->msg = "Nenhum equipamento cadastro.";
            }

            if($tot > 0 && $totError > 0){
                $retorno->status = false;
                $retorno->msg = "Alguns usuário não foram importados, verifique na aba erros."; 
            }else{
                $retorno->status = true;
                $retorno->msg = "Registro importados para a catraca com sucesso."; 
            }
            print_r(json_encode($retorno));
            //echo(json_encode($retorno));
            $this->enviarEmail($tot,$totSucc,$totError, $turn);
            //$this->relatorioAcessos($turn);
        }   
    }

    private function enviarEmail($userTot,$totSucess,$totError,$horario){
        $retorno = new stdClass();
        $data = date('d/m/Y H:i:s');
        $d = substr($data, 0, 2);
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d."/".$m."/".$y;
        $query = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}'")      
                           ->order_by("RH_REFEICAO.ID", "desc");
        $resultado = $query->get();
        $funcionarios = $resultado->result();  

        $diasemana = date('w', time());
        if($diasemana  == 1){
            $dataAnterio = date('d/m/Y', strtotime('-3 days'));
        }else{
            $dataAnterio = date('d/m/Y', strtotime('-1 days'));            
        }      
        $dA = substr($dataAnterio, 0, 2);  
        $mA = substr($dataAnterio, 3, 2);
        $yA = substr($dataAnterio, 6, 4);
        $dataA = $dA.'/'.$mA.'/'.$yA;

        $totalAnteriosAcesso = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$dataA}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}' AND RH_REFEICAO.ACESSOU = 1")
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->get()
                           ->num_rows();

        $totalAnterios = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$dataA}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}'")
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->get()
                           ->num_rows();

        $funcionarioAnterios = $resultado->result();  

        switch ($horario) {
            case '1':
                $horario = "12H";
                break;
            case '2':
                $horario = "19H";
                break;
            case '3':
                $horario = "22H";
                break;
            case '4':
                $horario = "00H";
                break;
        }
        
        $conteudo = "<style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                        
                        td, th {
                            border: 1px solid #dddddd;
                            text-align: left;
                            padding: 7px;
                        }
                        
                        tr:nth-child(even) {
                            background-color: #dddddd;
                        }
                        </style>
                        <label style='font-size : 18px;'>
                            <h3>Registro de Refeição para às {$horario} </h3>
                            Total de reservas: <b>{$userTot}</b><br>
                            Total de resevas com erro: <b>{$totError}</b><br>                
                            <h3>Registro Refeição das {$horario} - {$dataA}</h3> 
                            Total de reservas: <b>{$totalAnterios}</b><br> 
                            Total reservas consumidas: <b>{$totalAnteriosAcesso}</b><br><br>
                            <small>
                                <b>Caso alguma reserva com erro, favor verificar o status da importação no WebApp.</b><small>
                                <br>     
                                <br>   
                                <small><b>Alerta gerado via WebApp da Transmagna, favor não responder.</b></small>
                            </small><br>
                            
                            ===============================================================================
                            <br>
                            <p>Lista de Funcionários</p>
                            <table>
                                <tr>
                                    <th>Nome</th>
                                    <th>Refeição</th>
                                    <th>Status Liberação Catraca</th>
                                </tr>";

        $corpoTabela = '';
        foreach ($funcionarios as $key => $value) {
            $corpoTabela.= "<tr>
                                <td>{$value->NOME}</td>
                                <td>{$horario}</td>
                                <td>{$value->STATUS}</td>
                            </tr>";
        }

        $corpoFinal ="  
                </table>
                </label>";

        $conteudo.= $corpoTabela;
        $conteudo.= $corpoFinal;
        
        $assunto = "Refeição - ".$horario;                
        
        if($this->my_phpmailer->send_mail('ti03@transmagna.com.br',$conteudo,$assunto) && 
            $this->my_phpmailer->send_mail('refeitorio@transmagna.com.br',$conteudo,$assunto)){
            $retorno->ok = true;
            $retorno->mensagem = "Email enviado com sucesso!";
            return true;
        }else{
            $retorno->ok = false;
            $retorno->mensagem = "Ocorreu um erro ao tentar enviar o Email!";
            return false;
        }                        
    }


    function relatorioAcessos($horario){
        $retorno = new stdClass();
        $data = date('d/m/Y H:i:s');
        $d = substr($data, 0, 2);
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d."/".$m."/".$y;
        $query = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}'")      
                           ->order_by("RH_REFEICAO.ID", "desc");
        $resultado = $query->get();
        $funcionarios = $resultado->result();  

        $dataAnterio = date('d/m/Y');    
        $dA = substr($dataAnterio, 0, 2);  
        $mA = substr($dataAnterio, 3, 2);
        $yA = substr($dataAnterio, 6, 4);
        $dataA = $dA.'/'.$mA.'/'.$yA;

        $totalAnteriosAcesso = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$dataA}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}' AND RH_REFEICAO.ACESSOU = 1")
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->get()
                           ->num_rows();

        $totalAnterios = $this->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$dataA}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}'")
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->get()
                           ->num_rows();

        $funcionarioAnterios = $resultado->result();  

        switch ($horario) {
            case '1':
                $horario = "12H";
                break;
            case '2':
                $horario = "19H";
                break;
            case '3':
                $horario = "22H";
                break;
            case '4':
                $horario = "00H";
                break;
        }
        
        $conteudo = "<style>
                        table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                        
                        td, th {
                            border: 1px solid #dddddd;
                            text-align: left;
                            padding: 7px;
                        }
                        
                        tr:nth-child(even) {
                            background-color: #dddddd;
                        }
                        </style>
                        <label style='font-size : 18px;'>
                            <h3>Registro de Refeição para às {$horario} </h3>         
                            Total de reservas: <b>{$totalAnterios}</b><br> 
                            Total reservas consumidas: <b>{$totalAnteriosAcesso}</b><br><br>
                            <small>    
                                <br>   
                                <small><b>Alerta gerado via WebApp da Transmagna, favor não responder.</b></small>
                            </small><br>
                            
                            ===============================================================================
                            <br>
                            <p>Lista de Funcionários</p>
                            <table>
                                <tr>
                                    <th>Nome</th>
                                    <th>Status</th>                                    
                                </tr>";

        $corpoTabela = '';
        foreach ($funcionarios as $key => $value) {
            $status = "Não acessou";
            if($value->ACESSOU == 1){
                $status = "Acessou";
            }
            $corpoTabela.= "<tr>
                                <td>{$value->NOME}</td>
                                <td>{$status}</td>
                            </tr>";
        }
        $corpoFinal ="  
                </table>
                </label>";

        $conteudo.= $corpoTabela;
        $conteudo.= $corpoFinal;
        
        $assunto = "Refeição - ".$horario;                
        
        if($this->my_phpmailer->send_mail('ti03@transmagna.com.br',$conteudo,$assunto) && 
            $this->my_phpmailer->send_mail('refeitorio@transmagna.com.br',$conteudo,$assunto)){
            $retorno->ok = true;
            $retorno->mensagem = "Email enviado com sucesso!";
            return true;
        }else{
            $retorno->ok = false;
            $retorno->mensagem = "Ocorreu um erro ao tentar enviar o Email!";
            return false;
        }     
    }
}
