<?php

/**
 * Description of Portaria_checklist_foto_model
 *
 * @author Administrador
 */
class Portaria_checklist_foto_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'PORTARIA_CHECKLIST_FOTO';
        $this->primary_key = 'ID';
    }

}
