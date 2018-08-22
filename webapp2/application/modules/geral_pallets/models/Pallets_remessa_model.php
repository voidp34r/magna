<?php


class Pallets_remessa_model extends MY_Model {
	
    public function __construct() {    	
        parent::__construct();
        
        $this->table = 'PALLETS_REMESSA';
        $this->primary_key = 'ID';
    } 
}
