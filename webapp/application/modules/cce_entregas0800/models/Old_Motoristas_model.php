<?php

/**
 * Description of Agregado_veiculo_model
 *
 * @author Administrador
 */
class Old_Motoristas_model extends MY_Model {

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
        $this->query = "\n"
            ."SELECT TO_CHAR(FUN.NRCPF) AS CPFCNPJ, \n"
            ."  '".self::FUNCIONARIO."' AS TIPO, \n"
            ."  FUN.NRCELULAR           AS CELPARTIC, \n"
            ."  FUN.NRCELULAREMPRESA    AS CELEMPRESA, \n"
            ."  INITCAP(FUN.DSNOME)     AS DSNOME \n"
            ."FROM ".self::ALIAS.".SISFUN FUN \n"
            ."WHERE FUN.CDCBO = 98560 \n"
            ."  AND FUN.FGDEMITIDO <> 1 \n"
            ."UNION ALL \n"
            ."SELECT FRE.CDFRETEIRO     AS CPFCNPJ, \n"
            ."  '".self::FRETEIRO."'    AS TIPO, \n"
            ."  FRE.NRCELULARPARTICULAR AS CELPARTIC, \n"
            ."  FRE.NRCELULAREMPRESA    AS CELEMPRESA, \n"
            ."  INITCAP(FRE.DSNOME)     AS DSNOME \n"
            ."FROM ".self::ALIAS.".GTCFRETE FRE \n"
        ;
    	
    	return $this;
    }

	/**
	 * Faz um select de uma query composta inserindo clausula where.
	 * @param string $codigo
	 * @param string $tipo
	 */
    public function setWhere($condicao = ""){
    	$this->query = "SELECT * FROM (".$this->query.") WHERE ".$condicao;
    	
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
    /**
     * Atualiza telefones dos motoristas (freteiro ou funcionário)
     * 
     * Insere informação da operadora do número no campos da Softran. 
     * Esta informação é usada no webservice de Entregas 0800 para identificar a operadora do motorista
     * e ligar através de um número de mesma operadora para reduzir os custos.
     * @param unknown $post
     */
    public function updateTelefonesMotorista($post = null, $tipo = null){
    	if (!is_null($post) 
    		&& !is_null($tipo)
    		&& !is_null($post['CDFUNC']) 
    		&& !is_null($post['CELPARTIC']) 
    		&& !is_null($post['CELEMPRESA'])
    	){
    		if ($tipo == self::FRETEIRO){
    			$this->db->query(
    				 "UPDATE ".self::ALIAS.".GTCFRETE "
	    			."SET NRCELULARPARTICULAR = '".$post['CELPARTIC']."',"
	    			."    NRCELULAREMPRESA    = '".$post['CELEMPRESA']."' "
	    			."WHERE CDFRETEIRO = '".$post['CDFUNC']."'"
    			);
    			return true;
    		} else if ($tipo == self::FUNCIONARIO){
    			$this->db->query(
    				"UPDATE ".self::ALIAS.".SISFUN "
    				."SET NRCELULAR           = '".$post['CELPARTIC']."',"
    				."    NRCELULAREMPRESA    = '".$post['CELEMPRESA']."' "
    				."WHERE CDFUNCIONARIO = '".$post['CDFUNC']."'"
    			);
    			return true;
    		}
    	}
    	return false;
    }
    
}
