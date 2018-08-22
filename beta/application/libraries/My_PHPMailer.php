<?php 
    if ( ! defined('BASEPATH')) 
        exit('No direct script access allowed');
 
class My_PHPMailer {
    
    private static $host = "zimbra.transmagna.com.br";
    private static $userName = "alerta_mobile";
    private static $password = "342**447";
    private static $from = "alerta_mobile@transmagna.com.br";
    private static $fromName = "Alerta";
    
    
    public function My_PHPMailer() {
        require_once('PHPMailer/PHPMailerAutoload.php');
    }
    

    public function send_mail($destinatario,$conteudo,$assunto) {
        
	$Mailer = new PHPMailer();
	
	//Define que será usado SMTP
	$Mailer->IsSMTP();
	
	//Enviar e-mail em HTML
	$Mailer->isHTML(true);
	
	//Aceitar carasteres especiais
	$Mailer->CharSet = 'UTF-8';
	
	//Configurações
	$Mailer->SMTPAuth = true;
		
	//nome do servidor
	$Mailer->Host = $this::$host;

	
	//Dados do e-mail de saida - autenticação
	$Mailer->Username = $this::$userName;
	$Mailer->Password = $this::$password;
	
	//E-mail remetente (deve ser o mesmo de quem fez a autenticação)
	$Mailer->From = $this::$from;
	
	//Nome do Remetente
	$Mailer->FromName = $this::$fromName;
	
	//Assunto da mensagem
	$Mailer->Subject = $assunto;
	
	//Corpo da Mensagem
	$Mailer->Body = $conteudo;
	
	//Destinatario 
	$Mailer->AddAddress($destinatario);
	
	if($Mailer->Send()){
	    return true;
	}else{
            return false;
	}

    }    
    
 }
