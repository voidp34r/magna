<?php


class Cce_entregas0800 extends MY_Controller{
	
    public function __construct(){
		parent::__construct();
                $this->load->library('pagination');
                
		$this->dados['modulo_nome'] = 'Coleta e Entrega > Central de Entregas';
		$this->dados['modulo_menu'] = ['Entregas'   => 'infoEntregas0800',
                    '                           Motoristas' => 'listarMotoristas'];
                
		$this->load->model('motoristas_model');
                $this->load->model('infoentregas0800_model');
                
                if($this->verifica_gatilho('GRAFICOS')){
                    $this->dados['modulo_menu']['Gráficos']  = 'graficoEntregas0800';
                }
	}
	
	function index(){
		$this->redirect('cce_entregas0800/infoEntregas0800'); 
	}
	
        /**
         *  Remove os parenteses da mascara utilizada pelo cadastastro
         * @param int $Telefone
         * 
         */
        function removeMaskPhone($Telefone){
           return str_replace('-', '', str_replace("(","",(str_replace(")","",$Telefone)))) ; 
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
	    
            //Metodo Utilizado para relizar a paginação
            $this->dados['paginacao'] = $this->configurePagination(10, $rowcount, "cce_entregas0800/infoEntregas0800") ;

            $this->render('infoEntregas0800');

        } 
	
        /**
         * Seleciona os dados e retorna um objeto para mostar um gráfico na view  
         */
        public function graficoEntregas0800($Filtro = null,$tipoGrafico = null){
            
            $this->dados['filtro'] = (!empty($get['filtro'])) ? $get['filtro'] : array();
            $get = $this->input->get();
            
            //Verifica se tem filtro, caso não tenha pega os registros do mês atual
            if( (!isset($get['filtro']['date']['DTINI']) || !isset($get['filtro']['date']['DTFIM'])) ||
                (empty($get['filtro']['date']['DTINI']) || empty($get['filtro']['date']['DTFIM']))  ){
                
                if($Filtro == null || $Filtro == 'hoje'){
                    $dtIni = "(select to_char(sysdate,'DD/MM/YYYY') from dual)";
                    $dtFim = "(select to_char(sysdate,'DD/MM/YYYY') from dual)";
                }else{
                    date_default_timezone_set("Brazil/East");
                    $dtIniAux = substr(date('d/m/Y'),-7);
                    $dtIni = "'01/{$dtIniAux}'";
                    $dtFim = "(select to_char(sysdate,'DD/MM/YYYY') from dual)";
                    $this->dados['InfoData'] = "Intervalo das Datas : {$dtIni} até a data atual";                    

                }
            }else{ 
                $dtIni = "'{$get['filtro']['date']['DTINI']}'";
                $dtFim = "'{$get['filtro']['date']['DTFIM']}'";
                $this->dados['InfoData'] = "Intervalo das Datas: {$dtIni} até {$dtFim}";
            }
            
          if($tipoGrafico){
             $this->dados['tipoGrafico']  = $tipoGrafico;
             $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
             $query =  $this->retornaSqlGrafico($dtIni, $dtFim,$tipoGrafico);  
             $this->dados['graficoEntregas']  = $this->db->query($query)->result();
          }else{
             $this->dados['graficoEntregas']  = array(); 
             $this->dados['tipoGrafico']  = '';
          }
          
          $this->render('graficoEntregas0800');
        }


        function retornaSqlGrafico($dtIni,$dtFim,$tipo){
            
            if($tipo == 'romaneio'){
                return  "SELECT
                        SUM(NVL(QUANTIDADECENTRALCERTA,0)) AS QUANTIDADECENTRALCERTA,
                        SUM(NVL(QUANTIDADECENTRALERRADA,0)) AS QUANTIDADECENTRALERRADA,
                        SUM(NVL(QUANTIDADEMOVIMENTOSOFTRAN,0)) AS QUANTIDADEMOVIMENTOSOFTRAN ,
                        SUM(NVL(QUANTIDADEROMANABERTO,0)) QUANTIDADEROMANABERTO,
                        SUM(NVL(QUANTIDADEDIA,0)) as QUANTIDADEDIA, 
                        DSEMPRESA,
                        CODIGOEMPRESA
                        FROM(
                          SELECT 
                          COUNT (*) AS QUANTIDADECENTRALCERTA,
                          NULL AS QUANTIDADECENTRALERRADA,
                          NULL AS QUANTIDADEMOVIMENTOSOFTRAN, 
                          NULL AS QUANTIDADEROMANABERTO,
                          NULL AS QUANTIDADEDIA,
                          E.DSEMPRESA AS DSEMPRESA,
                          E.CDEMPRESA AS CODIGOEMPRESA
                          FROM SOFTRAN_MAGNA.CCEROMAN A
                          LEFT JOIN SOFTRAN_MAGNA.CCEROMIT B ON B.CDEMPRESA = A.CDEMPRESA AND B.CDROTA = A.CDROTA AND B.CDROMANEIO = A.CDROMANEIO
                          LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON C.NRSEQCONTROLE = B.NRSEQCONTROLE AND C.CDEMPRESA = B.CDEMPRESACOLETAENTREGA
                          RIGHT JOIN ENTREGA_0800INFO D ON D.CDFILIALORIGEM = C.CDEMPRESA AND D.NRDOCTOFISCAL = C.NRDOCTOFISCAL
                          LEFT JOIN SOFTRAN_MAGNA.SISEMPRE E ON E.CDEMPRESA = B.CDEMPRESA 
                          WHERE TRUNC(A.DTROMANEIO) BETWEEN {$dtIni} AND {$dtFim}
                          AND Trunc(To_date(D.dtini, 'YYYYMMDDHH24MISS')) >= {$dtIni}   AND Trunc(To_date(D.dtfim, 'YYYYMMDDHH24MISS')) <= {$dtFim}
                          AND NVL(A.INSITUACAO,0) <> 1
                          AND NVL(B.INSITUACAO,0) <> 1
                          AND C.CDTPDOCTOFISCAL IN (3,5)
                          AND C.DTEMISSAO  = (SELECT MAX(ZZ.DTEMISSAO) 
                                              FROM SOFTRAN_MAGNA.GTCCONHE ZZ 
                                              WHERE ZZ.NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                              AND ZZ.CDEMPRESA = C.CDEMPRESA)
                          AND To_char(Extract(hour FROM Numtodsinterval((To_date(D.dtfim, 'YYYYMMDDHH24MISS') 
                            - 
                            To_date(D.dtini,'YYYYMMDDHH24MISS')), 'DAY')), 'FM00') 
                              || ':' 
                              || To_char(Extract(minute FROM Numtodsinterval((To_date(D.dtfim, 'YYYYMMDDHH24MISS') - 
                                                To_date(D.dtini,'YYYYMMDDHH24MISS')), 'DAY')), 'FM00') BETWEEN '00:06' AND '100:00' 
                        GROUP BY E.CDEMPRESA,E.DSEMPRESA
                        UNION ALL
                        SELECT 
                        NULL AS QUANTIDADECENTRALCERTA,
                        COUNT(*) AS QUANTIDADECENTRALERRADA,
                        NULL AS QUANTIDADEMOVIMENTOSOFTRAN,
                        NULL AS QUANTIDADEROMANABERTO,
                        NULL AS QUANTIDADEDIA,
                        E.DSEMPRESA AS DSEMPRESA,
                        E.CDEMPRESA AS CODIGOEMPRESA
                        FROM SOFTRAN_MAGNA.CCEROMAN A
                        LEFT JOIN SOFTRAN_MAGNA.CCEROMIT B ON B.CDEMPRESA = A.CDEMPRESA AND B.CDROTA = A.CDROTA AND B.CDROMANEIO = A.CDROMANEIO
                        LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON C.NRSEQCONTROLE = B.NRSEQCONTROLE AND C.CDEMPRESA = B.CDEMPRESACOLETAENTREGA
                        RIGHT JOIN ENTREGA_0800INFO D ON D.CDFILIALORIGEM = C.CDEMPRESA AND D.NRDOCTOFISCAL = C.NRDOCTOFISCAL
                        LEFT JOIN SOFTRAN_MAGNA.SISEMPRE E ON E.CDEMPRESA = B.CDEMPRESA 
                        WHERE TRUNC(A.DTROMANEIO) BETWEEN {$dtIni} AND {$dtFim}
                        AND Trunc(To_date(D.dtini, 'YYYYMMDDHH24MISS')) >= {$dtIni}   AND Trunc(To_date(D.dtfim, 'YYYYMMDDHH24MISS')) <= {$dtFim}  
                        AND NVL(A.INSITUACAO,0) <> 1
                        AND NVL(B.INSITUACAO,0) <> 1
                        AND C.CDTPDOCTOFISCAL IN (3,5)
                        AND C.DTEMISSAO  = (SELECT MAX(ZZ.DTEMISSAO) 
                                            FROM SOFTRAN_MAGNA.GTCCONHE ZZ 
                                            WHERE ZZ.NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                            AND ZZ.CDEMPRESA = C.CDEMPRESA)

                        -- Compara tempo
                        AND To_char(Extract(hour FROM Numtodsinterval((To_date(D.dtfim, 'YYYYMMDDHH24MISS') 
                                - 
                                To_date(D.dtini,'YYYYMMDDHH24MISS')), 'DAY')), 'FM00') 
                            || ':' 
                            || To_char(Extract(minute FROM Numtodsinterval((To_date(D.dtfim, 'YYYYMMDDHH24MISS') - 
                                              To_date(D.dtini,'YYYYMMDDHH24MISS')), 'DAY')), 'FM00') BETWEEN '00:00' AND '00:05' 
                        GROUP BY E.CDEMPRESA,E.DSEMPRESA
                        UNION ALL 
                        SELECT 
                        NULL AS QUANTIDADECENTRALCERTA,
                        NULL AS QUANTIDADECENTRALERRADA,
                        COUNT(*) AS QUANTIDADEMOVIMENTOSOFTRAN,
                        NULL AS QUANTIDADEROMANABERTO,
                        NULL AS QUANTIDADEDIA,
                        E.DSEMPRESA AS DSEMPRESA,
                        E.CDEMPRESA AS CODIGOEMPRESA
                        FROM SOFTRAN_MAGNA.CCEROMAN A 
                        LEFT JOIN SOFTRAN_MAGNA.CCEROMIT B ON B.CDEMPRESA = A.CDEMPRESA AND B.CDROTA = A.CDROTA AND B.CDROMANEIO = A.CDROMANEIO
                        LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON C.NRSEQCONTROLE = B.NRSEQCONTROLE AND C.CDEMPRESA = B.CDEMPRESACOLETAENTREGA
                        RIGHT JOIN SOFTRAN_MAGNA.GTCMOVEN D ON D.NRSEQCONTROLE = C.NRSEQCONTROLE AND D.CDEMPRESA = C.CDEMPRESA 
                        LEFT JOIN SOFTRAN_MAGNA.SISEMPRE E ON E.CDEMPRESA = B.CDEMPRESA
                        WHERE TRUNC(A.DTROMANEIO) BETWEEN {$dtIni} AND {$dtFim}
                        --Valida se não foi cancelado o romaneio
                        AND NVL(A.INSITUACAO,0) <>  1
                        AND NVL(B.INSITUACAO,0) <>  1
                        -- Ocorrencia 1 - Somente baixadas 
                        AND D.CDOCORRENCIA =  1
                        -- Documentos 5 e 3 - Conhecimento e Nota Fiscal de serviço 
                        AND C.CDTPDOCTOFISCAL IN (3,5)
                        -- Pega o ultimo documento casa tenha dois documentos para a mesma empresa, exemplo nota fiscal de serviço e cte 
                        AND C.DTEMISSAO  = (SELECT MAX(ZZ.DTEMISSAO) 
                                            FROM SOFTRAN_MAGNA.GTCCONHE ZZ 
                                            WHERE ZZ.NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                            AND ZZ.CDEMPRESA = C.CDEMPRESA)
                        -- Não existir movimento na central de entregas
                        AND NOT EXISTS (SELECT *  
                                        FROM   entrega_0800info XX 
                                        WHERE  XX.cdfilialorigem = C.cdempresa 
                                        AND XX.nrdoctofiscal = C.nrdoctofiscal 
                                        AND XX.cdultimaocorrencia IN ( 3, 5 )) 
                        AND TRUNC(D.DTDIGITACAO) BETWEEN {$dtIni} AND {$dtFim}                
                        GROUP BY E.CDEMPRESA,E.DSEMPRESA
                        UNION ALL 
                        SELECT 
                        NULL  AS QUANTIDADECENTRALCERTA,
                        NULL AS QUANTIDADECENTRALERRADA,
                        NULL AS QUANTIDADEMOVIMENTOSOFTRAN,
                        COUNT(*) AS QUANTIDADEROMANABERTO,
                        NULL AS QUANTIDADEDIA,
                        E.DSEMPRESA AS DSEMPRESA,
                        E.CDEMPRESA AS CODIGOEMPRESA
                        FROM SOFTRAN_MAGNA.CCEROMAN A 
                        LEFT JOIN SOFTRAN_MAGNA.CCEROMIT B ON B.CDEMPRESA = A.CDEMPRESA AND B.CDROTA = A.CDROTA AND B.CDROMANEIO = A.CDROMANEIO
                        LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON C.NRSEQCONTROLE = B.NRSEQCONTROLE AND C.CDEMPRESA = B.CDEMPRESACOLETAENTREGA
                        LEFT JOIN SOFTRAN_MAGNA.SISEMPRE E ON E.CDEMPRESA = B.CDEMPRESA 
                        WHERE TRUNC(A.DTROMANEIO) BETWEEN {$dtIni} AND {$dtFim}

                        --Valida se não foi cancelado o romaneio
                        AND NVL(A.INSITUACAO,0) <>  1
                        AND NVL(B.INSITUACAO,0) <>  1

                        -- Documentos 5 e 3 - Conhecimento e Nota Fiscal de serviço 
                        AND C.CDTPDOCTOFISCAL IN (3,5)

                        -- Pega o ultimo documento casa tenha dois documentos para a mesma empresa, exemplo nota fiscal de serviço e cte 
                        AND C.DTEMISSAO  = (SELECT MAX(ZZ.DTEMISSAO)  
                                            FROM SOFTRAN_MAGNA.GTCCONHE ZZ 
                                            WHERE ZZ.NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                            AND ZZ.CDEMPRESA = C.CDEMPRESA)

                        --Valida se todos os documentos não foram baixados                  
                        AND NOT EXISTS (SELECT * 
                                        FROM SOFTRAN_MAGNA.GTCMOVEN ZZ  
                                        WHERE ZZ.NRSEQCONTROLE = C.NRSEQCONTROLE 
                                        AND ZZ.CDEMPRESA = C.CDEMPRESA 
                                        AND ZZ.CDOCORRENCIA =  1)                    
                        -- Não existir movimento na central de entregas
                        AND NOT EXISTS (SELECT *  
                                        FROM   entrega_0800info XX 
                                        WHERE  XX.cdfilialorigem = C.cdempresa 
                                        AND XX.nrdoctofiscal = C.nrdoctofiscal 
                                        AND XX.cdultimaocorrencia IN ( 3, 5 )) 
                        GROUP BY E.CDEMPRESA,E.DSEMPRESA
                        UNION ALL
                        SELECT 
                        NULL  AS QUANTIDADECENTRALCERTA,
                        NULL AS QUANTIDADECENTRALERRADA,
                        NULL AS QUANTIDADEMOVIMENTOSOFTRAN,
                        NULL AS QUANTIDADEROMANABERTO,
                        COUNT(*) AS QUANTIDADEDIA,
                        E.DSEMPRESA AS DSEMPRESA,
                        E.CDEMPRESA AS CODIGOEMPRESA
                        FROM SOFTRAN_MAGNA.CCEROMAN A 
                        LEFT JOIN SOFTRAN_MAGNA.CCEROMIT B ON B.CDEMPRESA = A.CDEMPRESA AND B.CDROTA = A.CDROTA AND B.CDROMANEIO = A.CDROMANEIO
                        LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON C.NRSEQCONTROLE = B.NRSEQCONTROLE AND C.CDEMPRESA = B.CDEMPRESACOLETAENTREGA
                        LEFT JOIN SOFTRAN_MAGNA.SISEMPRE E ON E.CDEMPRESA = B.CDEMPRESA 
                        WHERE TRUNC(A.DTROMANEIO) BETWEEN {$dtIni} AND {$dtFim}

                        --Valida se não foi cancelado o romaneio
                        AND NVL(A.INSITUACAO,0) <>  1
                        AND NVL(B.INSITUACAO,0) <>  1

                        -- Documentos 5 e 3 - Conhecimento e Nota Fiscal de serviço 
                        AND C.CDTPDOCTOFISCAL IN (3,5)

                        -- Pega o ultimo documento casa tenha dois documentos para a mesma empresa, exemplo nota fiscal de serviço e cte 
                        AND C.DTEMISSAO  = (SELECT MAX(ZZ.DTEMISSAO)  
                                            FROM SOFTRAN_MAGNA.GTCCONHE ZZ 
                                            WHERE ZZ.NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                            AND ZZ.CDEMPRESA = C.CDEMPRESA) 

                        GROUP BY E.CDEMPRESA,E.DSEMPRESA
                        ) 
                        GROUP BY DSEMPRESA,CODIGOEMPRESA ORDER BY QUANTIDADECENTRALCERTA DESC";
                    
            }else{ 
                    return "SELECT
                            SUM(NVL(QUANTIDADECENTRAL,0)) AS QUANTIDADECENTRAL,
                            SUM(NVL(QUANTIDADESOFTRAN,0)) AS QUANTIDADESOFTRAN,
                            CDEMPRESADESTINO,
                            DSEMPRESA
                            FROM
                            (SELECT
                            count(*) as QUANTIDADECENTRAL,
                            NULL AS QUANTIDADESOFTRAN,
                            B.CDEMPRESADESTINO,
                            C.DSEMPRESA
                            FROM ENTREGA_0800INFO A
                            LEFT JOIN SOFTRAN_MAGNA.GTCCONHE B ON B.CDEMPRESA = A.CDFILIALORIGEM AND B.NRDOCTOFISCAL = A.NRDOCTOFISCAL
                            LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C ON C.CDEMPRESA = B.CDEMPRESADESTINO
                            WHERE     Trunc(To_date(a.dtini, 'YYYYMMDDHH24MISS')) >= {$dtIni}  AND Trunc(To_date(a.dtfim, 'YYYYMMDDHH24MISS')) <= {$dtFim}
                            AND B.CDTPDOCTOFISCAL IN (3,5)
                            and B.dtemissao = (SELECT Max(ZZ.dtemissao) 
                                                FROM   softran_magna.gtcconhe ZZ 
                                                WHERE  ZZ.nrdoctofiscal = B.nrdoctofiscal 
                                                       AND ZZ.cdempresa = B.cdempresa 
                                                       AND B.cdtpdoctofiscal IN ( 3, 5 ))
                            GROUP BY B.CDEMPRESADESTINO,C.DSEMPRESA
                            UNION ALL
                            SELECT 
                            null as QUANTIDADECENTRAL,
                            COUNT(*) AS QUANTIDADESOFTRAN,
                            B.CDEMPRESADESTINO,
                            C.DSEMPRESA
                            FROM SOFTRAN_MAGNA.GTCMOVEN A
                            LEFT JOIN SOFTRAN_MAGNA.GTCCONHE B ON B.NRSEQCONTROLE = A.NRSEQCONTROLE AND B.CDEMPRESA = A.CDEMPRESA 
                            LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C ON C.CDEMPRESA = B.CDEMPRESADESTINO
                            WHERE A.CDOCORRENCIA = 1 
                            AND TRUNC(A.DTMOVIMENTO) BETWEEN {$dtIni} AND {$dtFim}
                            AND B.CDTPDOCTOFISCAL IN (3,5)
                            AND NOT EXISTS (SELECT * FROM ENTREGA_0800INFO D WHERE D.CDFILIALORIGEM = B.CDEMPRESA AND D.NRDOCTOFISCAL = B.NRDOCTOFISCAL)
                            and B.dtemissao = (SELECT Max(ZZ.dtemissao) 
                                                FROM   softran_magna.gtcconhe ZZ 
                                                WHERE  ZZ.nrdoctofiscal = B.nrdoctofiscal 
                                                       AND ZZ.cdempresa = B.cdempresa 
                                                       AND B.cdtpdoctofiscal IN ( 3, 5 ))
                            GROUP BY B.CDEMPRESADESTINO,C.DSEMPRESA)
                            GROUP BY CDEMPRESADESTINO,DSEMPRESA
                            ORDER BY QUANTIDADECENTRAL DESC";
            }
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
                $Retorno = $this->db->query("SELECT * FROM GERAL_CELULAR WHERE NRCELULAR LIKE  '%{$Numero}%' and (Cdfuncionario <> {$CdFuncionario} or Cdfuncionario is null)");
            }else{
                $Retorno = $this->db->query("SELECT * FROM GERAL_CELULAR WHERE NRCELULAR LIKE  '%{$Numero}%' and (NrCpf <> {$CnpjCpf} or NrCpf is null)");
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
        
        
         /**
	 * Tela para editar um motorista do cadastro da softran
	 * @param int $id
	 */
	public function editarMotorista($tipo, $codigo){
	   $erro = false;
		
            if ($tipo != "funcionario" && $tipo != "freteiro")
                    return false;
		
            $this->load->model('geral_operadora_model');
            $this->form_validation->set_rules('CPFCNPJ', 'Código Softran', 'trim|required');
            $this->form_validation->set_rules('NRCELULAR', 'Celular Empresa', 'trim|required'); 
 		
            if ($this->form_validation->run()){

                $post = $this->input->post();

                /*
                * Antes de realizar as validações, deixado só o numero : 
                *  - Remove o Parênteses "(" e ")" 
                *  - Remove os espaços
                *  - Remove o traço "-"  
                */
                
                $celNumber  = $this->removeMaskPhone("0{$post['OP_NRCELULAR']}{$post['NRCELULAR']}");
                $celular = str_replace(' ', '', $celNumber);
                $Operadora = false;
                $Erros = [];
                $HtmlInfo;
                
                /*
                 * Realiza as validações : 
                 *  - Sem está informando a operadora
                 *  - Se tem 13 Caracteres porem a operadora não é Nextel
                 *  - Se tem menos que 13 Caracteres (Nextel é o mínimo com 13 caracteres) 
                 */
                if($post['OP_NRCELULAR'] == 0){
                    array_push($Erros, 'O Campo operadora é obrigatório.');
                    $erro = true;
                    $Operadora = false;
                }else{
                    $Operadora = true;
                }   
                
                if(strlen($celular) == 13 && $post['OP_NRCELULAR'] != 99 && $Operadora ){
                    array_push($Erros, 'Verifique se está faltando nono dígito, ou o telefone é um nextel (Selecione operadora Nextel).');
                    $erro = true;
                }

                if(strlen($celular) < 13 && $Operadora){
                    array_push($Erros, 'Número inválido, verifique o tamanho do número e/ou a falta o nono dígito.');
                    $erro = true;                        
                }
                if($Operadora){
                    $ObjetoValidate = $this->existeNumeroCadastro(substr($celular,3),$post['CPFCNPJ'],$tipo);
                    if($ObjetoValidate->existe){

                        array_push($Erros, "{$ObjetoValidate->htmlAnchor}");   
                        $erro = true;
                    }
                    
                }
                    

                    if (!$erro){
                            $post['NRCELULAR'] = $celular;
                            $update = $this->motoristas_model->updateTelefonesMotorista($post, $tipo);

                            if ($update){
                                    $this->redirect(

                                                    'cce_entregas0800/listarMotoristas',
                                                    'sucesso',
                                                    'Cadastro do Motorista atualizado no Softran com sucesso!');
                            } else {
                                    $this->dados['erro'] = 'Erro ao gravar dados do motorista no Softran';
                                    $erro = true;
                            }
                    }else{
                        
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
                    if (is_null($_POST["NRCELULAR"])) 
                            $_POST["NRCELULAR"] = "";



                    $celular = preg_replace('/[^0-9]/', '', str_replace(" ", "", $_POST["NRCELULAR"]));
                    $tamanhoCelular = strlen($celular); 
                    
                    if($tamanhoCelular){
                        
                        if($tamanhoCelular == 14 || $tamanhoCelular == 13){
                            $_POST["OP_NRCELULAR"] = substr($celular,1,2);
                            $_POST["NRCELULAR"]  = substr($celular,3);
                        }else{
                            $_POST["OP_NRCELULAR"] = "0";
                            $_POST["NRCELULAR"]  = str_pad($celular, 10, "0", STR_PAD_LEFT);
                        }
                        
                    }else{
                        $_POST["OP_NRCELULAR"] = "0";
                        $_POST["NR_CELULAR"] = str_pad($celular, 10, "0", STR_PAD_LEFT);
                    }
            }

            
            $this->dados['titulo'] = 'Editar';
            $this->dados['tipo'] = ucfirst($tipo);
            $this->dados['operadoras'] = $this->geral_operadora_model
                                        ->order_by('ID') 
                                        ->as_dropdown('DSOPERADORA')
                                        ->get_all();
           
           if($_POST["OP_NRCELULAR"] == "0")  
             $this->dados['operadoras'][0] = "Selecione uma operadora";

            $this->render('editarMotorista');
    }
}
