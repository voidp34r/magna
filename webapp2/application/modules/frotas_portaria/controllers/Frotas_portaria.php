<?php

class Frotas_portaria extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('portaria_checklist_model');
        $this->load->model('portaria_checklist_foto_model');
        $this->load->model('portaria_foto_categoria_model');
        $this->load->model('portaria_foto_config_model');
        $this->load->model('portaria_visitante_model');
        $this->load->model('portaria_visitante_foto_model');
        $this->load->model('portaria_checklist_ocorrencia_model');
        $this->load->library('pagination');
        $this->dados['modulo_nome'] = 'Frotas > Portaria';
        $this->dados['modulo_menu'] = array('Checklists' => 'listar_checklist', 'Visitante' => 'listar_visitante');
    
        parent::__construct();

        if($this->verifica_gatilho('ENCERRAR_OCORRENCIA'))
        {
            $this->dados['modulo_menu']['Ocorrências'] = 'listar_ocorrencias';    
        }

    }   

    function index ()
    {
        $this->redirect('frotas_portaria/listar_checklist');
    }

    function listar_checklist ($page = 0)
    {
        $get  = $this->input->get();

        if($page <> 0) $page = (($page - 1) * 9) + 1;
        
        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'"); 
    
        $total = $this->db->select('PORTARIA_CHECKLIST.*,SOFTRAN_MAGNA.SISVEICU.NRPLACA')
                            ->from('PORTARIA_CHECKLIST ')
                            ->join('SOFTRAN_MAGNA.SISVEICU','SOFTRAN_MAGNA.SISVEICU.CDVEICULO  = PORTARIA_CHECKLIST.CDVEICULO','left')
                            ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST.USUARIO_ID','left')
                            ->order_by("PORTARIA_CHECKLIST.DTINCLUSAO", "desc")
                            ->get()
                            ->num_rows();
        
        $query = $this->db->select('PORTARIA_CHECKLIST.*,SOFTRAN_MAGNA.SISVEICU.NRPLACA')
                           ->from('PORTARIA_CHECKLIST ')
                           ->join('SOFTRAN_MAGNA.SISVEICU','SOFTRAN_MAGNA.SISVEICU.CDVEICULO  = PORTARIA_CHECKLIST.CDVEICULO','left')
                           ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST.USUARIO_ID','left')
                           ->order_by("PORTARIA_CHECKLIST.DTINCLUSAO", "desc")
                           ->limit(10,$page);

        //Realiza o Where com a Data
        if($this->filtroDate($get)) $query->where($this->filtroDate($get));
                
        //Realiza o Where com o Int
        if($this->filtroInt($get)) $query->where($this->filtroInt($get));
                
        //Filta pela placa do veículo
        if($this->filtroLike($get)) $query->where($this->filtroLike($get));

        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();       
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'frotas_portaria/listar_checklist');
        $this->dados['filtro'] = [];
        
        $this->dados['download'] = $this->verifica_gatilho('DOWNLOAD_CHECKLIST');
        $this->dados['download_planilha_portaria'] = $this->verifica_gatilho('DOWNLOAD_PLANILHA_PORTARIA');
	
        $this->render('listar_checklist');
    }

    function baixar_planilha_visitas(){
        $post = $this->input->post();
        $dtIni = date("d/m/Y", strtotime($post['dtIni'])); 
        $dtFim = date("d/m/Y", strtotime($post['dtFim']));
        $where = "WHERE CDEMPRESA  = {$this->sessao['filial']}
                  AND TRUNC(to_date(DTSAIDA,'YYYYMMDDHH24MISS')) BETWEEN '$dtIni' AND '$dtFim'
                  AND TRUNC(to_date(DTENTRADA,'YYYYMMDDHH24MISS')) BETWEEN '$dtIni' AND '$dtFim'";

        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'"); 
        $data = $this->db->query("SELECT * FROM VISITANTE_PORTARIA $where ORDER BY DTENTRADA DESC")->result(); 
        if( count($data) > 0){
            $htmlOut .= '<table bordered="1">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Visita</th>	
                                <th>Tipo Documento</th>		
                                <th>Documento</th>
                                <th>Empresa</th>	
                                <th>Telefone</th>	
                                <th>Motivo</th>	
                                <th>Solicitante</th>	
                                <th>DtEntrada</th>
                                <th>DtSaida</th>
                                <th>Tipo Veiculo</th>	
                                <th>Modelo Veiculo</th>	
                                <th>Cor veiculo</th>
                                <th>Placa</th>
                                <th>Tipo Visita</th>
                                <th>Tem Veiculo</th>
                                <th>Filial</th>
                            </tr>
                            </thead>
                        ';
            $htmlOut .= "<tbody>";
            foreach($data as $object)
            {
                $dtEntrada = data_oracle_para_web($object->DTENTRADA);
                $dtSaida = data_oracle_para_web($object->DTSAIDA);
                $htmlOut .= "<tr>
                                    <td>$object->ID</td>
                                    <td>$object->NOMEVISITA</td>	
                                    <td>$object->TPDOCTO</td>	
                                    <td>$object->NRDOCUMENTO</td>	
                                    <td>$object->DSEMPRESA</td>	
                                    <td>$object->TELEFONEEMPRESA</td>	
                                    <td>$object->MOTIVOVISITA</td>	
                                    <td>$object->SOLICITANTE</td>	
                                    <td>$dtEntrada</td>	
                                    <td>$dtSaida</td>	
                                    <td>$object->TPVEICULO</td>	
                                    <td>$object->MODELO</td>	
                                    <td>$object->COR</td>	
                                    <td>$object->PLACA</td>
                                    <td>$object->TPVISITA</td>
                                    <td>$object->FGVEICULO</td>
                                    <td>$object->CDEMPRESA</td>
                                </tr>
                            ";
            }
            $htmlOut .= '</tbody></table>';


            echo $htmlOut;
        }else {
            echo json_encode(false);
        } 
    }
    function baixar_planilha(){
        $date = $this->input->post();
        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'"); 
        
        $data = $this->db->select('PORTARIA_CHECKLIST.*,SOFTRAN_MAGNA.SISVEICU.NRPLACA')
                          ->from('PORTARIA_CHECKLIST ')
                          ->join('SOFTRAN_MAGNA.SISVEICU','SOFTRAN_MAGNA.SISVEICU.CDVEICULO  = PORTARIA_CHECKLIST.CDVEICULO','left')
                          ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST.USUARIO_ID','left')
                          ->where("TRUNC(to_date(PORTARIA_CHECKLIST.DTINCLUSAO,'YYYYMMDDHH24MISS')) between '{$date['dtIni']}' and '{$date['dtFim']}'")
                          ->order_by("PORTARIA_CHECKLIST.DTINCLUSAO", "desc")
                          ->get();
        

        $htmlOut = "";
        if( $data->num_rows()){
            $htmlOut .= '<table class="table" bordered="1">
                            <tr>
                                <th>Cód</th>
                                <th>Placa</th>
                                <th>Data Inclusão</th>
                                <th>Leu QrCode</th>
                            </tr>
                        ';
            foreach($data->result() as $object)
            {
                $dtInclusao = data_oracle_para_web($object->DTINCLUSAO);
                $leuQrCode = $object->INQRCODE == 1 ? 'SIM' : 'NÃO';
                $htmlOut .= "
                                <tr>
                                    <td>$object->ID</td>
                                    <td>$object->NRPLACA</td>
                                    <td>$dtInclusao</td>
                                    <td>$leuQrCode</td>
                                </tr>
                            ";
            }
            $htmlOut .= '</table>';


            echo $htmlOut;
        }else {
            echo json_encode(false);
        } 

        
    }

    function listar_ocorrencias ($page = 0)
    {        
        if($page <> 0) $page = (($page - 1) * 9) + 1;

        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'"); 

        $cdEmpresa = $this->sessao['filial'];

        $total = $this->db->select('PORTARIA_CHECKLIST_OCORRENCIA.*,USUARIO.NOME,USPORT.NOME AS NOMEPORT,SOFTRAN_MAGNA.GTCFUNDP.DSNOME')
                          ->from('PORTARIA_CHECKLIST_OCORRENCIA ')
                          ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIORESPONSAVEL','left')
                          ->join('USUARIO USPORT','USPORT.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIOPORTARIA','left')
                          ->join('SOFTRAN_MAGNA.GTCFUNDP','SOFTRAN_MAGNA.GTCFUNDP.NRCPF = PORTARIA_CHECKLIST_OCORRENCIA.CPFMOTORISTA','left')
                          ->where('FGRESOLVIDO','0')
                          ->where('PORTARIA_CHECKLIST_OCORRENCIA.CDEMPRESA',$cdEmpresa)
                          ->order_by("PORTARIA_CHECKLIST_OCORRENCIA.DTCRIACAO", "desc")
                          ->get()
                          ->num_rows();
        
        $query = $this->db->select('PORTARIA_CHECKLIST_OCORRENCIA.*,USUARIO.NOME,USPORT.NOME AS NOMEPORT,SOFTRAN_MAGNA.GTCFUNDP.DSNOME')
                          ->from('PORTARIA_CHECKLIST_OCORRENCIA ')
                          ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIORESPONSAVEL','left')
                          ->join('USUARIO USPORT','USPORT.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIOPORTARIA','left')
                          ->join('SOFTRAN_MAGNA.GTCFUNDP','SOFTRAN_MAGNA.GTCFUNDP.NRCPF = PORTARIA_CHECKLIST_OCORRENCIA.CPFMOTORISTA','left')
                          ->where('FGRESOLVIDO','0')
                          ->where('PORTARIA_CHECKLIST_OCORRENCIA.CDEMPRESA',$cdEmpresa)
                          ->order_by("PORTARIA_CHECKLIST_OCORRENCIA.DTCRIACAO", "desc")
                          ->limit(10,$page);
  
        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();       
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'frotas_portaria/listar_ocorrencias');
       
	
        $this->render('listar_ocorrencias');
    }    

    function resolver_ocorrencia($id)
    {
        $this->form_validation->set_rules('COMENTARIO', 'Comentário', 'trim|required');
        $this->form_validation->set_rules('FGRESOLVIDO', 'Decisão', 'trim|required');
        if ($this->form_validation->run())
        {   
            date_default_timezone_set("Brazil/East");
            $post = $this->input->post();
            $post['DTRESOLUCAO'] = date('YmdHis');
            $post['USUARIORESPONSAVEL'] = $this->dados['sessao']['usuario_id'];
            $this->portaria_checklist_ocorrencia_model->where($id);
            $okUpdate = $this->portaria_checklist_ocorrencia_model->update($post);

            if ($okUpdate) {
                $this->session->set_flashdata('sucesso', 'Ocorrência salva com sucesso.');
                $this->redirect('frotas_portaria/listar_ocorrencias');
            } else {
                $this->dados['erro'] = 'Erro ao gravar ocorrência';
            }
        }

        $this->dados['checklist'] = $this->db->select('PORTARIA_CHECKLIST_OCORRENCIA.*,USUARIO.NOME,SOFTRAN_MAGNA.GTCFUNDP.DSNOME,USPORT.NOME AS NOMEPORT')
                                             ->from('PORTARIA_CHECKLIST_OCORRENCIA ')
                                             ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIORESPONSAVEL','left')
                                             ->join('USUARIO USPORT','USPORT.ID = PORTARIA_CHECKLIST_OCORRENCIA.USUARIOPORTARIA','left')
                                             ->join('SOFTRAN_MAGNA.GTCFUNDP','SOFTRAN_MAGNA.GTCFUNDP.NRCPF = PORTARIA_CHECKLIST_OCORRENCIA.CPFMOTORISTA','left')
                                             ->where(["PORTARIA_CHECKLIST_OCORRENCIA.ID" => $id])
                                             ->get()
                                             ->result_object()[0];
                                                
        $this->dados['id_checklist'] = $id;
        $this->render('formulario_ocorrencia');
    }

    function filtroInt($get = null)
    { 
        
        $arr = [];
        
        if(isset($get['int'])){  
            foreach ($get['int'] as $key => $value ){
                 $arr[$key] = $value; 
            }            
            
        }
        
       return count($arr) > 0 ? $arr : false;
    }
    
    function filtroDate($get = null){

        $retorno = "";
        
        if(isset($get['filtro']['date']))
            
            if($get['filtro']['date']['DTINI'] && $get['filtro']['date']['DTFIM']){

                //Converte as Data
                $dataInicio = DateTime::createFromFormat('d/m/Y', $get['filtro']['date']['DTINI']);
                $dataFim   =  DateTime::createFromFormat('d/m/Y', $get['filtro']['date']['DTFIM']);

                //Data do SQL
                $dtIniSql = $get['filtro']['date']['DTINI'];
                $dtFimSql = $get['filtro']['date']['DTFIM'];

                //Verifica se a Data Inicial é maior que a final    
                if($dataInicio <= $dataFim){
                    $retorno = "TRUNC(to_date(PORTARIA_CHECKLIST.DTINCLUSAO,'YYYYMMDDHH24MISS')) between '{$dtIniSql}' and '{$dtFimSql}'";
                }else{
                    $this->dados['erro'] = 'A Data Fim deve ser maior que a Data de Inicio!';
                }
            }
        
        return $retorno;
    }

    function filtroDocVisit($get = null){
        $retorno = "";        
        if(isset($get['filtro']))
            if($get['filtro']['text']['NRDOCUMENTO']){
            
                $doc = $get['filtro']['text']['NRDOCUMENTO'];

                if($doc <> ""){
                    $retorno = "NRDOCUMENTO = '{$doc}'";                    
                }else{
                    $this->dados['erro'] = 'O número do documento não pode ser vaziu!';
                }
            }                     
        return $retorno;
    }

    function filtroDateVisit($get = null){

        $retorno = "";
        
        if(isset($get['filtro']['date']))
            
            if($get['filtro']['date']['DTINI'] && $get['filtro']['date']['DTFIM']){

                //Converte as Data
                $dataInicio = DateTime::createFromFormat('d/m/Y', $get['filtro']['date']['DTINI']);
                $dataFim   =  DateTime::createFromFormat('d/m/Y', $get['filtro']['date']['DTFIM']);

                //Data do SQL
                $dtIniSql = $get['filtro']['date']['DTINI'];
                $dtFimSql = $get['filtro']['date']['DTFIM'];

                //Verifica se a Data Inicial é maior que a final    
                if($dataInicio <= $dataFim){
                    $retorno = "TRUNC(to_date(VISITANTE_PORTARIA.DTENTRADA,'YYYYMMDDHH24MISS')) between '{$dtIniSql}' and '{$dtFimSql}'";
                }else{
                    $this->dados['erro'] = 'A Data Fim deve ser maior que a Data de Inicio!';
                }
            }
        
        return $retorno;
    }

    function filtroLike($get = null){
        
        if(isset($get['filtro']['like']['NRPLACA']))
            if($get['filtro']['like']['NRPLACA'])
            return $where =  "SOFTRAN_MAGNA.SISVEICU.NRPLACA = '".strtoupper(str_replace('-','',$get['filtro']['like']['NRPLACA']))."'";
    }
    
    function ver_checklist ($id){
        
       $query = $this->db->select('PORTARIA_CHECKLIST.*,SOFTRAN_MAGNA.SISVEICU.NRPLACA,USUARIO.NOME,USUARIO.USUARIO')
                         ->from('PORTARIA_CHECKLIST ')
                         ->join('SOFTRAN_MAGNA.SISVEICU','SOFTRAN_MAGNA.SISVEICU.CDVEICULO  = PORTARIA_CHECKLIST.CDVEICULO')
                         ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST.USUARIO_ID')
                         ->where(array("PORTARIA_CHECKLIST.ID"=>$id))
                         ->get();

        $checklist = $query->result()[0];
        
        if(strripos($checklist->USUARIO, '\\')){
            $checklist->USUARIO = substr($checklist->USUARIO, strpos($checklist->USUARIO, '\\')+1);
        }       
        
        $checklist->categoriaFotos = [];				  

        $fotos	= $this->portaria_checklist_foto_model
                       ->get_all(array("CHECKLIST_ID"=>$checklist->ID));


        foreach ($fotos as $key => $foto) {

            $objAux  =  $this->portaria_foto_config_model->get(array("ID"=>$foto->FOTO_CONFIG_ID));
            
            $idCat   = $objAux->FOTO_CATEGORIA_ID;
            
            $foto->DESC = $objAux->DSDESCRICAO; 

            $categoria = $this->portaria_foto_categoria_model
                              ->get(array("ID"=>$idCat)); 

            if(!empty($checklist->categoriaFotos)){

                $temCategoria = false;

                foreach ($checklist->categoriaFotos as $key => $categoriaChecklist) {

                    if($categoriaChecklist->ID == $categoria->ID){
                            array_push($categoriaChecklist->FOTOS,$foto);
                            $temCategoria = true;	
                    }
                }

                if(!$temCategoria){
                    $agrupamentoCategoria = new Stdclass();
                    $agrupamentoCategoria->NOME = $categoria->NOME;
                    $agrupamentoCategoria->ID = $categoria->ID;
                    $agrupamentoCategoria->FOTOS = [];
                    array_push($agrupamentoCategoria->FOTOS, $foto); 
                    array_push($checklist->categoriaFotos, $agrupamentoCategoria);	
                }

            }else{
                $agrupamentoCategoria = new Stdclass();
                $agrupamentoCategoria->NOME = $categoria->NOME;
                $agrupamentoCategoria->ID = $categoria->ID;
                $agrupamentoCategoria->FOTOS = [];
                array_push($agrupamentoCategoria->FOTOS, $foto); 
                array_push($checklist->categoriaFotos, $agrupamentoCategoria);		
            }
        }
        $this->dados['checklist'] = $checklist;  
        $this->render('ver_checklist');
    }
    

    private function deletarCache(){
        
        $files = glob('/var/www/webapp/zipexport/*'); 
        foreach($files as $file){ 
          if(is_file($file))
            unlink($file); 
        }
    }

    public function download_zip_image($dtIni,$dtFim){
        
        $this->deletarCache();
        date_default_timezone_set('America/Sao_Paulo');
        $zipname = '/var/www/webapp/zipexport/'.date('d-m-Y').'.zip';
	    $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);

        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
        $dtIni =  date("d/m/Y", strtotime($dtIni));
        $dtFim =  date("d/m/Y", strtotime($dtFim));

        $checklists = $this->db->select('PORTARIA_CHECKLIST.*,SOFTRAN_MAGNA.SISVEICU.NRPLACA')
                               ->from(' PORTARIA_CHECKLIST ')
                               ->where("TRUNC(to_date(PORTARIA_CHECKLIST.DTINCLUSAO,'YYYYMMDDHH24MISS')) between '{$dtIni}' and '{$dtFim}' ")
                               ->join('SOFTRAN_MAGNA.SISVEICU','SOFTRAN_MAGNA.SISVEICU.CDVEICULO  = PORTARIA_CHECKLIST.CDVEICULO')
                               ->join('USUARIO','USUARIO.ID = PORTARIA_CHECKLIST.USUARIO_ID')
                               ->order_by("PORTARIA_CHECKLIST.DTINCLUSAO", "desc")
                               ->get()
                               ->result();
                               
        
        if(count($checklists) > 0){
            
            foreach ($checklists as $checklist)
            {
                
               $arrFotos =  $this->portaria_checklist_foto_model
                                 ->where(array("CHECKLIST_ID" => $checklist->ID))
                                 ->get_all(); 
                if(count($arrFotos) > 0)
                {
                    $diretorio = "{$checklist->NRPLACA}-{$checklist->ID}";
                    $zip->addEmptyDir($diretorio);
                    
                    foreach($arrFotos as $foto) 
                    {
                        $descricao = $this->portaria_foto_config_model
                                          ->where('ID',$foto->FOTO_CONFIG_ID)
                                          ->get()
                                          ->DSDESCRICAO;
                        
                        $zip->addFile($foto->CAMINHO,ltrim("{$diretorio}/{$descricao}.JPG",'/'));
                    }
                }
            }
        }
        
	    $zip->close();
        ob_clean();
        header("Cache-Control: no-cache, no-store, must-revalidate"); 
	    header('Content-Type: application/zip');
	    header('Content-disposition: attachment; filename=fotos.zip');
	    //header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
    }

    function listar_visitante($page = 0,$dtSaida = false){
        $status = $dtSaida ? '!=' : '=';
        if($dtSaida) $page = $page - 1;
        if($page <> 0) $page = (($page - 1) * 9) + 1;

        $get  = $this->input->get();

        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'"); 

        $total = $this->portaria_visitante_model
                      ->where(["CDEMPRESA =" => $this->sessao['filial'], "DTSAIDA $status" => null])
                      ->count_rows();
        if($this->filtroDocVisit($get)){
            $query = $this->db->select('VISITANTE_PORTARIA.*')
                               ->from('VISITANTE_PORTARIA ')
                               ->where(["CDEMPRESA" => $this->sessao['filial']])
                               ->limit(10);
            $query->where(["DTSAIDA $status" => null]);
            $query->where($this->filtroDocVisit($get));
        } else{
            $query = $this->db->select('VISITANTE_PORTARIA.*')
                              ->from('VISITANTE_PORTARIA ')
                              ->where(["CDEMPRESA" => $this->sessao['filial'], "DTSAIDA $status" => null])
                              ->limit(10, $page)
                              ->order_by("ID","DESC");            
        }

        if($this->filtroDateVisit($get)) $query->where($this->filtroDateVisit($get));

        $resultado = $query->get();
        $this->dados['lista'] = $resultado->result();
        $this->dados['total'] = $total;
        $this->dados['filtro'] = [];
        $this->dados['paginacao'] = $this->configurePagination(10,$total,'frotas_portaria/listar_visitante');
        $this->render('listar_visitante');
    }

    function buscaVisitante($documento){
        $query = $this->db->select('VISITANTE_PORTARIA.*')
                          ->from('VISITANTE_PORTARIA')
                          ->where(array("VISITANTE_PORTARIA.NRDOCUMENTO"=>$documento, "VISITANTE_PORTARIA.CDEMPRESA" => $this->sessao['filial']))
                          ->order_by("VISITANTE_PORTARIA.ID", "desc")
                          ->get();                 
        $visitante = $query->result();
        if($visitante){
            echo(json_encode($visitante[0])); 
        }
    }

    function ver_visitante ($id){        
        $query = $this->db->select('VISITANTE_PORTARIA.*')
                          ->from('VISITANTE_PORTARIA')
                          ->where(array("VISITANTE_PORTARIA.ID"=>$id, "VISITANTE_PORTARIA.CDEMPRESA" => $this->sessao['filial']))
                          ->get();

        $visitante = $query->result()[0];

        $fotos = $this->portaria_visitante_foto_model
                                  ->get_all(array("VISITANTE_ID"=>$visitante->ID));		
                                  
       
        if(sizeof($fotos) > 0){
            $visitante->fotos = $fotos[0];            
        }else{
            $visitante->fotos = $fotos;    
        }
  
        $this->dados['visitante'] = $visitante;
        $this->render('ver_visitante');
     }

    function cadastro_visitante(){
        $this->dados['titulo'] = 'Cadastrar Visitante';
        $this->form_validation->set_rules('NOMEVISITA', 'Nome', 'trim|required');
        $this->form_validation->set_rules('TPDOCTO', 'Tipo do documento', 'trim|required');
        $this->form_validation->set_rules('NRDOCUMENTO', 'Número do documento', 'trim|required');
        $this->form_validation->set_rules('DSEMPRESA', 'Nome da Emprsa', 'trim|required');
        $this->form_validation->set_rules('TELEFONEEMPRESA', 'Telefone da empresa', 'trim|required');
        $this->form_validation->set_rules('MOTIVOVISITA', 'Motivo da visita', 'trim|required');
        $this->form_validation->set_rules('SOLICITANTE', 'Solicitante da visita', 'trim|required');
        $this->form_validation->set_rules('TPVISITA', 'Tipo de visitas', 'trim|required');
        if ($this->form_validation->run())
        {   $post = $this->input->post();
            date_default_timezone_set("Brazil/East");
            $idImg = $post['IDIMG'];
            $key = array_search($idImg , $post);
            if($key!==false){
                unset($post[$key]);
            }
            $post['DTENTRADA'] = date('YmdHis');
            $post['CDEMPRESA'] = $this->sessao['filial'];
            $retorno = $this->portaria_visitante_model->insert($post);

            if($retorno){
                if($idImg <> 'ND'){
                    $visitante_update = array();
                    $visitante_update['VISITANTE_ID'] = $retorno;
                    $visitante_update['CAMINHO'] ="uploads/visitantes/".$idImg."/";
                    $update = $this->portaria_visitante_foto_model->update($visitante_update, $idImg);
                    if($update){
                        $this->dados['sucesso'] = 'Visitante cadastrado com sucesso!';
                    }else{
                        $this->dados['erro'] = 'Erro ao salvar visitantes!';
                    }
                }else{
                    $this->dados['sucesso'] = 'Visitante cadastrado com sucesso!';
                }
            }else{
                $this->dados['erro'] = 'Erro ao salvar visitantes!';
            }
            $this->redirect('frotas_portaria/listar_visitante');
        }
        $this->render('cadastro_visitante');
    }

    function upload_img_visitante($id, $tipo){
        $destino = "uploads/visitantes/".$id."/";
        $arquivo = "rosto.jpg";
        if($tipo === 'face'){
            $arquivo = "rosto.jpg";
        }else{
            $arquivo = "documento.jpg";
        }        
        if(!is_dir($destino)){
            mkdir($destino, 0777, true);
        }        
        $destino.= $arquivo;
        move_uploaded_file($_FILES['webcam']['tmp_name'], $destino);        
    }

    function cadastra_foto_visitante(){
        print_r($this->portaria_visitante_foto_model->insert(array("CAMINHO" => "0")));
    }

    function finalizar_visita($id){
        $this->portaria_visitante_model->where($id);
        date_default_timezone_set("Brazil/East");
        $this->portaria_visitante_model->update(["DTSAIDA" => date('YmdHis')]);
        $this->dados['sucesso'] = 'Status alterado com sucesso!';
        $this->redirect('frotas_portaria/listar_visitante');
    }    

    function configurePagination($perPage = 10 ,$totalRows = 100,$BaseUrl = "")
    {
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

}
