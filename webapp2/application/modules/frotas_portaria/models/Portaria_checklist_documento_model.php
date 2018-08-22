<?php


class Portaria_checklist_documento_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        
        $this->table = 'PORTARIA_CHECKLIST_DOCUMENTO';
        $this->primary_key = 'ID';
    }

}
