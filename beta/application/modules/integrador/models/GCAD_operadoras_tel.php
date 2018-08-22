<?php

class GCAD_operadoras_tel extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
		
		$this->table = 'GCAD_OPERADORAS_TEL';
		$this->primary_key = 'ID';
	}

	/*
	public function getMotoristaByTelefone($numero = null){
		if (!is_null($numero)){
			$query = $this->db
						  ->select(['IDMOTORISTA','IDOPERADORA'])
						  ->from($this->table)
						  ->where('NUMERO', $numero)->get();
			
			if ($query->num_rows() > 0){
				return $query->row_array();
			}
			
			return false;
		}
	}
	
	

	private function _setEntrega($entrega){
		return array('ID' => $entrega['id'],
					 'ID_MOTORISTA' => $entrega['name']);
	}
	*/
}