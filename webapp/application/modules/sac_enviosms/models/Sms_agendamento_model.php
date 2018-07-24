<?php

/**
 * Description of sms_agendamento_model
 *
 * @author Administrador
 */
class Sms_agendamento_model extends MY_Model {

    public function __construct() {
        parent::__construct();

        $this->table = 'SMS_AGENDAMENTO';
        $this->primary_key = 'ID';
    }


}
