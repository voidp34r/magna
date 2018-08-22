<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class SoftranGeralCadastrosDB extends MY_Model{
	var $CI;

    public function __construct (){
        $this->CI = & get_instance();
    }

    public function getMotoristasDisponiveis(){
    	$query = $this->CI->db->query(
	    			"SELECT TO_CHAR(FUN.CDFUNCIONARIO) AS CDFUNC, ".
					"       FUN.NRCELULAR              AS CELPARTIC, ".
					"       FUN.NRCELULAREMPRESA       AS CELEMPRESA ".
					"  FROM SOFTRAN_MAGNA.SISFUN FUN ".
					" WHERE FUN.CDCBO = 98560 ". //Motorista
					"   AND FUN.FGDEMITIDO <> 1 ".
					"UNION ALL ".
					"SELECT FRE.CDFRETEIRO          AS CDFUNC, ".
					"       FRE.NRCELULARPARTICULAR AS CELPARTIC, ".
					"       FRE.NRCELULAREMPRESA    AS CELEMPRESA ".
					"  FROM SOFTRAN_MAGNA.GTCFRETE FRE ");
    	
    	return $query->result();
    }
}
