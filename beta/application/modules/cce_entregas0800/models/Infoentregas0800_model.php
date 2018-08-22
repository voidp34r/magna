<?php

class Infoentregas0800_model extends MY_Model {
   
    public $where = "";
    public function __construct() {
        
        parent::__construct();
        $this->table = 'ENTREGA_0800INFO';
        $this->primary_key = NULL;
    }
    
    public function getAllEntregas(){
    
    $this->query =  "SELECT".
                    " NVL(D.VLTDA,0) AS TDA,".    
                    " NVL(B.VLTDE,0) AS TDE,".    
                    " A.DSNOMEMOTORISTA,".
                    " A.CDFILIALORIGEM,".
                    " A.NRDOCTOFISCAL,".
                    " A.DSDESTINARIO,".
                    " A.CDULTIMAOCORRENCIA,".
                    " A.DTINI,".
                    " A.DTFIM,".
                    " B.DTEMISSAO,".
                    " B.CDEMPRESADESTINO,".            
                    " (select to_date(A.DtIni,'YYYYMMDDHH24MISS') from dual) as DTINIALT,".
                    " (select to_date(A.DtFim,'YYYYMMDDHH24MISS') from dual)  as DTFIMALT,".
                    " C.CDEMPRESA as CDFILIALENTREGA,".
                    " C.DSEMPRESA as DSFILIALENTREGA".
                    " FROM ENTREGA_0800INFO A".
                    " LEFT JOIN SOFTRAN_MAGNA.GTCCONHE B ON B.CDEMPRESA = A.CDFILIALORIGEM  AND A.NRDOCTOFISCAL = B.NRDOCTOFISCAL AND B.CDTPDOCTOFISCAL IN (3,5)". 
                    " LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C ON C.CDEMPRESA = B.CDEMPRESADESTINO".
                    " LEFT JOIN SOFTRAN_MAGNA.GTCCONIA D ON D.NRSEQCONTROLE = B.NRSEQCONTROLE AND D.CDEMPRESA = B.CDEMPRESA".
                    " WHERE B.DTEMISSAO = (SELECT MAX(ZZ.DTEMISSAO) FROM SOFTRAN_MAGNA.GTCCONHE ZZ WHERE ZZ.NRDOCTOFISCAL = B.NRDOCTOFISCAL AND ZZ.CDEMPRESA = B.CDEMPRESA AND B.CDTPDOCTOFISCAL IN (3,5))";
    return $this;

    }
    
    
    public function retornaUltimaEntrega($NrDoctoFiscal,$CdFilial){
        
        $this->db->query($this->queryCount)->result()[0];
    }
    
    public function updateEntrega($NrDoctoFiscal,$CdFilial,$Data,$UltimaOcorrencia = false){
        //Atualiza apenas a ultima ocorrência
        if($UltimaOcorrencia){
           $DtIni =  $this->db->query("SELECT 
                                       MAX(to_char(to_date(DtIni,'YYYY/MM/DD HH24:MI::SS'),'DD/MM/YYYY HH24:MI:SS')) as DTINI
                                       FROM ENTREGA_0800INFO
                                       WHERE CDFILIALORIGEM = {$CdFilial}
                                       AND NRDOCTOFISCAL = {$NrDoctoFiscal}")->result()[0]->DTINI;
                                       
           $this->db->where("to_char(to_date(DTINI,'YYYY/MM/DD HH24:MI::SS'),'DD/MM/YYYY HH24:MI:SS') = ",$DtIni); 
        }
        $this->db->where('CDFILIALORIGEM',$CdFilial);
        $this->db->where('NRDOCTOFISCAL',$NrDoctoFiscal);
        $this->db->update($this->table,$Data); 
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
                          ? "ORDER BY ".$orderBy." "
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
}

