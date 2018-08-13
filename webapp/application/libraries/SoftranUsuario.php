<?php

Class SoftranUsuario {

       var $CI;

       public function __construct(){
             $this->CI = &get_instance();
     } 

     public function getFuncionarioFreteiro($CPF){

             $result = $this->CI->db->query("SELECT * FROM SOFTRAN_MAGNA.GTCFRETE WHERE CDFRETEIRO = '$CPF' AND ININATIVO = 0")->result(); 

             if(!empty($result)){
                   return $result[0];
           }else{
                   return [];
           }
   }

   public function getFuncionario($CPF){
     $result = $this->CI->db->query("SELECT * FROM SOFTRAN_MAGNA.SISFUN WHERE NRCPF = '$CPF' AND NVL(FGDEMITIDO,0) = 0")->result(); 

     if(!empty($result)){
           return $result[0];
   }else{
           return [];
   }
}

 //TODO : Retornar a imagens do funcionario
public function getImgFunction($CPF){
     $result = $this->CI->db->query("SELECT CDFUNCIONARIO FROM SOFTRAN_MAGNA.SISFUN WHERE NRCPF = '$CPF' ")->result(); 

     if(!empty($result)){
 //$imgs = $this->CI->db->query("SELECT * FROM SOFTRAN_MAGNA.sisfunft WHERE CDFUNCIONARIO = {$result[0]->CDFUNCIONARIO}")->result(); 
           $imgs = $this->CI->db->query("SELECT * FROM SOFTRAN_MAGNA.sisfunft WHERE CDFUNCIONARIO = 6993")->result(); 
           if(!empty($imgs)){
                 header("Content-Type: image/png"); 
                 return base64_encode($imgs[0]->IMFOTO);
         }else{
                 return [];
         } 
 }else{
   return [];
}
}

}