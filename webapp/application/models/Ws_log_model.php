<?php


class Ws_log_model extends MY_Model {

	public $dhIni;
	public $dhFim;
	public $dsRequisicao;
	public $dsRetorno;
	public $cdRetorno;
	public $dsMetodo;
	public $dsDescricao;
	public $ID;
	public $deleteOnEnd = false;
	
    public function __construct(){
        parent::__construct();

        $this->table = 'WS_LOG';
        $this->primary_key = 'ID';
    }

    public function setDsRequisicao($str){
    	$this->dsRequisicao = $str;
    	return $this;
    }
    
    public function setDsRetorno($str){
    	$this->dsRetorno = $str;
    	return $this;
    }
    
    public function setCdRetorno($str){
    	$this->cdRetorno = $str;
    	return $this;
    }
    
    public function setDsMetodo($str){
    	$this->dsMetodo = $str;
    	return $this;
    }
    
    public function setDescricao($str){
    	$this->dsDescricao = $str;
    	return $this;
    }
    
    public function setDelete($bool){
    	$this->deleteOnEnd = $bool;
    	return $this;
    }
    
    public function commit(){
    	//TODO remover essa .gamb 
    	if ($this->deleteOnEnd){
    		$this->delete($this->ID);
    		
    		return $this;
    	}
    	
    	//Pode dar erro ao gravar se requisição ou retorno ultrapassar 4000k (varchar2, CI ainda não suporta BLOB)
    	//Então faz um substring para gravar
    	if (strlen($this->dsRequisicao) > 2000)
    		$this->dsRequisicao = substr($this->dsRequisicao, 0, 500).'  (... REQUISIÇÃO ENCURTADA ...)  '.substr($this->dsRequisicao, -500);
    	
    	if (strlen($this->dsRetorno) > 2000)
    		$this->dsRetorno = substr($this->dsRetorno, 0, 500).'  (... RETORNO ENCURTADO ...)  '.substr($this->dsRetorno, -500);
    	
    	//Verifica se é inclusão ou update e grava
   		if (is_null($this->ID)){
   			//insert
   			$this->dhIni = microtime(true);
   			
			$qry = $this->insert($this->getFieldsArray());
			if ($qry){
				$this->ID = $qry;
			}
		} else {
			//update
			if (is_null($this->dhFim)){
				$this->dhFim = microtime(true);
			}
			$qry = $this->update($this->getFieldsArray(), $this->ID);
		}
		
    	if (!$qry){
    		if (!is_null($this->ID)){
    			$qry = $this->update(["DHFIM"=>$this->dhFim], $this->ID);
    		}
    		log_message('error', 'Webservice: Falha ao gerar log');
    	}
    	
    	return $this;
    }
    
    private function getFieldsArray(){
    	return [
    		'DHINI' => $this->dhIni,
    		'DHFIM' => $this->dhFim,
    		'DSREQ' => $this->dsRequisicao,
    		'DSRET' => $this->dsRetorno,
    		'DSMETODO' => $this->dsMetodo,
    		'DSDESCRICAO' => $this->dsDescricao,
    		'CDRET' => $this->cdRetorno
    	];
    }
}
