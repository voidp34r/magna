<?php

/**
 * Description of Agregado_veiculo_model
 *
 * @author Administrador
 */
class Pallets_cliente_model extends MY_Model {
	
    public function __construct() {    	
        parent::__construct();
        
        $this->table = 'PALLETS_CNPJ';
        $this->primary_key = 'ID';
    }  
}
