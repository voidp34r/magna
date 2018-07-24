<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Dev_utils{
	var $CI;

    public function __construct (){
        $this->CI = & get_instance();
    }
    
    public function criar_arquivo($nome_arquivo, $conteudo, $modo = "w"){
    	$fp = fopen($nome_arquivo, $modo);
    	$escreve = fwrite($fp, $conteudo);
    	fclose($fp);
    	
    	echo "dev_utils::criar_arquivo: ".$nome_arquivo.": ".$escreve;
    	
    	return $escreve ? true : false;
    }
    
    public function ler_arquivo($nome_arquivo, $modo = "r"){
    	$fp = fopen($nome_arquivo, $modo);
    	$conteudo = fread($fp, filesize($nome_arquivo));
    	fclose($fp);
    	 
    	echo "dev_utils::ler_arquivo: ".$nome_arquivo.": ".$conteudo;
    	 
    	return $conteudo;
    }
    
}
