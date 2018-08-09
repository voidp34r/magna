<?php

/**
 * Description of usuario_model
 *
 * @author Administrador
 */
class Usuario_log_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'USUARIO_LOG';
        $this->primary_key = 'ID';
    }

	public function getWSChartData($telas = null, $dateInicio){
		$dNow = date('YmdHis');
		$tNow = time($dNow);
		
		$query = $this->db
					  ->select([
					  		'substr(DATAHORA, 1, 12) as DATAHORA',
					  		'TELA',
					  		'count(*) as COUNT'])
					  ->from($this->table)
					  ->where("MODULO", 'webservice')
					  ->where_in("TELA", $telas)
					  ->where("DATAHORA >=", date('YmdHis', $tNow - ($tempo*60)))
					  ->where("DATAHORA <=", $dNow)
					  ->group_by("substr(DATAHORA, 1, 12)")
					  ->group_by("TELA")
					  ->order_by("DATAHORA", "ASC")
					  ->get();

		return $query->result();
	}
}
