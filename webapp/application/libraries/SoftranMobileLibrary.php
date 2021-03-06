<?php 

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class SoftranMobileLibrary{
    
    var $CI;
    
    public function __construct() {
        $this->CI = &get_instance();
    }
    
    /**
     * Consulta o Softran e retorna a Km do veículo
     * @param nrPlacaVeiculo Placa do veiculo a ser consultado
     */
    public function retornaHodometroVeiculo($nrPlacaVeiculo){
        
        $ObjetoRetorno = new stdClass();
        $retorno =  $this->CI->db
                          ->query("SELECT CDVEICULO,NVL(NRHODATUAL,0) AS NRHODATUAL FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '{$nrPlacaVeiculo}'");
                  
        if($retorno->num_rows()){
            
            if($retorno->num_rows() > 1){
                $ObjetoRetorno->ok = false;
                $ObjetoRetorno->mensagem = "Existe mais de um veículo com essa placa no sistema";                
            }else{
                $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                $data  = $this->CI->db->query(" SELECT 
                                                  CDVEICULO,
                                                  TO_CHAR(DTATUALIZACAO,'DD/MM/YYYY') AS DTATUALIZACAO ,
                                                  CDSEQUENCIA,
                                                  TO_CHAR(HRINCLUSAO,'HH24:MI:SS') AS HRATUALIZACAO,
                                                  NRHODHOR 
                                                  FROM 
                                                  SOFTRAN_MAGNA.SISHODAT 
                                                  WHERE CDVEICULO = {$retorno->result()[0]->CDVEICULO} 
                                                  AND CDSEQUENCIA = (SELECT 
                                                                       MAX(CDSEQUENCIA) 
                                                                       FROM SOFTRAN_MAGNA.SISHODAT 
                                                                       WHERE CDVEICULO = {$retorno->result()[0]->CDVEICULO}) ");
                                                                       
                //Workaround - Verifica se o hodometro do cadastro do veículo(SISVEICU) é maior que da tabela de Atualizações de Hodometro(SISHODAT) 
                if($data->num_rows())
                {
                    $ObjetoRetorno->obj = $data->result()[0];
                }else{
                    //Não tem nenhum registro na tabela SISHODAT
                    $ObjetoRetorno->obj = new StdClass();
                    $ObjetoRetorno->obj->CDVEICULO = $retorno->result()[0]->CDVEICULO;
                    $ObjetoRetorno->obj->CDSEQUENCIA = 1;
                    $ObjetoRetorno->obj->NRHODHOR = 0;
                }
                
                if($retorno->result()[0]->NRHODATUAL > 0 ||  $ObjetoRetorno->obj->NRHODHOR > 0)
                {
                    $hodometro = $retorno->result()[0]->NRHODATUAL >  $ObjetoRetorno->obj->NRHODHOR 
                                                                ? $retorno->result()[0]->NRHODATUAL : $ObjetoRetorno->obj->NRHODHOR;
                }else{
                    $hodometro = 0;
                }

                $ObjetoRetorno->obj->NRHODHOR = $hodometro;
                $ObjetoRetorno->ok = true; 
                $ObjetoRetorno->obj->NRPLACA = $nrPlacaVeiculo ;
            }
        }else{
            $ObjetoRetorno->ok = false;
            $ObjetoRetorno->mensagem = "Placa não encontrada";
        }
        
        return $ObjetoRetorno;
                    
    }

    /**
     * Atualiza a data e hora da saida ou chegada do romaneio apos o checklist 
     * @param cdRota Codigo da rota
     * @param cdRomaneio Codigo do romaneio
     * @param empresa Codigo da empresa
     */
    public function atualizaSaidaChegada($cdRota,$cdRomaneio,$empresa){
        $retorno = new stdClass();
        $queryHora =  $this->CI->db->query("SELECT TO_CHAR(sysdate, 'HH24:MI') as HORA FROM dual ")->result()[0];

        $hora =  "30/12/1899 {$queryHora->HORA}";
        $docChecklist =  $this->CI->db->query("SELECT * from PORTARIA_CHECKLIST_DOCUMENTO 
                                                   WHERE CDROTA = {$cdRota} 
                                                     AND CDROMANEIO = {$cdRomaneio} 
                                                     AND CDEMPRESA = {$empresa}");
                             
        if($docChecklist->num_rows()){
            //Atualizar Chegada
            $saida = $this->CI->db->query("UPDATE SOFTRAN_MAGNA.CCEROMAN 
                                                SET DTCHEGADA = to_char(SYSDATE,'DD/MM/YYYY'),
                                                    HRCHEGADA = to_date('{$hora}','DD/MM/YYYY HH24:MI')
                                                WHERE CDEMPRESA = {$empresa} AND CDROTA = {$cdRota} AND CDROMANEIO = {$cdRomaneio}");         
            if($saida){
                $retorno->ok = true;
                $retorno->mensagem = "Operação Realizada com sucesso";    
            }else{
                $retorno->ok = false;
                $retorno->mensagem = "Erro ao atualizar o cadastro do ROMANEIO";                     
            }                         
        }else{
            //Atualizar Saida
            $saida = $this->CI->db->query("UPDATE SOFTRAN_MAGNA.CCEROMAN 
                                                SET DTSAIDA = to_char(SYSDATE,'DD/MM/YYYY'),
                                                    HRSAIDA = to_date('{$hora}','DD/MM/YYYY HH24:MI')
                                                WHERE CDEMPRESA = {$empresa} AND CDROTA = {$cdRota} AND CDROMANEIO = {$cdRomaneio}");                 
            if($saida){
                $retorno->ok = true;
                $retorno->mensagem = "Operação Realizada com sucesso";    
            }else{
                $retorno->ok = false;
                $retorno->mensagem = "Erro ao atualizar o cadastro do ROMANEIO";                     
            }            
        }
        return $retorno->ok;
    }
    
    public function atualizaHodometro($cdVeiculo,$kmAtual,$novoKm,$cdSequencia,$usuario){
        $retorno = new stdClass();
        
        $queryHora =  $this->CI->db->query("SELECT TO_CHAR(sysdate, 'HH24:MI') as HORA FROM dual ")
                         ->result()[0];

        $Hora =  "30/12/1899 {$queryHora->HORA}" ;
            $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
            
            $idHodAt = $this->CI->db->query("INSERT INTO SOFTRAN_MAGNA.SISHODAT   
                                            (CDVEICULO,
                                             DTATUALIZACAO,
                                             DTINCLUSAO,
                                             CDSEQUENCIA,
                                             HRINCLUSAO,
                                             HRATUALIZACAO,
                                             CDATUALIZACAOHOD,
                                             NRHODHOR,
                                             NRHODHORANT,
                                             DSUSUARIOINC)
                                             VALUES
                                            ({$cdVeiculo},
                                             to_char(SYSDATE,'DD/MM/YYYY'),
                                             to_char(SYSDATE,'DD/MM/YYYY'),
                                             {$cdSequencia},
                                             to_date('{$Hora}','DD/MM/YYYY HH24:MI'),
                                             to_date('{$Hora}','DD/MM/YYYY HH24:MI'),
                                             1,
                                             {$novoKm},
                                             {$kmAtual},
                                             '{$usuario}' )
                                            ");
                                             
            if($idHodAt){
                
                $idHodVeic = $this->CI->db->query("UPDATE SOFTRAN_MAGNA.SISVEICU 
                                                  SET NRHODATUAL = {$novoKm} 
                                                  WHERE CDVEICULO = {$cdVeiculo}"); 
                
                 if($idHodVeic){
                    $retorno->ok = true;
                    $retorno->mensagem = "Operação Realizada com sucesso";    
                 }else{
                    $retorno->ok = false;
                    $retorno->mensagem = "Erro ao atualizar o cadastro do veículo";                     
                 }
                
            }else{
                $retorno->ok = false;
                $retorno->mensagem = "Erro ao atualizar o histórico";                
            }                                 
        
        
        return $retorno->ok;
    }


    public function getPlateWithCode($code){
        
        $query =  $this->CI->db->query("SELECT  NRPLACA FROM SOFTRAN_MAGNA.SISVEICU WHERE NRVEICULO  = '{$code}'");
        $retorno = new StdClass();

        if($query->num_rows()){
            $retorno->status = true;
            $retorno->nrPlaca = $query->result()[0]->NRPLACA;
        }else{
            $retorno->status = false;
        }
        return $retorno;

    }
}