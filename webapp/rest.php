<?php
    set_time_limit(2500);
    date_default_timezone_set("Brazil/East");
    $data = date('d/m/Y H:i:s');
    $hora = substr($data, 11, 2);
    $retorno = 0;
    switch($hora){
        case '08':
            $retorno = 1;
        break;
        case '14':
            $retorno = 2;
        break;
        case '18':
            $retorno = 3;
        break;
        case '23':
            $retorno = 4;
        break;
        default : 
            $retorno = 0;
        break;
    }
   if($retorno != 0){
        // Inicia o cURL
        $ch = curl_init();
        // Define a URL original (do formulário de login)
        curl_setopt($ch, CURLOPT_URL, 'http://webapp.transmagna.com.br/webservice/autenticacao');
        // Habilita o protocolo POST
        curl_setopt ($ch, CURLOPT_POST, 1);
        // Define os parâmetros que serão enviados (usuário e senha por exemplo)
        curl_setopt ($ch, CURLOPT_POSTFIELDS, 'usuario=ura_webservice&senha=magna_ws');
        // Imita o comportamento patrão dos navegadores: manipular cookies
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        // Define o tipo de transferência (Padrão: 1)
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        // Executa a requisição
        $store = curl_exec ($ch);        
        // Define uma nova URL para ser chamada (após o login)
        curl_setopt($ch, CURLOPT_URL, "http://webapp.transmagna.com.br/webservice/refeicao/{$retorno}");
        // Executa a segunda requisição
        $content = curl_exec ($ch);
        // Encerra o cURL
        curl_close ($ch);

        echo($content);
   }else{
       echo ("Fora de horario");
   }
?>