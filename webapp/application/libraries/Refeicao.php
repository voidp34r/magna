<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class Refeicao{

    const ALMOCO12 = 1;
    const ALMOCO19 = 2;
    const ALMOCO22 = 3;
    const ALMOCO00 = 4;

    const GRUPO12 = 1005;
    const GRUPO19 = 1006;
    const GRUPO22 = 1007;
    const GRUPO00 = 1008;
    
    public function __construct(){
        $this->CI = &get_instance(); 
        $this->CI->load->model('rh_refeicao/rh_refeicao_model');
        $this->CI->load->model('ti_biometria/biometria_equipamento_model');
        $this->CI->load->library('My_PHPMailer');
    }

    public function import($ref){
        return $this->importAfd($ref);
    }

    function importAcess(){
        date_default_timezone_set("Brazil/East");        
        $diasemana_numero = date('w', time());
        if($diasemana_numero  == 1){
            $data = date('d/m/Y', strtotime('-3 days'));
        }else{
            $data = date('d/m/Y', strtotime('-1 days'));            
        }     
        $d = substr($data, 0, 2);  
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d.'/'.$m.'/'.$y;
        $equipamento = $this->CI->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        if($equipamento){
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $this->CI->load->library('IdSecure',$constructIdSecure);
            if($this->CI->idsecure->authenticate()){
                $users = $this->CI->idsecure->reportDepartment($d,$m,$y);
                if($users = $users->data){
                    foreach ($users as $key => $users) {
                        $resultado = $this->CI->db->select('RH_REFEICAO.*')
                                          ->from('RH_REFEICAO ')  
                                          ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.USER_ID_IDSECURE = {$users->user_id}")      
                                          ->order_by("RH_REFEICAO.ID", "desc")
                                          ->get()
                                          ->result();                        
                        if($resultado){                            
                            $operationOk = $this->CI->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.ACESSOU = 1 WHERE RH_REFEICAO.ID = {$resultado[0]->ID}");
                        }                        
                    }
                } 
                $this->CI->db->query("UPDATE RH_REFEICAO SET RH_REFEICAO.ACESSOU = 2 WHERE RH_REFEICAO.ACESSOU is null AND RH_REFEICAO.DATA = '{$data}'");              
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
        $resultado = $this->CI->db->select('RH_REFEICAO.ID')
                          ->from('RH_REFEICAO ')  
                          ->where("RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$ref}'")      
                          ->order_by("RH_REFEICAO.ID", "desc")
                          ->get()
                          ->result();
        if($resultado){
            foreach ($resultado as $key => $value) {
                $this->CI->db->query("DELETE FROM RH_REFEICAO WHERE RH_REFEICAO.ID = {$value->ID} AND RH_REFEICAO.DATA = '{$data}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$ref}'");              
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
        $equipamento = $this->CI->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));
        $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
        $this->CI->load->library('IdClass',$constructParams);
        if($equipamento){            
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
            $this->CI->load->library('IdClass',$constructParams);    
            $this->CI->load->library('IdSecure',$constructIdSecure);
            if($this->CI->idsecure->authenticate()){      
                if($this->CI->idclass->authenticate()){
                    $afd = $this->CI->idclass->getAFDRefeicao($d,$m,$y,0);
                    foreach ($afd as $key => $value) {
                        $usuario = new StdClass();                        
                        $user = $this->CI->idsecure->searchUser(substr($value->PIS, 1));
                        if($user){                            
                            /* Remove os grupos de todos */
                            foreach ( $user->groups as $key => $value) {
                                if($value == Refeicao::GRUPO12 ||
                                $value == Refeicao::GRUPO19 ||
                                $value == Refeicao::GRUPO22 ||
                                $value == Refeicao::GRUPO00 ){                                    
                                    unset($user->groups[$key]);
                                    unset($user->groupsList[$key]);
                                    unset($user->userGroupsList[$key]);
                                }                                
                            }
                            $this->CI->idsecure->setUserGroup($user);                                                          
                        }
                    }
                }
            }
        }
    }

    function importAfd($ref){
        $turn = 1;
        $grupoCode = 1005;
        switch ($ref) {
            case 1:
                $turn = Refeicao::ALMOCO12;
                $grupoCode = Refeicao::GRUPO12;
                break;
            case 2:
                $turn = Refeicao::ALMOCO19;
                $grupoCode = Refeicao::GRUPO19;
                break;
            case 3:
                $turn = Refeicao::ALMOCO22;
                $grupoCode = Refeicao::GRUPO22;
                break;
            case 4:
                $turn = Refeicao::ALMOCO00;
                $grupoCode = Refeicao::GRUPO00;
                break;
        }
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
        $equipamento = $this->CI->biometria_equipamento_model->get_all(array('TIPO'=>'IDSECURE'));                

        if($equipamento){  
            $constructIdSecure = ["ip" => $equipamento[0]->IP, "port" => $equipamento[0]->PORTA, "user" => $equipamento[0]->USUARIO, "password" => $equipamento[0]->SENHA,"protocol"=>$equipamento[0]->PROTOCOLO];
            $constructParams = ["ip" => '192.168.98.13', "user" => 'admin', "password" => 'admin',"protocol"=>'https'];
            $this->CI->load->library('IdClass',$constructParams);    
            $this->CI->load->library('IdSecure',$constructIdSecure);
            if($this->CI->idsecure->authenticate()){      
                if($this->CI->idclass->authenticate()){
                    //$this->removeAcess();
                    $afd = $this->CI->idclass->getAFDRefeicao($d,$m,$y,$turn);
                    $tot = count($afd);
                    foreach ($afd as $key => $value) {
                        $usuario = new StdClass();                        
                        $user = $this->CI->idsecure->searchUser(substr($value->PIS, 1));
                        $usuario->DATA = $value->DTBATIDA;
                        if($user){                            
                            $usuario->NOME = $user->name;
                            $usuario->USER_ID_IDSECURE = $user->id;
                            $group = $this->CI->idsecure->getGroups($grupoCode);
                            if($group){
                                foreach ($user->groups as $key => $value) {
                                    if($value == Refeicao::GRUPO12 ||
                                        $value == Refeicao::GRUPO19 ||
                                        $value == Refeicao::GRUPO22 ||
                                        $value == Refeicao::GRUPO00 ){                                    
                                        unset($user->groups[$key]);
                                        unset($user->groupsList[$key]);
                                        unset($user->userGroupsList[$key]);
                                    }  
                                    if($value != $grupoCode){                                                     
                                        array_push($user->groups, $grupoCode);
                                        array_push($user->groupsList, $group);
                                    }                              
                                }                                
                                $usuario->STATUS = "Sucesso";
                                $totSucc++;
                                //print_r($user);
                                if($this->CI->idsecure->setUserGroup($user)){
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
                        $this->CI->rh_refeicao_model->insert($usuario);
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
        $this->enviarEmail($tot,$totSucc,$totError, $turn);   
        return $retorno;
    }

    private function enviarEmail($userTot,$totSucess,$totError,$horario){
        $retorno = new stdClass();
        $data = date('d/m/Y H:i:s');
        $d = substr($data, 0, 2);
        $m = substr($data, 3, 2);
        $y = substr($data, 6, 4);
        $data = $d."/".$m."/".$y;
        $query = $this->CI->db->select('RH_REFEICAO.*')
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

        $totalAnteriosAcesso = $this->CI->db->select('RH_REFEICAO.*')
                           ->from('RH_REFEICAO ')  
                           ->where("RH_REFEICAO.DATA = '{$dataA}' AND RH_REFEICAO.HORARIO_REFEICAO = '{$horario}' AND RH_REFEICAO.ACESSOU = 1")
                           ->order_by("RH_REFEICAO.ID", "desc")
                           ->get()
                           ->num_rows();

        $totalAnterios = $this->CI->db->select('RH_REFEICAO.*')
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
        
        if($this->CI->my_phpmailer->send_mail('ti03@transmagna.com.br',$conteudo,$assunto) && 
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