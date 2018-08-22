<?php


class Motoristas_model extends MY_Model {

	public $query = "";
	public $order = "";
	
	CONST FUNCIONARIO = "funcionario";
	CONST FRETEIRO = "freteiro";
	CONST ALIAS = "SOFTRAN_MAGNA";
	
	private $isCount = false;
	
    public function __construct() {    	
        parent::__construct();
    }

	/**
     * Entidade Motorista: Freteiro ou Funcionário
    */
    public function getMotoristas(){
        
        $this->query = "SELECT    'funcionario' AS tipo, 
                        CEL.nrcelular as NRCELULAR,
                        CEL.nrcelular2 as NRCELULAR2,
                        Regexp_replace(fun.nrcpf, '[^[:digit:]]', NULL) AS cpfcnpj, 
                        Initcap(fun.dsnome) AS dsnome 
                        FROM      SOFTRAN_MAGNA.gtcfundp fdp 
                        LEFT JOIN SOFTRAN_MAGNA.sisfun fun  ON    fun.cdfuncionario = fdp.cdfuncionario 
                        LEFT JOIN GERAL_CELULAR CEL ON CEL.CDFUNCIONARIO = FUN.CDFUNCIONARIO
                        WHERE     fdp.infuncionario = 1 
                        AND       fdp.cdfuncionario IS NOT NULL 
                        AND       fun.cdfuncionario IS NOT NULL 
                        AND       NVL(fun.FGDEMITIDO,0) = 0
                        AND       fun.DTAFASTAMENTO IS NULL
                        UNION ALL 
                        SELECT    'freteiro' AS tipo, 
                        CEL.nrcelular as NRCELULAR,
                        CEL.nrcelular2 as NRCELULAR2, 		
                        regexp_replace(fre.cdfreteiro, '[^[:digit:]]', NULL) AS cpfcnpj, 
                        initcap(fre.dsnome) AS dsnome 
                        FROM      SOFTRAN_MAGNA.gtcfundp fdp 
                        LEFT JOIN SOFTRAN_MAGNA.gtcfrete fre ON fre.cdfreteiro = fdp.nrcpf 
                        LEFT JOIN GERAL_CELULAR CEL ON CEL.nrcpf = regexp_replace(fre.cdfreteiro, '[^[:digit:]]', NULL)
                        WHERE     nvl(fdp.infuncionario, 0) = 0 
                        AND       fdp.nrcpf IS NOT NULL 
                        AND       FRE.INJURIDICAFISICA = 1
                        AND       fre.cdfreteiro IS NOT NULL";
        
    	return $this;
    }
       
    /**
     * Busca os CTE´s de motoristas (freteiros ou funcionários)
     * Documentos elegíveis à ocorrência de entrega:
     *    - CDTPDOCTOFISCAL = 3 (conhecimento)
     *    - Não cancelados (Sem data de cancelamento)
     *    - Não entregues (Sem data de entrega)
     *
     * @param string $cpfCnpjMotorista
     */
    public function getCtesAEntregar($cpfCnpjMotorista){
		$query =
	    	"SELECT CON.NRDOCTOFISCAL, \n".
	    	"		CON.CDEMPRESA, \n".
	    	"		CON.NRSEQCONTROLE, \n".
	    	"		CON.CDMOTORISTA, \n".
	    	"		CON.DTENTREGA \n".
	    	"  FROM SOFTRAN_MAGNA.GTCCONHE CON \n".
	    	" WHERE CON.CDMOTORISTA = ? \n".
	    	"   AND CON.CDMOTORISTA IS NOT NULL \n".
	    	"   AND CON.DTENTREGA IS NULL \n".
	    	"   AND CON.CDTPDOCTOFISCAL = 3 \n".
	    	"   AND CON.DTCANCELAMENTO IS NULL "
		;
    	//Teste		 
		return $this->CI->db
   			->query($query, [$cpfCnpjMotorista])
    		->result();
    }
    
	/**
	 * Faz um select de uma query composta inserindo clausula where.
	 * @param string $codigo
	 * @param string $tipo
	 */
    public function setWhere($where = ""){
    	if (!empty($where))
    		$this->query = "SELECT * FROM (".$this->query.") WHERE ".$where;
    	
    	return $this;
    }
    
    /**
     * Faz a paginação de querys no oracle.
     */
    public function paginateOracle($page = 1, $perPage = 10, $orderBy = ""){
    	$ini = ($page * $perPage) - $perPage + 1;
    	$fim = ($ini-1) + $perPage;
    	
    	$this->isCount = false;
        $this->query = 
        	"SELECT * FROM ( "
            .    "SELECT X.*, row_number() OVER ( "
                      .(!is_null($orderBy)
                          ? "ORDER BY LTRIM(".$orderBy.") "
                          : ""
                      )
            .    ") line_number "
            .    "FROM ( ".$this->query.") X "
            .") "
            ."WHERE line_number >= ".$ini." "
            ."AND line_number <= ".$fim
        ;
    	
    	return $this;
    }

    /**
     * Monta um count de uma query
     */
    public function count(){
    	$this->isCount = true;
    	$this->queryCount = "SELECT COUNT(*) AS COUNT FROM ( ".$this->query." )";
    	
    	return $this;
    }
    
    /**
     * Executa mesmo
     */
    public function go(){
    	log_message('debug', 'motoristas_model.go(): '.$this->query);
    	
    	if($this->isCount){
    		//number do campo count
    		return $this->db->query($this->queryCount)
    			->result()[0]->COUNT;
    	} else {
    		//array com 1 objeto para cada linha
    		return $this->db->query($this->query)
    			->result();
    	}
    }

    public function atualizaTelefoneMotorista($tipo,$cpf,$telefone,$principal){

        $where = $tipo == self::FRETEIRO ? 'NRCPF' : 'CDFUNCIONARIO';
        $cpf = $tipo == self::FRETEIRO ? $cpf : $this->getCdFuncionario($cpf);
        $update = $principal ? 'NRCELULAR' : 'NRCELULAR2';

        $rs = $this->db->query("SELECT * FROM GERAL_CELULAR WHERE $where = '$cpf' ")->result();
        
        $operationOk;

        if(!empty($rs)){
            $operationOk = $this->db->query("UPDATE GERAL_CELULAR SET $update = '$telefone' WHERE $where = '$cpf'");
        }else{
            $operationOk = $this->db->query("INSERT INTO GERAL_CELULAR ($where,$update) VALUES ('$cpf','$telefone')");
        }

        return $operationOk;
    }

    
    /** 
     * @param varchar $Cpf  
     * Retorna o código do funcionario passando o cpf dele
     */
    function getCdFuncionario($Cpf){
        //Primeiro Pega o Código do Funcionario, pois é o que Diferencia do Freteiro na tabela 'GERAL_CELULAR'
        $Fun = $this->db->query("SELECT CDFUNCIONARIO FROM SOFTRAN_MAGNA.SISFUN WHERE NRCPF ='{$Cpf}' AND NVL(FGDEMITIDO,0) = 0  AND DTAFASTAMENTO IS NULL "); 
        return $Fun->result()[0]->CDFUNCIONARIO;  
    }
}
