<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Controlid_webservice
 *
 * @author Administrador
 */
class Controlid_webservice {

    var $ip;
    var $session;
    var $user;
    var $pass;
    var $protocol;
    var $prepare;
    var $uri;

    //@Deprecated
    function config ($ip, $protocol = 'http'){
        $this->ip = $ip;
        $this->protocol = $protocol;
    }

    function login ($user, $pass){
        $data = '{"login":"' . $user . '","password":"' . $pass . '"}';
        return $this->_curl('/login.fcgi', $data);
    }

    function comando($uri, $fields_string, $file = NULL){
        return $this->_curl($uri, $fields_string, $file);
    }

    /*
     * IDACCESS
     */
    function execute_actions(){
        $data = '{"actions":[{"action":"door","parameters":"door=1"}]}';
        return $this->_curl('/execute_actions.fcgi', $data);
    }

    function get_configuration(){
        $data = '{"general":["beep_enabled","relay1_enabled","relay2_enabled","relay1_timeout","relay2_timeout"]}';
        return $this->_curl('/get_configuration.fcgi', $data);
    }

    function get_user_image(){
        $data = '{"user_id":44}';
        return $this->_curl('/user_get_image.fcgi', $data);
    }
    
    function create_objects ($name, $arr){
        $json = json_encode($arr);
        $data = '{"object":"' . $name . '","values":[' . $json . ']}';
        return $this->_curl('/create_objects.fcgi', $data);
    }

    function destroy_objects ($name, $arr){
        $json = json_encode(array($name => $arr));
        $data = '{"object":"' . $name . '","where":' . $json . '}';
        return $this->_curl('/destroy_objects.fcgi', $data);
    }

    function load_objects ($name, $opcional = NULL){
        $data = '{"object":"' . $name . '"';
        if ($opcional)
        {
            if (!empty($opcional['where']))
            {
                $data .= ',"where":' . json_encode($opcional['where']);
            }
        }
        $data .= '}';
        return $this->_curl('/load_objects.fcgi', $data);
    }

    function template_extract ($file, $width, $height){
        $param = '?width=' . $width . '&height=' . $height;
        return $this->_curl('/template_extract.fcgi' . $param, $file, true);
    }

    function template_match ($file, $s0, $s1, $s2){
        //$param = '?size0=' . $s0 . '&size1=' . $s1 . '&size2=' . $s2;
        return $this->_curl('/template_merge.fcgi', $file, false);
    }

    function template_create ($file, $user_id, $s0, $s1, $s2, $type){
        $param = '?size0=' . $s0 . '&size1=' . $s1 . '&size2=' . $s2;
        $param .= '&user_id=' . $user_id . '&finger_type=' . $type;
        return $this->_curl('/template_create.fcgi' . $param, $file, true);
    }

    /*
     * IDCLASS - Registrador Eletr�nico de Ponto
     */

    function add_users ($arr){
        $json = json_encode($arr);
        $data = '{"users":[' . $json . ']}';
        
        return $this->_curl('/add_users.fcgi', $data);
    }

    function remove_users ($arr){
        $json = json_encode($arr);
        $data = '{"users":' . $json . '}';
        echo $data;
        
        return $this->_curl('/remove_users.fcgi', $data);
    }

    function get_afd ($initial_date = NULL){
        $data = '{';
        if ($initial_date)
        {
            $data .= '"initial_date":';
            $data .= json_encode($initial_date);
        }
        $data .= '}';
        return $this->_curl('/get_afd.fcgi', $data);
    }

    /*
     * FUNÇÃO GENÉRICA QUE FAZ A REQUISIÇÃO
     * DE ACORDO COM O PROTOCOLO E IP CONFIGURADO,
     * INCLUI A SESSÃO CASO ESTEJA REGISTRADA E 
     * RETORNA O OBJETO ATRAVÉS DE JSON_DECODE
     */

    function _curl ($uri, $fields_string, $file = NULL){
        //$url = $this->protocol . '://' . $this->ip . $uri;
        $url = "https" . '://' . $this->ip . $uri;
        
        if ($this->ip = '192.168.99.49'){
        	$url = "https" . '://' . $this->ip . $uri;
        } else {
        	$url = "http" . '://' . $this->ip . $uri;
        }
        //$fields_string = str_replace('\/', '/', $fields_string);
        //$url = 'https://192.168.99.49/template_extract.fcgi?width=240;height=400';
        
        if ($this->session){
            $url .= (strstr($url, '?')) ? '&' : '?';
            $url .= 'session=' . $this->session;
        }
        
        $header = array();
        $header[] = 'Content-Length: ' . strlen($fields_string);
        
        if ($file){
            $header[] = 'Content-Type: application/octet-stream';
            $header[] = 'Expect:';
            
        } else {
            $header[] = 'Content-Type: application/json';
            $header[] = 'Expect:';
            $this->uri = $uri;
            $this->prepare = $fields_string;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //DEBUG
//        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);               
//        $verbose = fopen('php://temp', 'w+');
//        curl_setopt($ch, CURLOPT_STDERR, $verbose);
//        rewind($verbose);
//        $verboseLog = stream_get_contents($verbose);
//        print_pre(htmlspecialchars($verboseLog));
        $data['result'] = curl_exec($ch);
        $data['err'] = curl_errno($ch);
        $data['errmsg'] = curl_error($ch);
        $data['header'] = curl_getinfo($ch);
//        print_pre($uri);
//        print_pre($fields_string);
//        print_pre($data['result']);
        $result = json_decode($data['result']);
        
        return json_last_error() == JSON_ERROR_NONE ? $result : $data['result'];
    }

}
