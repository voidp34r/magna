<?php


class Cce_entregas0800 extends MY_Controller{
	
    public function __construct(){
		parent::__construct();
        $this->load->library('pagination');
        $this->load->library('GraficoCentral');
		$this->dados['modulo_nome'] = 'Coleta e Entrega > Central de Entregas';
		$this->dados['modulo_menu'] = ['Entregas'   => 'infoEntregas0800', 'Motoristas' => 'listarMotoristas'];
        $this->load->library('softran_oracle');        
		$this->load->model('motoristas_model');
        $this->load->model('infoentregas0800_model');

        $this->publico = array('retornaGraficoPorFilal');

        if($this->verifica_gatilho('GRUPO_EMPRESA')){
            $this->dados['modulo_menu']['Configurações Graficos'] = 'listar_grupos_empresa';
        }
        if($this->verifica_gatilho('GRAFICOS')){
            $this->dados['modulo_menu']['Gráficos']  = 'graficoEntregas0800';
        }

	}
	
	function index(){
		$this->redirect('cce_entregas0800/infoEntregas0800'); 
	}
	

        
        /**
         *  <b>Retorna a paginação dos registros buscados</b>
         *  @param int $PerPage Quantidade de registro por página
         *  @param int $TotalRows Total de registro
         *  @param String $BaseUrl do método que realiza a busca
         */
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
    
        /**
         *
         * Retorna o filtro utilizado nas telas atráves dos input's
         * @param int $get array das informações         
         * @param String $where Condição a ser utilizada 
         */
        function getFiltroWhereString($get = null,$where = "1 = 1"){
            
            if (!empty($get['filtro']['like'])){
		foreach ($get['filtro']['like'] as $get_chave => $get_valor){
                    if ($get_valor){
                        $valor = trim(strtolower($get_valor));
			$where .= " AND LOWER($get_chave) LIKE '%$valor%'";
		    }
		}
	    }
            return $where;
        }
        
        /**
         * Busca o dados pela data das informações do 0800 Entrega
         * @param  string $Where default "1 = 1" 
         * @param  get    $get   campo da requisição
         */
        function getFiltroDate($get = null,$where = " 1 = 1"){
            
            if (isset($get['filtro']['date']['DTINI'])){
                $DataInicio = $get['filtro']['date']['DTINI'];
            }
            
            if (isset($get['filtro']['date']['DTFIM'])){
               $DataFim  =  $get['filtro']['date']['DTFIM'];  
            }
            if(!empty($DataInicio) || !empty($DataFim)){
                
                if(!empty($DataInicio) && !empty($DataFim)){
                    $DtIni =  $get['filtro']['date']['DTINI'];  
                    $DtFim =  $get['filtro']['date']['DTFIM'];
                    $where .= " AND TRUNC(to_date(DtIni,'YYYYMMDDHH24MISS')) >=  '{$DtIni}'";
                    $where .= " AND TRUNC(to_date(DtFim,'YYYYMMDDHH24MISS')) <=  '{$DtFim}'";
                }else if(!empty($DataInicio)){
                    $DtIni  = $get['filtro']['date']['DTINI'];
                    $where .= "  AND TRUNC(to_date(DtIni,'YYYYMMDDHH24MISS')) >= '{$DtIni}'";                    
                }else {
                    $DtFim  = $get['filtro']['date']['DTFIM'];
                    $where .= " AND TRUNC(to_date(DtFim,'YYYYMMDDHH24MISS')) <=  '{$DtFim}'";
                }
                return  $where;   
            }
        }

        /**
         * Realiza a consulta pelo tipos de taxa TDE ou TDA
         */
         function getFiltroTiposTaxa($get = null,$where = " 1 = 1"){
           $Result = ""; 
           if(isset($get['getFiltroTipo'])){
            foreach ($get['getFiltroTipo'] as $key => $value ) {
                
                switch ($value) {
                    case 1:
                        $where .= " AND TDE <> 0 ";
                        break;
                    case 2:
                        $where .= " AND TDA <> 0 ";
                        break;
                    case 3:
                        $where .= " AND TDE = 0 ";
                        break;
                    case 4:
                        $where .= " AND TDA = 0 ";
                        break;
                }   
            }  
                return $where;
           }
         }
         
        /**
         * Realiza a consulta pelo tipo das ocorrências 
         */
         function getFiltroTipoOcorrencia($get = null,$where = " 1 = 1"){
           $Result = ""; 
           if(isset($get['getFiltroDate'])){
            foreach ($get['getFiltroDate'] as $key => $value ) {
                if($key == 0){
                    $Result =  "{$value}";
                }else{
                    $Result .= ",{$value}";
                }       
            }  
                return $where .= " AND CDULTIMAOCORRENCIA IN ({$Result})";
           }
         }
         
        /*
         * Filtro para verificar quando tempo demorou a entrega para ser realizada  
         */
         function getFiltroTempoEntrega($get,$where = " 1 = 1"){
            
             if(isset($get['hrini']) && isset($get['hrfim'])){
                $hrIni = $get['hrini'];
                $hrFim = $get['hrfim'];
                if (!empty($hrIni) && !empty($hrFim)){
                    
                    return $where .= " AND  TO_CHAR(EXTRACT(HOUR from NUMTODSINTERVAL((to_date(DtFim,'YYYYMMDDHH24MISS') - to_date(DtIni,'YYYYMMDDHH24MISS')),'DAY')),'FM00') 
                                        ||':' ||
                                        TO_CHAR(EXTRACT(Minute from NUMTODSINTERVAL((to_date(DtFim,'YYYYMMDDHH24MISS') - to_date(DtIni,'YYYYMMDDHH24MISS')),'DAY')),'FM00')  
                                        BETWEEN '{$hrIni}' AND '{$hrFim}'";
                    
                }
                
            } 
         }
         

         
         
         /** Filtro utilizado para as empresa  */
        function getFiltroEmpresa($get,$where = "1 = 1"){
            
            if(isset($get['cdFilial']) ){
                $CdFilial = $get['cdFilial'];
                if(!empty($CdFilial)){
                    return $where .= " AND  CDFILIALENTREGA IN ($CdFilial) ";
                }
            }
        }
         
         
        /**
         * Converte data de Dia/Mes/Ano para Mes/Dia/Ano para ultilizar nas consultas SQL
         * @param String $Data Data que será convertida
         */
        function converteDataFiltro($Data){
            $dia = substr($Data, 0,2);
            $mes = substr($Data, 3,2);
            $ano = substr($Data, 6,4);
            return "{$dia}/{$mes}/{$ano}";
        }
                
	function listarMotoristas($page = 1, $order = 'DSNOME'){
            
        $where = (String) null;
        $get = $this->input->get();
        $where = $this->getFiltroWhereString($get);
        $perPage = 10;

		$lista = $this->motoristas_model->getMotoristas()
			->setWhere($where)
			->paginateOracle($page, $perPage, $order)
			->go();
		
		$totalRows = $this->motoristas_model->getMotoristas()
			->setWhere($where)
			->count()
			->go();
		
		$this->dados['filtro'] = (!empty($get['filtro'])) ? $get['filtro'] : array();
		$this->dados['titulo'] = 'Listar Motoristas';
		$this->dados['lista'] = $lista;
		$this->dados['total'] = $totalRows;
                
		//Metodo Utilizado para relizar a paginação
        $this->dados['paginacao'] = $this->configurePagination($perPage, $totalRows, "cce_entregas0800/listarMotoristas") ;
		$this->render('listarMotoristas');
    }
    

    function listar_grupos_empresa($page = 1){
        $this->load->model('grupo_empresa_model');
        if($this->input->get('filtro')['where']['CDGRUPOCLIENTE']){
            $this->grupo_empresa_model->set_where_pagination('CDGRUPOCLIENTE',$this->input->get('filtro')['where']['CDGRUPOCLIENTE']);
        } 

        if($this->input->get('filtro')['like']['DSGRUPOCLIENTE']){
            $this->grupo_empresa_model->set_where_pagination('UPPER(DSGRUPOCLIENTE)','like',  strtoupper($this->input->get('filtro')['like']['DSGRUPOCLIENTE']));
        }
            
        $total = $this->grupo_empresa_model->count_rows();
        $lista = $this->grupo_empresa_model->paginate(10, $total, $page);
        
        foreach($lista as $obj){

            $rs = $this->db->query("SELECT FGATIVO FROM GRUPO_EMPRESA_GRAFICO WHERE CDGRUPOCLIENTE = $obj->CDGRUPOCLIENTE ");
            if($rs->num_rows() > 0){
                $obj->FGATIVO = $rs->result()[0]->FGATIVO ? 'Ativado' : 'Desativado';
            }else{
                $obj->FGATIVO = 'Desativado';
            }
            
        }
        $this->dados['filtro'] = $this->input->get('filtro');
        $this->dados['page'] = $page;
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->grupo_empresa_model->all_pages;
        $this->render('listarGrupos');
        
    }

    function editar_grupo_cliente($cdEmpresa = null) {
        $this->load->model('grupo_empresa_model');    

        if(!empty($this->input->post())){
            $fgAtivo = !isset($this->input->post()['FGATIVO']) ? 0 : 1; 
            $temRegistroAnterior = $this->db->query("SELECT FGATIVO FROM GRUPO_EMPRESA_GRAFICO WHERE CDGRUPOCLIENTE = {$this->input->post()['CDGRUPOCLIENTE'] } ")->num_rows() >  0 ? true : false ;

            if($temRegistroAnterior){
                $this->db->query("UPDATE GRUPO_EMPRESA_GRAFICO SET FGATIVO = $fgAtivo  WHERE CDGRUPOCLIENTE = {$this->input->post()['CDGRUPOCLIENTE'] }");
            }else{
                $this->db->query("INSERT INTO GRUPO_EMPRESA_GRAFICO (FGATIVO,CDGRUPOCLIENTE) VALUES($fgAtivo,{$this->input->post()['CDGRUPOCLIENTE'] })");
            }
            $this->redirect('cce_entregas0800/listar_grupos_empresa');
        }else{
            
            $data =  $this->grupo_empresa_model->get(['CDGRUPOCLIENTE' => $cdEmpresa]);
            $rs = $this->db->query("SELECT FGATIVO FROM GRUPO_EMPRESA_GRAFICO WHERE CDGRUPOCLIENTE = $cdEmpresa ");
            
            if($rs->num_rows()){
                $data->FGATIVO = $rs->result()[0]->FGATIVO;
            }else{
                $data->FGATIVO = 0;
            }
        }

        $this->dados['cli'] =  $data;
        $this->render('editar_grupos');
    }  
    

        
         /**
         * Lista todas as entregas baixadas via 0800  
         */
        function infoEntregas0800($page = 1,$order = 'DTINI DESC'){
            
            //Filtros Where's : D - Data , S - String, T - Tipo Ocorrência, Te - Tempo de Entrega, Emp - Código da empresa de Entrega
            $whereD   = (String) null;
            $whereS   = (String) null;
            $whereT   = (String) null;
            $whereTe  = (String) null;
            $whereEmp = (String) null;
            $where    = (String) null;
            $sqlPagination = (String) null;
            $get      = $this->input->get();
            
            if(isset($get['sqlPagination'])){
                $sqlPagination =  $get['sqlPagination'] ;
            }
                        
            //Verifica se existe paginação ou se é um novo filtro
            if(empty(trim($sqlPagination))){
                //Where com as String, Filtro like
                $whereS  =  $this->getFiltroWhereString($get);

                //Where com as Datas da ocorrências - (Inicio e Fim)
                $whereD  =  $this->getFiltroDate($get,$whereS)? $this->getFiltroDate($get,$whereS) : $whereS;

                //Where com os Tipo de Ocorrência 1- Inicio de Entrega 3- Entrega Normal 5- Problema na Entrega
                $whereT  =  $this->getFiltroTipoOcorrencia($get, $whereD)? $this->getFiltroTipoOcorrencia($get, $whereD) :$whereD ; 

                //Where com o tempo de demora da Entrega    
                $whereTe   =  $this->getFiltroTempoEntrega($get,$whereT)? $this->getFiltroTempoEntrega($get,$whereT) : $whereT;

                //Where pelas empresas de entrega 
                $whereEmp  =  $this->getFiltroEmpresa($get,$whereTe)? $this->getFiltroEmpresa($get,$whereTe) : $whereTe;

                //Where Pelas Taxas TDE ou TDA
                $where =   $this->getFiltroTiposTaxa($get,$whereEmp)? $this->getFiltroTiposTaxa($get,$whereEmp) : $whereEmp ;


                if($this->verifica_gatilho('FILTRO_CENTRAL')){
                    
                    if(!empty($this->input->get()['CDEMPRESA'])){

                        foreach($this->input->get()['CDEMPRESA'] as $k => $cdEmp){
                            
                            if($k == 0){
                                $empresa = $cdEmp;
                            }else{
                                $empresa .= ",$cdEmp";  
                            }
                            
                        }
                        $where .= " AND CDEMPRESADESTINO in ($empresa) ";
                    }

                }else{
                    $where .= " AND CDEMPRESADESTINO = {$this->sessao['filial']} ";
                }


                if($where == "1 = 1"){
                    $this->dados['sqlPagination'] = "";
                }else{
                    $this->dados['sqlPagination'] = base64_encode($where);
                }
                
            }else{
                $where = base64_decode($sqlPagination);
                $this->dados['sqlPagination'] = base64_encode($where);
            }
            
            
            $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
            $lista   = $this->infoentregas0800_model->getAllEntregas()
                       ->setWhere($where)
                       ->paginateOracle($page,10,$order)
                       ->go();
            
            $rowcount = $this->infoentregas0800_model->getAllEntregas()
                       ->setWhere($where)
                       ->count()
                       ->go();              
            
            $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
            $this->dados['filtro'] = (!empty($get['filtro'])) ? $get['filtro'] : array();
            $this->dados['titulo'] = 'Central de Entregas';
            $this->dados['lista'] = $lista;
            $this->dados['total'] = $rowcount;
            $this->dados['filiais'] = $this->softran_oracle->empresas();
            $this->dados['FILTRO_CENTRAL'] = $this->verifica_gatilho('FILTRO_CENTRAL');
	    
            //Metodo Utilizado para relizar a paginação
            $this->dados['paginacao'] = $this->configurePagination(10, $rowcount, "cce_entregas0800/infoEntregas0800") ;

            $this->render('infoEntregas0800');

        } 
	
        /**
         * Seleciona os dados e retorna um objeto para mostar um gráfico na view  
         */
        public function graficoEntregas0800($Filtro = null){
            
            $get = $this->input->get();
            $this->dados['filtro'] = (!empty($get['filtro'])) ? $get['filtro'] : array();
            
            $dtIni = isset($get['filtro']['date']['DTINI']) ? $get['filtro']['date']['DTINI'] : date('d/m/Y'); 
            $dtFim = isset($get['filtro']['date']['DTFIM']) ? $get['filtro']['date']['DTFIM'] : date('d/m/Y'); 
            $cdEmpresa =  isset($get['cdempresa']) ? $get['cdempresa'] : $this->sessao['filial']; 
            
            //Se tiver empresa quer dizer que o filtro pode ser realizado
            if(isset($get['cdempresa'])){
                $this->dados['graficoEntregas'] =  $this->graficocentral->getDesempenhoFiliais($cdEmpresa,$dtIni,$dtFim);
                $this->dados['descEmp'] = $cdEmpresa.' - '.$this->db->query("SELECT * FROM SOFTRAN_MAGNA.SISEMPRE WHERE CDEMPRESA = $cdEmpresa")->result()[0]->DSEMPRESA;

            }else{
                $this->dados['descEmp'] = '';
                $this->dados['graficoEntregas'] = [];
            }
            $this->render('graficoEntregas0800');
        }

        

        /*
         * Retorna o CPF do funcionario para utilizar nos link de números repetidos
         * */
        private function retornaCpfFun($cdFuncionario){
            
            $QueryAux = $this->db->query("SELECT Regexp_replace(nrcpf, '[^[:digit:]]', NULL) AS cpf from SOFTRAN_MAGNA.SISFUN where CdFuncionario = {$cdFuncionario} AND NVL(FGDEMITIDO,0) = 0 AND DTAFASTAMENTO IS NULL")
                        ->result();
            if(!empty($QueryAux))
             return $QueryAux[0]
                    ->CPF;
          
        }
        
        /**
         * Metodo utilizado para verificar se já existe aquele numero para freteiro/funcionario
         * <br>
         * <br>
         * <b>WORKAROUND</b>
         * @param int $Numero Numero que sera validado
         * @param int $CnpjCpf Cpf que é a chave do usuário
         * @param int $Tipo Se é freteiro o ou funcionario
         * @return StdClass <p>Retorna uma Classe com os atributos : 
         *                      - existe Caso exista numero reptido.
         *                      - Quantidade Quantidade numeros reptidos.
         *                      - htmlAnchor - Html com links para os cadastros que estão duplicados.
         *                  <p>
         */
        private function existeNumeroCadastro($Numero,$CnpjCpf,$Tipo){
            $Retorno = null;
            if($Tipo == "funcionario"){ 
                $CdFuncionario = $this->motoristas_model->getCdFuncionario($CnpjCpf);
                $Retorno = $this->db->query("SELECT * FROM GERAL_CELULAR WHERE (NRCELULAR LIKE '%{$Numero}%'OR NRCELULAR2 LIKE '%$Numero%') and (Cdfuncionario <> {$CdFuncionario} or Cdfuncionario is null)");
            }else{
                $Retorno = $this->db->query("SELECT * 
                                                FROM GERAL_CELULAR A
                                                LEFT JOIN SOFTRAN_MAGNA.GTCFRETE B ON B.NRCNPJCPF = A.NRCPF 
                                                WHERE (A.NRCELULAR LIKE  '%$Numero%' OR A.NRCELULAR2 LIKE  '%$Numero%') 
                                                and (A.NrCpf <> '$CnpjCpf' or A.NrCpf is null)
                                                AND (B.INJURIDICAFISICA = 1 OR B.INJURIDICAFISICA IS NULL)");

            }
            $ObjRetorno = new stdClass();
            $ObjRetorno->htmlAnchor = "";
            $Tamanho  = count($Retorno->result());
            $ObjRetorno->Quantidade = $Tamanho;
            if(!empty($Retorno->result())){
                $ObjRetorno->existe = true;
                if($Tamanho > 1){ 
                    if($Tamanho <= 10) {
                        $ObjRetorno->htmlAnchor .= "Número utilizado nos seguintes cadastros:";
                        foreach ($Retorno->result() as $row){
                            if($row->NRCPF){
                               $Ret = $this->motoristas_model->getMotoristas()->setWhere("cpfcnpj = {$row->NRCPF} ")->go()[0]; 
                               $Cnpj = $Ret->CPFCNPJ;
                               $ObjRetorno->htmlAnchor .= " <br><a target='_blank' href='cce_entregas0800/editarMotorista/freteiro/{$Cnpj}'>$Ret->DSNOME</a>";
                            }else if($row->CDFUNCIONARIO){
                                $cpf = $this->retornaCpfFun($row->CDFUNCIONARIO);
                                if(isset($cpf)){
                                    $Nome = $this->motoristas_model->getMotoristas()->setWhere("cpfcnpj = {$cpf} ")->go()[0]->DSNOME;
                                    $ObjRetorno->htmlAnchor .= " <br><a target='_blank' href='cce_entregas0800/editarMotorista/funcionario/{$cpf}'>{$Nome}</a>";
                                }
                            }     
                       }
                    }else{
                       $ObjRetorno->htmlAnchor = " Foram encontrados {$Tamanho} registros com esse número.";
                    }  
                }else{
                    $ObjRetorno->htmlAnchor .= "Esse número já está sendo utilizado pelo seguinte motorista:";
                    if(isset($Retorno->result()[0]->NRCPF)){
                       $Ret = $Ret = $this->motoristas_model->getMotoristas()->setWhere("cpfcnpj = {$Retorno->result()[0]->NRCPF} ")->go()[0]; 
                       $Cnpj = $Ret->CPFCNPJ;
                       $ObjRetorno->htmlAnchor .= " <br><a target='_blank' href='cce_entregas0800/editarMotorista/freteiro/{$Cnpj}'>{$Ret->DSNOME}</a>";
                    }else{
                        $cpf = $this->retornaCpfFun($Retorno->result()[0]->CDFUNCIONARIO);
                        $Nome = $this->motoristas_model->getMotoristas()->setWhere("cpfcnpj = {$cpf} ")->go()[0]->DSNOME;
                        $ObjRetorno->htmlAnchor .= " <br><a target='_blank' href='cce_entregas0800/editarMotorista/funcionario/{$cpf}'>{$Nome}</a>";
                    }
                    
                } 
            }else{
                $ObjRetorno->existe = false;
            }  
            
            return $ObjRetorno;
        }
        
        
	public function editarMotorista($tipo, $codigo){
        $this->load->helper('phone_number');
	    $erro = false;
        if ($tipo != "funcionario" && $tipo != "freteiro"){
            return false;
        }

        $this->load->model('geral_operadora_model');
        $this->form_validation->set_rules('CPFCNPJ', 'Código Softran', 'trim|required');
        $this->form_validation->set_rules('NRCELULAR', 'Celular Empresa', 'trim|required'); 
    
        if ($this->form_validation->run()){

            $post = $this->input->post();
            $Erros = [];
            $HtmlInfo;
            $celulares = [];

            //Junta todos os telefones em um array de validação, foi desenvolvido assim caso precise adicionar mais números futuramente
            array_push($celulares,buildCellObj($post['NRCELULAR'],$post['OP_NRCELULAR']));
            if(!is_null($post['NRCELULAR2'])){
                array_push($celulares,buildCellObj($post['NRCELULAR2'],$post['OP_NRCELULAR2'],false));
            }   
            
            /*
            * Realiza as validações : 
            *  - Sem está informando a operadora
            *  - Se tem 13 Caracteres porem a operadora não é Nextel
            *  - Se tem menos que 13 Caracteres (Nextel é o mínimo com 13 caracteres) 
            *  - Remove o Parênteses "(" e ")" 
            *  - Remove os espaços
            *  - Remove o traço "-"
            *  - Verifica se os números não são iguais
            */

            if(buildCellObj($post['NRCELULAR'],$post['OP_NRCELULAR'])->telefone == buildCellObj($post['NRCELULAR2'],$post['OP_NRCELULAR2'],false)->telefone){
                array_push($Erros, "O Número primário não pode ser igual ao número secundário"); 
                $erro = true; 
                $stopOperation = true;
            }

            foreach($celulares as $cel){

                $celular = removeMaskPhone("0$cel->operadora$cel->telefone");
                
                $existsOp = opValida($cel->operadora);

                if(!opValida($cel->operadora)){
                    array_push($Erros, "O Campo operadora é obrigatório, telefone: $cel->telefone"); 
                    $erro = true; 
                }

                if(nextelInvalido($celular,$cel->operadora,$existsOp)){
                    array_push($Erros, "Verifique se está faltando nono dígito, ou o telefone é um nextel (Selecione operadora Nextel). Telefone: $cel->telefone"); 
                    $erro = true; 
                }

                if(tamanhoInvalido($celular,$existsOp)){
                    array_push($Erros, "Número inválido, verifique o tamanho do número e/ou a falta o nono dígito. Telefone: $cel->telefone"); 
                    $erro = true; 
                }

                if($existsOp){
                    $ObjetoValidate = $this->existeNumeroCadastro(substr($celular,1),$post['CPFCNPJ'],$tipo);
                    if($ObjetoValidate->existe){    
                        array_push($Erros, "{$ObjetoValidate->htmlAnchor}");   
                        $erro = true;
                    }
                }

                if ($erro && $cel->principal) {
                    $stopOperation = true;
                }
                
            }

            if (!$erro || ($erro && !isset($stopOperation))){

                foreach($celulares as $cel){
                    $nrCelular = removeMaskPhone("0$cel->operadora$cel->telefone");
                    
                    //Caso deu algum erro então foi no número secundario nesse caso só atualiza o número principal, caso contrário atualiza os dois
                    if(!$erro){
                        $this->motoristas_model->atualizaTelefoneMotorista($tipo, $post['CPFCNPJ'],$nrCelular,$cel->principal);                                
                    }else if($cel->principal){
                        $this->motoristas_model->atualizaTelefoneMotorista($tipo, $post['CPFCNPJ'],$nrCelular,$cel->principal);
                    } 

                }

                $numeroDefault = "(00) 0000-0000";
                
                //Caso dê algum erro que não seja no número principal ele altera o principal, mas avisa que deu erro em algum número
                if(!$erro){
                    $this->dados['sucesso'] = "Ambos números foram alterados com sucesso!";
                }else if($numeroDefault == $post['NRCELULAR2']){
                    $this->dados['sucesso'] = "Número principal alterado com sucesso!";
                }else{
                    $this->dados['sucesso'] = "Primeiro número alterado com sucesso, porem o segundo contem erros.";
                    $this->setErrorScreen($Erros);
                }

            }else{
                $this->setErrorScreen($Erros);
            }
        }

        if (!$erro){

            $motorista = $this->motoristas_model
                                ->getMotoristas()
                                ->setWhere("CPFCNPJ = '".$codigo."' and TIPO = '".$tipo."'")
                                ->go();

            if (!empty($motorista))
                    $motorista = $motorista[0]; //stdClass

            //Popula _POST com todos os campos do getMotoristas()
            foreach ($motorista as $campo => $valor){
                    $_POST[$campo] = $valor;
            }

            /* Remove caracteres especiais e espaços dos telefones
                * para verificar o tamanho e remover informação de operadora se já possuir */
            if (empty($_POST["NRCELULAR"])) 
                    $_POST["NRCELULAR"] = "";

            if (empty($_POST["NRCELULAR2"])) 
                $_POST["NRCELULAR2"] = "";

            $buildObj = function($num,$principal = false){
                $tel = new StdClass();
                $tel->telefone = $num;
                $tel->principal =  $principal;
                return $tel;
            };
            $arrTels = [$buildObj($_POST["NRCELULAR"],true),$buildObj($_POST["NRCELULAR2"])];

            foreach($arrTels as $tel){
                $celIndex = $tel->principal ? "NRCELULAR" : "NRCELULAR2";
                $opIndex =  $tel->principal ? "OP_NRCELULAR" : "OP_NRCELULAR2";

                $celular = preg_replace('/[^0-9]/', '', str_replace(" ", "", $_POST[$celIndex]));
                $tamanhoCelular = strlen($celular); 
                
                if($tamanhoCelular){
                    if($tamanhoCelular == 14 || $tamanhoCelular == 13){
                        $_POST[$opIndex] = substr($celular,1,2);
                        $_POST[$celIndex]  = substr($celular,3);
                    }else{
                        $_POST[$opIndex] = "0";
                        $_POST[$celIndex]  = str_pad($celular, 10, "0", STR_PAD_LEFT);
                    }
                }else{
                    $_POST[$opIndex] = "0";
                    $_POST[$celIndex] = str_pad($celular, 10, "0", STR_PAD_LEFT);
                }
            }

        }
            
        $this->dados['titulo'] = 'Editar';
        $this->dados['tipo'] = ucfirst($tipo);
        $this->dados['operadoras'] = $this->geral_operadora_model
                                          ->order_by('ID') 
                                          ->as_dropdown('DSOPERADORA')
                                          ->get_all();
        
        if($_POST["OP_NRCELULAR"] == "0" || $_POST["OP_NRCELULAR2"] == "0"){
            $this->dados['operadoras'][0] = "Selecione uma operadora";
        } 

        $this->render('editarMotorista');
    }

    function setErrorScreen($Erros){

        if(count($Erros) > 1){
            $HtmlInfo = "<b>As Seguintes informações estão divergentes :</b>";
            
            foreach ($Erros as $chave => $valor){
                $HtmlInfo .=  "<br> - {$valor}";
            }
            $this->dados['erro'] = $HtmlInfo;
        }else{
            $this->dados['erro'] = "<b>A Seguinte informação está divergente :</b> <br> - {$Erros[0]}";
        }
    }

        public function retornaGraficoPorFilal(){

            $dtIni = strpos($this->input->post('inicio'),'DD/MM/YYYY') ? str_replace("DD/MM/YYYY","'DD/MM/YYYY'",$this->input->post('inicio')) : "'".$this->input->post('inicio')."'";
            $dtFim = strpos($this->input->post('fim'),'DD/MM/YYYY') ? str_replace("DD/MM/YYYY","'DD/MM/YYYY'",$this->input->post('fim')) :  "'".$this->input->post('fim')."'";
            $cdFilial  = $this->input->post('empresa');
            echo json_encode($this->graficocentral->detalhamentoFilial($dtIni,$dtFim, $cdFilial));
        
        }

}
