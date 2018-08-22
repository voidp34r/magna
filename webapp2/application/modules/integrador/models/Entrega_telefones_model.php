<?php

class Entrega_telefones_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
		
		$this->table = 'ENTREGA_TELEFONES';
	}

	public function getMotoristaByTelefone($numero = null){
		if (!is_null($numero)){
			$query = $this->db
						  ->select([
						  		'TEL.IDMOTORISTA',
						  		'TEL.IDOPERADORA',
						  		'OP.ID'])
						  ->from($this->table.' TEL')
						  	->join('GCAD_OPERADORAS_TEL OP', 'OP.ID = TEL.IDOPERADORA', 'left')
						  ->where('NUMERO', $numero)
						  ->get();
			
			if ($query->num_rows() > 0){
				return $query->row_array();
			}
			
			return false;
		}
	}	
}