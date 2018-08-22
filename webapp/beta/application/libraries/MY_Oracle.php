<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class MyOracle {

    var $CI;

    public function __construct (){
        $this->CI = & get_instance();
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
     * Faz a pagina��o de querys.
     */
    public function paginate($page = 1, $perPage = 10, $orderBy = ""){
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
}
