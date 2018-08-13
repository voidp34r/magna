<?php
    set_time_limit(2500);
        // Inicia o cURL
        $ch = curl_init();
        // Define a URL original (do formulário de login)
        curl_setopt($ch, CURLOPT_URL, 'http://webapp.transmagna.com.br/webservice/autenticacao');
        // Habilita o protocolo POST
        curl_setopt ($ch, CURLOPT_POST, 1);
        // Define os parâmetros que serão enviados (usuário e senha por exemplo)
        curl_setopt ($ch, CURLOPT_POSTFIELDS, 'usuario=ura_webservice&senha=magna_ws');
        // Imita o comportamento patrão dos navegadores: manipular cookies
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie2.txt');
        // Define o tipo de transferência (Padrão: 1)
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        // Executa a requisição
        $store = curl_exec ($ch);        
        // Define uma nova URL para ser chamada (após o login)
        curl_setopt($ch, CURLOPT_URL, "http://webapp.transmagna.com.br/rh_refeicao/import_remove");
        // Executa a segunda requisição
        $content = curl_exec ($ch);
        // Encerra o cURL
        curl_close ($ch);

        echo($content);
?>