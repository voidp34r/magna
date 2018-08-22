<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class PortariaLogin
{
   
   var $CI;
   
   function __construct() {
       
       $this->CI = & get_instance();
   }
   
   public function logarPortaria($usuario,$senha){
    
       $res = $this->CI->db->query("SELECT * FROM USUARIO_PORTARIA WHERE USUARIO = '{$usuario}' AND ISATIVO = 1 ");
       
       if($res->num_rows()){
           
           if(password_verify($senha, $res->result()[0]->SENHA)){
                
               $dados = array(
                            'usuario' => $res->result()[0]->USUARIO,
                            'usuario_nome' => $res->result()[0]->USUARIO_NOME,
                            'usuario_email' => '',
                            'logado' => true
                          );   
                $this->CI->session->set_userdata($dados);
                return true;
            }else{
               return false;
           }
           
       }else{
           return false;
       }
       
   }
   
   public function getFilialUsuarioPortaria($usuario){
       return $this->CI->db->query("SELECT  
                                    CDEMPRESA 
                                    FROM USUARIO_PORTARIA 
                                    WHERE USUARIO = '{$usuario}' ")
                           ->result()[0]->CDEMPRESA;
   }
}
