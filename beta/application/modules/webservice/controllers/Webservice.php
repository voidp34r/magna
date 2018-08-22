<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';


class Webservice extends REST_Controller {
    var $CI;
	
	const RESPONSE_OK    = 'response';
	const RESPONSE_ERROR = 'error';
	const OK_FINALIZE = 0;
	const OK_CONTINUE = 1;
	const METODO_LISTA_ENTREGAS = 'listaDocsMotorista';
	const METODO_OCORR_ENTREGAS = 'ocorrenciaEntrega';
    const DIRETORIOARQUIVOS = '/var/www/webapp/uploads/checklist';
	
	protected $dtStart;
	protected $_out_args;
	protected $log;
        
	public function __construct(){
        parent::__construct('rest');
        
        $this->CI = &get_instance();
		
		//Gera log
		log_message('debug', 'Webservice: '.print_r($this->_args, true));
		
		//Dados para a tela do módulo Webservice 
		$this->dados['modulo_nome'] = 'TI > Webservices';
		$this->dados['modulo_menu'] = ['Status' => 'status'];
		$this->dados['titulo'] = 'Webservice Entregas 0800';
		//Log banco
		$this->load->model('Ws_log_model');
        $this->load->model('frotas_portaria/portaria_checklist_foto_model');
        $this->load->model('frotas_portaria/portaria_checklist_model');
                
                
        $this->load->library('SoftranMobileLibrary');
        $this->load->library('My_PHPMailer');
                
		$log_args = [
			'post_args'=>$this->_post_args,
			'session'=>$this->sessao
		];
		date_default_timezone_set("Brazil/East");
		$this->Ws_log_model
			->setDsRequisicao(print_r($log_args, true))
			->commit();			
	}
	
	public function __destruct(){
		//Log banco
		if (isset($this->_out_args[Webservice::RESPONSE_ERROR])){
			$this->Ws_log_model
				->setCdRetorno(0) //false
				->setDsRetorno($this->_out_args[Webservice::RESPONSE_ERROR]);
			
		} else if (isset($this->_out_args[Webservice::RESPONSE_OK])) {
			$this->Ws_log_model
				->setCdRetorno(1)//true
				->setDsRetorno($this->_out_args[Webservice::RESPONSE_OK]);
		}
		date_default_timezone_set("Brazil/East");
		$this->Ws_log_model->commit();
	
		//Log arquivo
		log_message('debug', 'Webservice: _out_args: '.print_r($this->_out_args, true));
	
		parent::__destruct();
	}
	
        
        /**
         * Método utilizado para retorna todas as noticias para mobile
         *  
         * */
        public function noticiasMobile_post(){
            
            $this->load->model('ti_noticia/sistema_noticias');
            $where = "FLMOBILE = 0 OR FLMOBILE = 1";
            $retorno =  $this->sistema_noticias
                        ->where($where, NULL, NULL,FALSE, FALSE,TRUE)
                        ->order_by(array('DTNOTICIA' => 'DESC'))
                        ->get_all();
            
            echo json_encode($retorno);
            
        }
        
	/** POST listaDocsMotorista 
	 *  Listagem de motoristas - Método da URAa
	 * 
	 * Usado para listar documentos vinculados à um motorista pelo telefone dele.
	 * Retorna a lista de documentos disponíveis e quais as opções de apontamento 
	 * de ocorrência de entrega estarão disponíveis para cada documento. */
	public function listaDocsMotorista_post(){		
		$this->Ws_log_model->setDsMetodo("Consulta de motorista");
		$this->load->library('SoftranEntregasDB');
		$this->load->model('motoristas_model');
		
		$post = [
            'telefone' => $this->post('telefone'),
			'hash'     => $this->post('hash'),
			'token'    => $this->post('token')
		];
		$this->validaCamposEsperados($post);
		$this->Ws_log_model
		      ->setDescricao("Telefone ".$this->post('telefone'))
		      ->commit();
			
		//Monta where para buscar um motorista pelo celular cadastrado
		//Utiliza expressão regular para remover tudo que não for dígito. 
		$telefone = preg_replace('/[^[:digit:]]/', null, $post['telefone']);
		
                $whereMotorista = "   SUBSTR(regexp_replace(NRCELULAR, '[^[:digit:]]', null),4,11) LIKE '".$telefone."'  OR 
                                      SUBSTR(regexp_replace(NRCELULAR2, '[^[:digit:]]', null),4,11) LIKE '".$telefone."'  
                                  ";
		
        $motoristas = $this->motoristas_model
                            ->getMotoristas()
                            ->setWhere($whereMotorista)
                            ->go();
		


		//Valida motoristas encontrados pelo número do celular
		if (sizeof($motoristas) == 1 && !is_null($motoristas[0]->CPFCNPJ)){
            $operadora = substr($motoristas[0]->NRCELULAR,1,2);
			//Array de parâmetros que serão retornados
			$params = ['operadora' => $operadora]; //Operadora

			//Array de documentos do motorista
			$docs = $this->softranentregasdb->getCtesDoMotorista(str_pad($motoristas[0]->CPFCNPJ, 14, "0", STR_PAD_LEFT));
			
			//Gera array as informações de cada documento conforme documentação do webservice.
			foreach ($docs as &$res){
				$res = [
					"nrDocto" => $res->NRDOCTO,
					"empresa" => $res->EMPRESA,
                    "OPCOES"  => $res->OPCOES,
                    "DESTINATARIO" => $res->DESTINATARIO,
                    "REMETENTE" => $res->REMETENTE,
                    "VLFRETEVALOR" => "-",
                    "QTVOLUME" => $res->QTVOLUME
				];
			}
			
			//Monta JSON com documentos encontrados e parâmetros
			//Caso não tenha encontrado nenhum documento, retorna OK também mas com o response = self::OK_FINALIZE 
			$this->_out_args = $this->getListaDocsJson($params, $docs);
			$this->response($this->_out_args, REST_Controller::HTTP_OK);
			
		} else if (sizeof($motoristas) > 1){
			$this->_out_args = 'Numero '.$post['telefone'].' duplicado no cadastro da softran';
			$this->responseError($this->_out_args, REST_Controller::HTTP_OK);
			
		} else {
			$this->_out_args = 'Numero '.$post['telefone'].' nao encontrado';
			$this->responseError($this->_out_args, REST_Controller::HTTP_OK);
		}
	}
	
	/* POST ocorrenciaEntrega
	 * Listagem de motoristas - Método da URA
	 *
	 * Projeto Entrega 0800, utilizado para apontar uma ocorrência de entrega.
	 * Após o motorista selecionar a opção referente ao apontamento que deseja fazer, a central
	 * fará uma chamada a este método passando a opção e o cte informado para inclusão da ocorrência
	 * no banco de dados da softran. */
	public function ocorrenciaEntrega_post(){
		$this->load->model('cce_entregas0800/infoentregas0800_model');//Carrega Model
                $this->Ws_log_model->setDsMetodo("Ocorrência de entrega");
		$post = [
			'cdempresa' => $this->post('cdempresa'),
			'nrdoctofiscal' => $this->post('nrdoctofiscal'),
			'status'   => $this->post('status'),
			'hash'    => $this->post('hash'),
			'token'   => $this->post('token')
		];
		$this->validaCamposEsperados($post);
		
		$this->Ws_log_model->setDescricao(
			"CT-e: ".$post['nrdoctofiscal'].
			', Empresa: '.$post['cdempresa'].
			', Opção: '.$post['status']
		);

		$query = "SELECT NRSEQCONTROLE, CDEMPRESA 
				    FROM SOFTRAN_MAGNA.GTCCONHE 
			       WHERE NRDOCTOFISCAL = ? 
				     AND CDEMPRESA = ?
				     AND CDTPDOCTOFISCAL in (5,3)
                                     AND DTENTREGA IS NULL
                                     AND DTCANCELAMENTO IS NULL";
	
		$con = $this->db
			->query($query,[$post['nrdoctofiscal'], $post['cdempresa']])
			->result();
                
                //Não está passando o documento como parametro, pois no momento o 0800 só vai funcionar para documento do Tipo 3 - Cte
                $QueryMotorista = $this->getCpfMotorista($post['cdempresa'],$con[0]->NRSEQCONTROLE);
                
                $CpfMotorista = $QueryMotorista[0]->NRCPFMOTORISTA;      
                
                //Busca o nome do motorista da viagem
                $NomeMotorista = ($QueryMotorista[0]->DSNOME ? $QueryMotorista[0]->DSNOME : "-");
                
                //Busca Destinario do Cte
                $NomeEntidade = $this->getNomeClienteCte($post['cdempresa'],$post['nrdoctofiscal']);
               
                
                /**
                * Função aninhada utilizada para retornar resposta ao lançar as ocorrências.
                * @author Tiago Hélio de Borba<ti03.mtz@transmagna.com.br>  
                */
                function sendResponse($Array,$ref){
                    $Erro = null;
                    //Caso for 1 Movimentou apenas Entrega do WebApp, Caso 2 Movimentou Softran e Webapp
                    if(count($Array) == 1){
                        $ref->response([$Array[0]['type'] => $Array[0]['mensagem']],$Array[0]['httpcode']);
                    }else{
                        /*Caso Tenha dois ou mais, ira percorrer todos e verificar se existe algum procedimento que ocorreu erro.
                        / caso sim, envia a resposta de erro, caso contrario envia a com sucesso*/
                        for($I = 0 ; $I < count($Array);$I++ ){
                            if(!$Array[$I]['status']){
                                $Erro = true;
                                $ref->response([$Array[$I]['type'] => $Array[$I]['mensagem']],$Array[$I]['httpcode']); 
                            }
                        }
                        if(!$Erro){
                            $ref->response([$Array[0]['type'] => $Array[0]['mensagem']],$Array[0]['httpcode']);
                        }
                        
                    }
                }
                
                /**
                 * Função aninhada que movimenta a GTCCONHE da SOFTRAN, 
                 * Atualmente seta a OCorrência(CDOCORRENCIA) 67 - FinsEstátistico 
                 * 
                 * @author Tiago Hélio de Borba<ti03.mtz@transmagna.com.br>
                 * 
                 */
                function movimentaConhecimentoSoftran($CdOcorrencia = 1,$con,$post,$CpfMotorista,$NomeMotorista,$ref){
                    
                    if (sizeof($con) == 1){
                        
                        //Verifica ocorrências anteriores
                        $query = "SELECT NVL(MAX(CDSEQUENCIA), 0)+1 AS CDSEQUENCIA
                                            FROM SOFTRAN_MAGNA.GTCMOVEN 
                                        WHERE NRSEQCONTROLE = ? 
                                            AND CDEMPRESA = ?";

                        $cdSequencia = $ref->db
                                    ->query($query,[$con[0]->NRSEQCONTROLE, $con[0]->CDEMPRESA])
                                    ->result()[0]
                                    ->CDSEQUENCIA;

                        //Insere o movimento de entrega
                        if ($cdSequencia > 0 && !is_null($con[0]->NRSEQCONTROLE))
                        {

                            //Começa uma Transação, caso de algum erro no meio do caminho será realizado o rollback
                            $ref->db->trans_begin();
                            
                            //Alterar o formato da Data
                            $ref->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                            $querySoftran =  $ref->db->query("SELECT TO_CHAR(sysdate, 'HH24:MI') as HORA FROM dual ")
                                                ->result()[0];

                            $Hora =  "30/12/1899 {$querySoftran->HORA}" ;

                            //Momento que insere o movimento, lança a ocorrência 67 - Fins estátisticos
                            $query = "INSERT INTO SOFTRAN_MAGNA.GTCMOVEN (  
                                                    CDEMPRESA, NRSEQCONTROLE, CDSEQUENCIA, DTMOVIMENTO, HRMOVIMENTO, CDOCORRENCIA,DSUSUARIO,DsComplementoOcorr,
                                                    DtCadastro,DtDigitacao
                                                ) VALUES (
                                                    ".$post['cdempresa'].",
                                                    ".$con[0]->NRSEQCONTROLE.",
                                                    ".$cdSequencia.",
                                                    to_char(SYSDATE,'DD/MM/YYYY'),
                                                    to_date('{$Hora}','DD/MM/YYYY HH24:MI'),
                                                    {$CdOcorrencia},
                                                    {$CpfMotorista},
                                                    'Baixa efetuada via 0800, Motorista: {$NomeMotorista}',
                                                    SYSDATE,
                                                    SYSDATE)";  

                            $res = $ref->db->query($query);


                            
                            $nrDiasEntrega = $ref->db->query("SELECT SOFTRAN_MAGNA.SP_CALCDIASSEMDTENTREGA({$con[0]->CDEMPRESA},{$con[0]->NRSEQCONTROLE},to_char(SYSDATE,'DD/MM/YYYY')) 
                                                                AS NRDIASENTREGA 
                                                                    from SOFTRAN_MAGNA.EXPCFG")->result()[0]->NRDIASENTREGA;


                            //Atualiza a tabela do Conhecimento
                            $ref->db->query("UPDATE SOFTRAN_MAGNA.GTCCONHE 
                                                SET DtEntrega = (select to_char(SYSDATE,'DD/MM/YYYY') from dual),
                                                    NrDiasEntrega = $nrDiasEntrega,
                                                    CdSituacaoCarga = 1     
                                                WHERE CdEmpresa = {$con[0]->CDEMPRESA} and NrSeqControle = {$con[0]->NRSEQCONTROLE} ");

                            $ref->db->query("UPDATE SOFTRAN_MAGNA.GTCCONIA 
                                                set CdEmpresaEntrega = 0 
                                            Where CdEmpresa = {$con[0]->CDEMPRESA} 
                                            and NrSeqControle = {$con[0]->NRSEQCONTROLE}");

                            //Verifica se existe romaneio vinculado, caso exista atualiza também o status da vinculação
                            $existeRoman = $ref->db->query("SELECT * FROM SOFTRAN_MAGNA.CCEROMIT 
                                                            WHERE CdEmpresaColetaEntrega = {$con[0]->CDEMPRESA} 
                                                            And NrSeqControle = {$con[0]->NRSEQCONTROLE} ")->num_rows() > 0 ? true : false;

                            if($existeRoman){
                                $ref->db->query(" UPDATE SOFTRAN_MAGNA.CCEROMIT 
                                                    SET InSituacao = 2, CdSequenciaColetaEntrega = $cdSequencia 
                                                    Where CdEmpresaColetaEntrega = {$con[0]->CDEMPRESA}  
                                                    AND NrSeqControle = {$con[0]->NRSEQCONTROLE} 
                                                    AND (InSituacao = 0 or inSituacao is null)  
                                                    AND InColetaEntrega = 1  "
                                                );
                            }
                            

                           /* 
                            //Busca as notas fiscais
                            $arrNotas = $ref->db->query("SELECT NRNOTAFISCAL,NRSERIE,CDINSCRICAO,CDEMPRESA,NRSEQCONTROLE from SOFTRAN_MAGNA.GTCNFCON
                                                            Where CdEmpresa = {$con[0]->CDEMPRESA} and NrSeqControle = {$con[0]->NRSEQCONTROLE} ")->result();
                            
                            //Atualiza o estados de todas notas fiscais presentes naquele conhecimento
                            foreach($arrNotas as $nf){

                                $sequencia = $ref->db->query("SELECT NVL(MAX(CDSEQUENCIA),0) AS MAXCDSEQUENCIA 
                                                                        from SOFTRAN_MAGNA.GTCNFENT
                                                                    Where NrNotaFiscal = $nf->NRNOTAFISCAL
                                                                    and NrSerie = $nf->NRSERIE 
                                                                    and CdRemetente= $nf->CDINSCRICAO ")->result()[0]->MAXCDSEQUENCIA + 1;

                                $ref->db->query("INSERT INTO SOFTRAN_MAGNA.GTCNFENT 
                                                (CdRemetente,
                                                NrNotaFiscal,
                                                NrSerie,
                                                CdEmpresa,
                                                NrSeqControle,
                                                CdHistorico,
                                                CdSequencia,
                                                DtMovimento,
                                                HrMovimento,
                                                DtDigitacao,
                                                DtCadastro, 
                                                DsUsuario,
                                                CdAgencia,
                                                DsComplemento)
                                                VALUES ('$nf->CDINSCRICAO', 
                                                $nf->NRNOTAFISCAL,  
                                                '$nf->NRSERIE', 
                                                {$con[0]->CDEMPRESA}, 
                                                {$con[0]->NRSEQCONTROLE}, 
                                                1, 
                                                $sequencia, 
                                                to_char(SYSDATE,'DD/MM/YYYY'),
                                                to_date('{$Hora}','DD/MM/YYYY HH24:MI'),
                                                to_char(SYSDATE,'DD/MM/YYYY HH24:MI'),
                                                to_char(SYSDATE,'DD/MM/YYYY HH24:MI'),
                                                '$CpfMotorista',
                                                0,
                                                'Central de Entregas')
                                ");

                            $ref->db->query("UPDATE GTCNF 
                                    Set DtEntrega = to_char(SYSDATE,'DD/MM/YYYY') 
                                Where NrNotaFiscal= $nf->NRNOTAFISCAL and NrSerie='$nf->NRSERIE' and CdRemetente='$nf->CDINSCRICAO' ");


                            }*/

                            //Verifica se ocorreu alguma exceção ao executas as query, caso sim realiza o roll back
                            if ($ref->db->trans_status() === FALSE){
                                $ArrayRetorno = ["status" => false,
                                                "mensagem" => 'Problemas aos movimentas as tabelas do SOFtran.',
                                                "httpcode" => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                                                "type"     => Webservice::RESPONSE_ERROR];  

                                $ref->db->trans_rollback();
                            }else{
                                $ref->db->trans_commit();
                            }


                        } else {                            
                                $ArrayRetorno = ["status" => false,
                                                "mensagem" => 'Problemas ao gerar a sequência do movimento de entrega.',
                                                "httpcode" => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                                                "type"     => Webservice::RESPONSE_ERROR];                            
                        }
                        
                        if(!isset($ArrayRetorno)){
                            $ArrayRetorno = ["status"   => true,
                                            "mensagem" => Webservice::OK_CONTINUE,
                                            "httpcode" => REST_Controller::HTTP_OK,
                                            "type"     => Webservice::RESPONSE_OK];
                        }
                
            } else if (sizeof($con) > 1){
                        
                        $ArrayRetorno = ["status"   => false,
                                        "mensagem" => 'Mais de um documento encontrado.',
                                        "httpcode" => REST_Controller::HTTP_BAD_REQUEST,
                                        "type"     => Webservice::RESPONSE_ERROR];
                        
            } else {
                            $ArrayRetorno = ["status"   => false,
                                            "mensagem" => 'Nenhum documento encontrado.',
                                            "httpcode" => REST_Controller::HTTP_BAD_REQUEST,
                                            "type" => Webservice::RESPONSE_ERROR];
            }
                        
            //Retorno com a resposta
            return $ArrayRetorno;
                    
                    
            }
                
                /**
                 * Função aninhada que movimenta a tabela Entega_0800info do WebApp, 
                 * Ocorrências possíveis até o momento : 
                 *  1 - Inicio de Entrega
                 *  3 - Entrega Normal
                 *  5 - Problema Na Entrega
                 * @author Tiago Hélio de Borba<ti03.mtz@transmagna.com.br>
                 * 
                 */
                function movimentaEntregaInfo($post,$NomeMotorista = "",$NomeEntidade = "",$ref ){
                    try{
                        
                        date_default_timezone_set("Brazil/East");//Seta o fuso horario      
                        switch ($post['status']) {
                            // 1 - Entrega Iniciada
                            case 1:
                                {
                                   $Entrega = array('DSNOMEMOTORISTA'    => $NomeMotorista,
                                                     'CDFILIALORIGEM'     => $post['cdempresa'],
                                                     'NRDOCTOFISCAL'      => $post['nrdoctofiscal'],
                                                     'DSDESTINARIO'       => $NomeEntidade,
                                                     'DTINI'              => date('YmdHis'),
                                                     'CDULTIMAOCORRENCIA' => $post['status']);
                                   $ref->infoentregas0800_model->insert($Entrega); 
                                   break;
                                }
                            //3 - Entrega Normal   
                            case 3 : 
                                {
                                    $Entrega = array('CDULTIMAOCORRENCIA'    => $post['status'],
                                                     'DTFIM'     	     => date('YmdHis'));
                                    $ref->infoentregas0800_model->updateEntrega($post['nrdoctofiscal'],$post['cdempresa'],$Entrega,true);
                                    break;
                                }
                            //5 - Problema na Entrega  
                            case 5 : {                                
                                    $Entrega = array('CDULTIMAOCORRENCIA'    => $post['status'],
                                                     'DTFIM'     	     => date('YmdHis'));
                                    $ref->infoentregas0800_model->updateEntrega($post['nrdoctofiscal'],$post['cdempresa'],$Entrega);
                                    break;
                            }    
                        }
                        $ArrayRetorno = ["status"  => true,
                                         "mensagem" => Webservice::OK_CONTINUE,
                                         "httpcode" => REST_Controller::HTTP_OK,
                                         "type"     => Webservice::RESPONSE_OK];
                    } catch(Exception $ex){
                        $ArrayRetorno = ["status" => false,
                                         "mensagem" => "Erro ao movimentar o WebApp",
                                         "httpcode" => REST_Controller::HTTP_INTERNAL_SERVER_ERROR,
                                         "type"     => Webservice::RESPONSE_ERROR];
                    }
                    
                    return $ArrayRetorno; 
                }               
                
                //Após receber as informações pelo POST decide qual ação realizar atráves do STATUS (Ocorrência)
                switch ($this->post('status')) {
                    case 1:
                        {
                           $ResultEntrega = movimentaEntregaInfo($post,$NomeMotorista,$NomeEntidade,$this); 
                           sendResponse([$ResultEntrega],$this);
                           break;
                        }
                    case 3:
                        {                            
                            $ResultConhecimento = movimentaConhecimentoSoftran(1,$con,$post,$CpfMotorista,$NomeMotorista,$this);
                            $ResultEntrega      = movimentaEntregaInfo($post,$NomeMotorista,$NomeEntidade,$this); 
                            sendResponse([$ResultConhecimento,$ResultEntrega],$this);
                            break;
                        }
                    case 5:{
                            $ResultEntrega  = movimentaEntregaInfo($post,$NomeMotorista,$NomeEntidade,$this);
                            sendResponse([$ResultEntrega],$this);
                            break;
                        }
                }                
	}

	/* POST LOG
	 * Recebe mensagem para gerar um log - Método da URA */
	public function log_post(){
		$this->Ws_log_model->setDsMetodo("Log URA");

		$post = [
			'codigo'   => $this->post('codigo'),
			'mensagem' => $this->post('mensagem'),
			'hash'     => $this->post('hash'),
			'token'    => $this->post('token')
		];
		$this->validaCamposEsperados($post);
		
		//Trata log da URA para pegar número
		//[codigo] => 5 [mensagem] => [03/11/2016 10:47:27] [4796781189]: Discagem para motorista
		//[codigo] => 2 [mensagem] => [03/11/2016 10:49:30] [4796781189]: ("Envio de status da entrega concluido [4796781189]")
		//[codigo] => 6 [mensagem] => [03/11/2016 10:53:03] [4796781189]: Motorista não encontrado
		if ($post['codigo'] == 5){
			$num = explode("[", $post['mensagem']);
			$num = str_replace("]", "", explode(":", $num[2])[0]);
			
			$this->Ws_log_model->setDescricao("Discagem para ".$num);

		} else {
			//remove log criado 
			$this->Ws_log_model->setDelete(true);
		}

		$this->_out_args = [Webservice::RESPONSE_OK => Webservice::OK_CONTINUE];
		$this->response($this->_out_args, REST_Controller::HTTP_OK);
	}
        
    public function getNomeClienteCte($CdEmpresa,$NrDoctoFiscal){
        $query = $this->db->query("SELECT 
                                    B.DSENTIDADE
                                    FROM SOFTRAN_MAGNA.GTCCONHE A 
                                    LEFT JOIN  SOFTRAN_MAGNA.SISCLI B ON B.CdInscricao = A.CdDestinatario
                                    WHERE A.CdEmpresa = {$CdEmpresa} 
                                    AND A.NRDOCTOFISCAL = {$NrDoctoFiscal} 
                                    AND A.CDTPDOCTOFISCAL in (5,3) 
                                    AND A.DTENTREGA IS NULL
                                    AND A.DTCANCELAMENTO IS NULL");
        
        return $query->result()[0]->DSENTIDADE;
    }

    public function validaNumero_post(){
        $newMobile = $this->post('newMobile');
        

        
        
        $this->load->model('motoristas_model');
        $telefone = preg_replace('/[^[:digit:]]/', null, $this->post('telefone'));
       
        $cpf = preg_replace('/[^[:digit:]]/', null, $this->post('cpf'));
        $whereNumero = "   SUBSTR(regexp_replace(NRCELULAR, '[^[:digit:]]', null),4,11) LIKE '".$telefone."'  OR 
                              SUBSTR(regexp_replace(NRCELULAR2, '[^[:digit:]]', null),4,11) LIKE '".$telefone."'";

        $retorno = new StdClass();
        if(!$newMobile || $newMobile != '2'){
            $retorno->msgErr =  'Você esta utilizando uma versão antiga do aplicativo. Entre em contato com o TI.';
            $retorno->ok = false;
            $this->response($retorno,REST_Controller::HTTP_OK); 
        }
        $motoristas = $this->motoristas_model
                           ->getMotoristas()
                           ->setWhere($whereNumero)
                           ->setWhere("CPFCNPJ = 000$cpf")
                           ->go();
        
        if(sizeof($motoristas) > 1 || !sizeof($motoristas)){
            $retorno->msgErr =  sizeof($motoristas) > 1 ? 'Existem dois números iguais no sistema' : 'Número não encontrado';
            $retorno->ok = false;
        }else{
            $retorno->ok = true;
        }
        $retorno->ok = true;

        $this->response($retorno,REST_Controller::HTTP_OK);                   
    }
	
	/**
	 * Em desenvolvimento, ainda não utilizado.
	 * Gera JSON com dados para gráfico google */
	public function getChartData_get(){
		$this->load->model('ti_permissoes/usuario_log_model');
	
		$tempo = '6 hours';
	
		if (!preg_match('/day|hour/', $tempo))
			return false;
	
			//Data inicial voltando o tempo parametrizado
			$index = new DateTime();
			$index->modify('-'.$tempo);
	
			//Steps (como a informação será agrupada) Week,Day,Hour,Minute...
			$stepBy = substr($tempo, strpos($tempo, ' '));
	
			$table = [];
			$table['rows'] = [];
			$table['cols'] = [['label' => 'hora', 'type' => 'datetime'],
					['label' => 'Listagem', 'type' => 'number'],
					['label' => 'Ocorrencias', 'type' => 'number']];
	
			$t = ['days'  => ['mask' => 'Ymd',   'steps' => 24, 'increment' => '+ 1 hour'],
				  'hours' => ['mask' => 'YmdHi', 'steps' => 60, 'increment' => '+ 1 minute']];
	
			$metodos = [Webservice::METODO_LISTA_ENTREGAS,
					Webservice::METODO_OCORR_ENTREGAS];
	
			$logs = $this->usuario_log_model->getWSChartData($metodos, $index);
			$map = array_map(function($value){return $value->DATAHORA;}, $logs);
	
			for ($i = 0; $i <= $t[$stepBy]->steps; $i++){
				$min = intval($index->format('i'));
				$seg = intval($index->format('s'));
				$strDate = $dtHr->format('\\D\\a\\t\\e(Y, n, j, G, ').$min.', '.$seg.', 000)';
				$temp[] = ['v' => $strDate];
					
				$k = array_search($index->format('YmdHi'), $map);
				$rowTela = $k !== false ? $logs[$k]->TELA : null;
				$rowCount = $k !== false ? $logs[$k]->COUNT : 0;
					
				$temp[] = ['v' => ($rowTela == Webservice::METODO_LISTA_ENTREGAS ? $rowCount : 0)];
				$temp[] = ['v' => ($rowTela == Webservice::METODO_OCORR_ENTREGAS ? $rowCount : 0)];
				$table['rows'][] = ['c' => $temp];
					
				$temp = [];
				$index->modify($t[$stepBy]->increment);
			}
	
			$this->response($table, REST_Controller::HTTP_OK);
	}
	
        /*
        * <b>Metodo que retorna o CPF do motorista do conhecimento</b>
        * @param CdEmpresa - Codigo da Empresa 
        * @param NrSeqControle - Sequencia de Controle do Conhecimento
        * @param CdTpDoctoFiscal - Tipo do documento fiscal, por default é 3 - CTE
        */
        public function getCpfMotorista($cdEmpresa,$nrSeqControle){

            $query = "SELECT 
                      D.DSNOME,
                      B.NRCPFMOTORISTA
                      FROM SOFTRAN_MAGNA.CCEROMIT A
                      LEFT JOIN SOFTRAN_MAGNA.CCEROMAN B ON A.CDEMPRESA = B.CDEMPRESA AND A.CDROTA = B.CDROTA AND A.CDROMANEIO = B.CDROMANEIO
                      LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON A.CDEMPRESACOLETAENTREGA = C.CDEMPRESA AND A.NRSEQCONTROLE = C.NRSEQCONTROLE
                      LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP D ON B.NRCPFMOTORISTA = D.NRCPF
                      WHERE NVL(B.INSITUACAO,0) = 0 
                      AND B.DTROMANEIO = 
                      (SELECT MAX(ZZ.DTROMANEIO) FROM SOFTRAN_MAGNA.CCEROMAN ZZ
                      LEFT JOIN SOFTRAN_MAGNA.CCEROMIT GG ON ZZ.CDEMPRESA = GG.CDEMPRESA AND ZZ.CDROTA = GG.CDROTA AND ZZ.CDROMANEIO = GG.CDROMANEIO
                      WHERE GG.NRSEQCONTROLE = C.NRSEQCONTROLE AND GG.CDEMPRESACOLETAENTREGA = C.CDEMPRESA ) 
                      AND C.CDEMPRESA = ? AND C.NRSEQCONTROLE = ? AND C.CDTPDOCTOFISCAL in (3,5)  ";
            
             //Caso não tenha dados do motorista é manifesto
             if (empty($this->db->query($query,[$cdEmpresa,$nrSeqControle])->result()[0] ))
             {
                $queryManif = " SELECT 
                                B.CDMOTORISTA AS NRCPFMOTORISTA,
                                C.DSNOME
                                FROM SOFTRAN_MAGNA.GTCMANCN A
                                LEFT JOIN SOFTRAN_MAGNA.GTCMAN B ON B.NRMANIFESTO  = A.NRMANIFESTO
                                LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP C ON C.NRCPF  = B.CDMOTORISTA
                                WHERE A.NRSEQCONTROLE = ?
                                AND A.CDEMPRESA = ? 
                                AND A.DTCADASTRO = (select max(D.DtCadastro) from SOFTRAN_MAGNA.GTCMANCN D where D.NRSEQCONTROLE = A.NRSEQCONTROLE AND D.CDEMPRESA = A.CDEMPRESA)";
                
                return $this->db->query($queryManif,[$nrSeqControle,$cdEmpresa])->result();
             }else{
                 return $this->db->query($query,[$cdEmpresa,$nrSeqControle])->result();
             }
        }
        
	/**
	 * Monta retorno no formato json com parâmetros e documentos encontrados.
	 * @param array $params
	 * @param array $docs
	 */
	private function getListaDocsJson($params, $docs){	
		$ret = [Webservice::RESPONSE_OK => sizeof($docs) > 0 ? Webservice::OK_CONTINUE : Webservice::OK_FINALIZE];
		$ret = array_merge($ret, $params);
		$ret = array_merge($ret, ['docs' => $docs]);
	
		return $ret;
	}
		
	/**
	 * Adiciona Ocorrência de Entrega
	 * Método privado
	 * 
	 * @param unknown $cdEmpresa
	 * @param unknown $nrDocto
	 * @param unknown $status */
	private function addOcorrenciaEntrega($cdEmpresa, $nrDocto, $status){
		$this->load->model('entrega_conhecimentos_model');
		
		/*
		 *
		 *
		 * TODO: Verificar se já existe ocorrência para cte informado
		 * 
		 *
		 */ 
		
		return $this->entrega_conhecimentos_model->addOcorrenciaEntrega($cdEmpresa, $nrDocto, $status);
	}
	
	/**
	 * Verifica se nenhum dos campos é null.
	 * Caso sim, retorna response com erro e mensagem específica.
	 * @param array $campos
	 * @return boolean 
	 */
	private function validaCamposEsperados($campos = []){
		$campoVazio = array_search(null, $campos);
		
		if ($campoVazio)
			$this->response(
				[Webservice::RESPONSE_ERROR => 'Argumento '.$campoVazio.' deve ser informado'],
				REST_Controller::HTTP_EXPECTATION_FAILED
			);
		
		return true;
	}
	
	/**
	 * 
	 * @param string $msgErro
	 * @param unknown $httpStatus
	 */
	private function responseError($msgErro = "", $httpStatus = REST_Controller::HTTP_NOT_FOUND){
		$this->response([Webservice::RESPONSE_ERROR => $msgErro], $httpStatus);
	}
        
        
        /** 
         * Retorna a KM atual do veículo
         * o método retornaHodometroVeiculo retorna um Objeto pronto para retorno, validando possíveis erros  
         * @param String NrPlaca Placa do veículo 
         */
        public function getHodometroVeiculo_post(){
          
            $resultado = $this->softranmobilelibrary->retornaHodometroVeiculo($this->post('nrPlacaVeic')); 
            $this->response($resultado,REST_Controller::HTTP_OK);
             
        }
        
        /**
         *  Atualiza Hodometro do veiculo, já passando a sequencia 
         *  o novo KM, e o Km Atual e o veiculo a ser atualizado
         *  @param int cdVeiculo 
         *  @param int kmAtual
         *  @param int novoKm
         *  @param int cdSequencia
         * */
        private function atualizaHodometroVeiculo($cdVeiculo,$kmAtual,$novoKm,$cdSequencia,$usuario){
            
          $resultado = $this->softranmobilelibrary->atualizaHodometro($cdVeiculo,
                                                                      $kmAtual,
                                                                      $novoKm,
                                                                      $cdSequencia, 
                                                                      $usuario);
          return $resultado;

        }
        
        private function enviarEmail($nrPlaca,$cdVeiculo,$kmAtual,$kmInformado,$dtAtualizacao,$usuario){
            $retorno = new stdClass();
            
            $conteudo = "<label style='font-size : 18px;'>
                            Falha ao tentar atualizar o hodômetro do veículo <br><br> 
                            Placa: <b>{$nrPlaca}</b><br> 
                            Código do Veículo: <b>{$cdVeiculo}</b><br>
                            KM Atual: <b>{$kmAtual}</b><br>    
                            KM informado pelo usuário: <b>{$kmInformado}</b><br>
                            Data da última atualização : <b>{$dtAtualizacao}</b><br>    
                            Usuário : <b>{$usuario}</b><br><br> 
                            <small><b>Alerta gerado via o Aplicativo da Transmagna, não responder.</b><small>    
                        </label>";
            
            $assunto = "Falha ao tentar atualizar o hodômetro do veículo";                
            
            if($this->my_phpmailer->send_mail('portaria_mobile@transmagna.com.br',$conteudo,$assunto)){
                $retorno->ok = true;
                $retorno->mensagem = "Email enviado com sucesso!";
                return true;
            }else{
                $retorno->ok = false;
                $retorno->mensagem = "Ocorreu um erro ao tentar enviar o Email!";
                return false;
            }                        
        }

        public function getPlacaAntt_post(){
            $this->response($this->softranmobilelibrary->getPlateWithCode( $this->post('code') ),REST_Controller::HTTP_OK);
        }
        
        public function getTelasChecklist_post() {
            
            $this->load->model('frotas_portaria/portaria_foto_config_model');
            $this->load->model('frotas_portaria/portaria_foto_categoria_model');

            $objTelas = new stdClass();
            
            $objTelas->telas =  $this->portaria_foto_categoria_model
                                     ->where('FLINATIVO',0)
                                     ->order_by('SEQUENCIA', 'ASC')
                                     ->get_all();   
           
            foreach ($objTelas->telas as $key => $categoria) {
                
             
                $fotos =  $this->portaria_foto_config_model
                               ->where('FOTO_CATEGORIA_ID',$categoria->ID)
                               ->order_by('NRSEQUENCIA', 'ASC')
                               ->get_all();
                
                foreach($fotos as $key => $foto){   
                    
                    if(!isset($categoria->fotos)){
                        $categoria->fotos = [];
                        array_push($categoria->fotos,$foto);
                    }else{
                        array_push($categoria->fotos,$foto);
                    }
                    
                }
            }
            
            $this->response($objTelas,REST_Controller::HTTP_OK);
        }
        
        public function postNewChecklist_post()
        {
            $retorno = true;

            $ArrChecklist = json_decode(json_encode($this->post()));


            $this->load->library('MobileLib');

            foreach($ArrChecklist->checklists as $checklist)
            {   
                
                if(!$this->mobilelib->insertNewChecklist($checklist,$ArrChecklist->docs))
                {
                    $retorno = false;
                }
            }

            $this->response(json_encode($retorno),REST_Controller::HTTP_OK);

        }
        
        public function ocorrenciaChecklist_post()
        {   
            date_default_timezone_set("Brazil/East");
            $this->load->model('frotas_portaria/portaria_checklist_ocorrencia_model');
            
            $data = $this->input->post();
            $data['DTCRIACAO'] = date('YmdHis');
            $retorno = $this->portaria_checklist_ocorrencia_model->insertOcorr($data);

            $this->response($retorno,REST_Controller::HTTP_OK); 
        }

        public function postChecklist_post()
        {

            $status = false;
            $retorno = new stdClass();
            $objeto =  json_decode(json_encode($this->post()));
            date_default_timezone_set("Brazil/East");
            
            $checkList = new stdClass();
            $checkList->TPACAO = $objeto->FGACAO;
            $checkList->DSOBSERVACAO =  isset($objeto->comentario) ? $objeto->comentario : '';
            $checkList->USUARIO_ID = $objeto->userID;
            $checkList->INQRCODE = $objeto->leuQrCode == 'true' ? 1 : 0;
            $checkList->DTINCLUSAO = date('YmdHis');     
            print_r($objeto);
            //Ação 1 - Envia Email com problemas no checklist 2- Atualiza Hodometro do Veiculo             
            switch (intval($objeto->FGACAO))
            {
                
                case  1 : 
                {
                    $status = $this->enviarEmail($objeto->EMAIL->nrPlaca,
                                                          $objeto->EMAIL->cdVeiculo,
                                                          $objeto->EMAIL->kmAtual,
                                                          $objeto->EMAIL->kmInformado,
                                                          $objeto->EMAIL->dtAtualizacao,
                                                          $objeto->EMAIL->usuario);
                    
                    $checkList->CDVEICULO = $objeto->EMAIL->cdVeiculo;
                    break;
                } 
            
                case  2 : 
                {
                    $status = $this->atualizaHodometroVeiculo($objeto->HODOMETRO->cdVeiculo, 
                                                                       $objeto->HODOMETRO->kmAtual, 
                                                                       $objeto->HODOMETRO->novoKm, 
                                                                       $objeto->HODOMETRO->cdSequencia, 
                                                                       $objeto->HODOMETRO->usuario);
                    
                    //Se Atualizou o hodometro, passa esse valor pro checklist
                    if($status)
                    {
                        $checkList->KMATUAL = $objeto->HODOMETRO->novoKm;
                        $checkList->KMANTERIOR = $objeto->HODOMETRO->kmAtual;
                    }
                    
                    $checkList->CDVEICULO = $objeto->HODOMETRO->cdVeiculo;
                    break;
                }
                
            }
            
            $checkList->ID = $this->portaria_checklist_model->insert($checkList);
            $status = $checkList->ID ? true : false;

            if($status){
                //TODO : andre
                $query = "SELECT 
                                nvl(
                                    (
                                        select CASE WHEN DATACORRECAO IS NULL THEN 1 ELSE 0 END AS PENDENCIAS 
                                        from softran_html.statusveic_logfrt
                                        WHERE PLACA = A.NRPLACA AND TIPO = 'CHECKLIST' 
                                        GROUP BY TIPO, 
                                        CASE WHEN DATACORRECAO IS NULL THEN 1 ELSE 0 END
                                        ),
                                    0) AS CHECKLIST
                            FROM SOFTRAN_MAGNA.SISVEICU a
                            WHERE  
                            -- Veiculos não baixados da frota
                            NVL(A.FGBAIXADO,0) = 0
                            -- Placa especifica inserida
                            AND UPPER(A.NRPLACA) = 'MHI9003'";
                $RetornoSoftran = $this->CI->db
                                        ->query($query)
                                        ->result();
                                        
                print_r($RetornoSoftran);
                if($RetornoSoftran->CHECKLIST == 1){
                    if($usuarioWebapp = $this->CI->db
                                             ->query("SELECT USUARIO FROM USUARIO WHERE id = {$objeto->userID}")
                                             ->result()){
                        $usuarioWebapp = $usuarioWebapp[0]->USUARIO;
                        $operationOk = $this->CI->db->query("UPDATE STATUSVEIC_LOGFRT 
                                                                SET DESCRICAOCORRECAO = 'CHECKLIST REALIZADO', 
                                                                    USUARIOCORRECAO = '{$usuarioWebapp}', 
                                                                    DATACORRECAO = sysdate 
                                                                WHERE PLACA = 'PLACA DO CHECKLIST' 
                                                                AND TIPO = 'CHECKLIST' ");   
                    }
                }
            }
            
            $diretorio = self::DIRETORIOARQUIVOS.date('d-m-Y');
            
            //Caso Tenha Foto Salva Elas
            if(isset($objeto->fotos)){
               
                //Verifica se não existe o diretório, cria ele
                if(!file_exists($diretorio)){
                    mkdir($diretorio);
                }
                
                //Percorre o Array de Imagens, e salva no diretório
                foreach ($objeto->fotos as $id => $imagem) {
                    
                    //Pega as Informações da Imagem para inserir no BD
                    $imagemPost = new stdClass();
                    $imagemPost->CHECKLIST_ID = $checkList->ID;
                    $imagemPost->FOTO_CONFIG_ID = $imagem->ID; 
                    $imagemPost->FGVISIVEL = 1;

                    //Seta o nome do Arquivo com as Informações da Foto
                    $arquivoNome = "{$checkList->ID}-{$checkList->CDVEICULO}-{$imagem->DSDESCRICAO}.jpg";
                    $caminhoImagem = "{$diretorio}/{$arquivoNome}";
                    
                    //Seta o Caminho para inserir no Bnaco
                    $imagemPost->CAMINHO = $caminhoImagem;
                    
                    //Salva a Foto
                    file_put_contents($caminhoImagem,base64_decode($imagem->IMG));
                    
                    $status = $this->portaria_checklist_foto_model->insert($imagemPost);
                }              
            }
            
            $response = new stdClass();
            $response->ok = $status ? true : false;
            
            $this->response(json_encode($response),REST_Controller::HTTP_OK);       
        }

    public function getDocumentosBatida_post()
    {
        $post = $this->input->post();

        //Pega a empresa do usuário
        $cdEmpresa = $post['cdempresa'];

        //Carrega o model de equipamentos
        $this->load->model('ti_biometria/biometria_equipamento_model');

        //Pega o equipamento cadastrado para a aquela filial
        $equipamento = $this->biometria_equipamento_model->get(["CDEMPRESA" => $cdEmpresa , "PADRAO" => "1"]);
        
        //Retorna o Json de "response"
        $retorno = new stdClass();
                        
        
        if($equipamento)
        {   
            //Tipo de equipamento
            $tipo = $equipamento->TIPO == 'IDACCESS';
            //Adiciona os parametros
            $constructParams = ["id" => $equipamento->ID, "protocol" => $equipamento->PROTOCOLO, "ip" => $equipamento->IP, "user" => $equipamento->USUARIO, "password" => $equipamento->SENHA];

            //Carrega a instancia da classe do equipamento
            $this->load->library('IdClass',$constructParams);

            //Autentica com o equipamento
            if($this->idclass->authenticate())
            {   
                //Atualiza o banco de dados com os registros do ponto
                $this->idclass->atualizarPontosDb($tipo);
                
                //Carrega a instancia da classe que retorna o Json que vamos utilizar no mobile
                $this->load->library('BuildJsonPortaria');

                //Retorna o Json de "response"
                $retorno->obj = $this->buildjsonportaria->buildJson($cdEmpresa);

                echo(json_encode($retorno));
            }else{
                $retorno->error = true;
                $retorno->msg = 'Não foi possível logar';
                echo(json_encode($retorno)); 
            }
        }else{
            $retorno->error = true;
            $retorno->msg = 'Sua filial não tem equipamento cadastrado';
            echo(json_encode($retorno)); 
        }
        

    } 

    public function refeicao_post($ref){
        $this->load->library('Refeicao');
        echo json_encode($this->refeicao->import($ref));
    }

    public function getPlacaCheckMec_post(){
        $post = $this->input->post();
       
        $nrPlaca = strtoupper($post['nrplaca']);

        //Carrega a instancia da classe que retorna o Json que vamos utilizar no mobile
        $this->load->library('BuildJsonPortaria');

        //Retorna o Json de "response"
        $retorno = $this->buildjsonportaria->buildJsonChecklistMec($nrPlaca);

       echo(json_encode($retorno));           
    } 
        
}
