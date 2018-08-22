<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of SMS_Iagente
 *
 * @author Administrador
 */
class SMS_Webservice {

    var $ws;
    var $user = 'gerencia.ti@transmagna.com.br';
    var $pass = 'nG21JS9x';
    var $options = array(
        'location' => 'https://www.iagentesms.com.br/webservices/ws.php',
        'uri' => 'https://www.iagentesms.com.br/webservices/',
        'encoding' => 'ISO-8859-1',
        'trace' => 1,
        'exceptions' => 0
    );

    public function __construct() {
        $this->ws = new SoapClient(NULL, $this->options);
        $this->ws->Auth($this->user, $this->pass);
    }

    public function enviar_sms($numero, $mensagem, $agendamento = '', $codigo = null){
        return $this->ws->enviar_sms('avulso', $numero, $mensagem, $agendamento, $codigo);
    }
    
    public function verifica_status($codigo){
        return $this->ws->verifica_status($codigo);
    }
    
}
