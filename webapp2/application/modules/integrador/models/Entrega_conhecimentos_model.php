<?php

class Entrega_conhecimentos_model extends MY_Model
{
	public function __construct()
	{
		parent::__construct();
		
		$this->table = 'ENTREGA_CONHECIMENTOS';
	}

	public function getCtesByMotorista($idMotorista = null){
		if (!is_null($idMotorista)){
			$query = $this->db
						  ->select([
						  		'CDEMPRESA     as empresa',
						  		'NRDOCTOFISCAL as nrDocto',
						  		'IDSTATUS      as statusEntrega',
						  		"'1,2,3,4,5'   as opcoes"])
						  ->from($this->table)
						  ->where([
						  		'CDMOTORISTA' => $idMotorista])
						  		//'IDSTATUS !=' => '1'])
						  ->get();

			return $query->result();
		}
		
		return false;
	}
	
	public function addOcorrenciaEntrega($cdEmpresa, $nrDocto, $status){
		$query = $this->db
					  ->where([
					  		'CDEMPRESA'     => $cdEmpresa,
							'NRDOCTOFISCAL' => $nrDocto])
					  ->update($this->table, ['IDSTATUS' => $status]);
					  
		if ($this->db->affected_rows() > 0)
			return true;
			
		return false;
	}
}