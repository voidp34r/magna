<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class SoftranEntregasDB {
	var $CI;

    public function __construct (){
        $this->CI = &get_instance();
    }

    /**
     * Busca os CTE´s de motoristas (freteiros ou funcionários)
     * Documentos elegíveis à ocorrência de entrega:
     *    - CDTPDOCTOFISCAL = 3 (conhecimento)
     *    - Não cancelados (Sem data de cancelamento)
     *    - Não entregues (Sem data de entrega)
     *    
     * @param string $cpfCnpjMotorista 
     */
    public function getCtesDoMotorista($cpfCnpjMotorista){
        
	$query = "  SELECT
                    C.CDEMPRESA as empresa ,
                    C.NRDOCTOFISCAL as nrDocto,
                    '1' as opcoes,
                    D.DSAPELIDO as DESTINATARIO,
                    E.DSAPELIDO as REMETENTE,
                    C.VLFRETEVALOR,
                    C.QTVOLUME
                    FROM SOFTRAN_MAGNA.CCEROMIT A
                    LEFT JOIN SOFTRAN_MAGNA.CCEROMAN B ON A.CDEMPRESA = B.CDEMPRESA AND A.CDROTA = B.CDROTA AND A.CDROMANEIO = B.CDROMANEIO
                    LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON A.CDEMPRESACOLETAENTREGA = C.CDEMPRESA AND A.NRSEQCONTROLE = C.NRSEQCONTROLE
                    LEFT JOIN SOFTRAN_MAGNA.SISCLI D on D.CDINSCRICAO = C.CDDESTINATARIO
                    LEFT JOIN SOFTRAN_MAGNA.SISCLI E on E.CDINSCRICAO = C.CDREMETENTE 
                    WHERE 
                    -- VALIDA STATUS ENTREGA
                    A.INSITUACAO is null
                    -- VALIDA SE FOI RETIRADO DO ROMANEIO
                    AND NVL(B.INSITUACAO,0) = 0
                    -- VALIDA SE O CTE NAO FOI ENTREGUE!
                    AND (C.DTENTREGA IS NULL)
                    -- VALIDA SE O CTE FOI CANCELADO
                    AND NVL(C.INCONHECIMENTO,0) = 0 
                    -- PEGA ULTIMO ROMANEIO PARA ESTE CTE, GARANTINDO A BAIXA APENAS PARA O ULTIMO MOTORISTA QUE CARREGOU A MERCADORIA
                    AND B.DTROMANEIO = 
                    (SELECT MAX(ZZ.DTROMANEIO) FROM SOFTRAN_MAGNA.CCEROMAN ZZ
                    LEFT JOIN SOFTRAN_MAGNA.CCEROMIT GG ON ZZ.CDEMPRESA = GG.CDEMPRESA AND ZZ.CDROTA = GG.CDROTA AND ZZ.CDROMANEIO = GG.CDROMANEIO
                    WHERE GG.NRSEQCONTROLE = C.NRSEQCONTROLE AND GG.CDEMPRESACOLETAENTREGA = C.CDEMPRESA ) 
                    AND
                    B.NRCPFMOTORISTA = '{$cpfCnpjMotorista}'
                    AND NOT EXISTS (SELECT * FROM ENTREGA_0800INFO 
                    WHERE CDFILIALORIGEM = C.CDEMPRESA 
                    AND NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                    AND CDULTIMAOCORRENCIA  = 3  ) 
                    AND C.CDTPDOCTOFISCAL in (5,3)
                    UNION ALL
                    SELECT 
                    C.CDEMPRESA as empresa,
                    C.NRDOCTOFISCAL as nrDocto,
                    '1' as opcoes,
                    D.DSAPELIDO as DESTINATARIO,
                    E.DSAPELIDO as REMETENTE,
                    C.VLFRETEVALOR,
                    C.QTVOLUME
                    FROM SOFTRAN_MAGNA.GTCMANCN A 
                    LEFT JOIN SOFTRAN_MAGNA.GTCMAN B ON A.NRMANIFESTO = B.NRMANIFESTO
                    LEFT JOIN SOFTRAN_MAGNA.GTCCONHE C ON A.CDEMPRESA = C.CDEMPRESA AND A.NRSEQCONTROLE = C.NRSEQCONTROLE
                    LEFT JOIN SOFTRAN_MAGNA.SISCLI D on D.CDINSCRICAO = C.CDDESTINATARIO
                    LEFT JOIN SOFTRAN_MAGNA.SISCLI E on E.CDINSCRICAO = C.CDREMETENTE 
                    WHERE                    
                    -- VALIDA SE FOI RETIRADO DO ROMANEIO
                    NVL(B.INSITUACAO,0) = 0
                    AND NVL(A.INSITUACAO,0) = 0
                    -- VALIDA SE O CTE NAO FOI ENTREGUE!
                    AND (C.DTENTREGA IS NULL)
                    -- VALIDA SE O CTE FOI CANCELADO
                    AND NVL(C.INCONHECIMENTO,0) = 0
                    AND B.DTEMISSAO = (SELECT MAX(ZZ.DTEMISSAO) FROM SOFTRAN_MAGNA.GTCMAN ZZ
                    LEFT JOIN SOFTRAN_MAGNA.GTCMANCN GG ON ZZ.NRMANIFESTO = GG.NRMANIFESTO
                    WHERE GG.NRSEQCONTROLE = C.NRSEQCONTROLE AND GG.CDEMPRESA = C.CDEMPRESA)
                    AND B.CDMOTORISTA = '{$cpfCnpjMotorista}'
                    --AND B.DTCHEGADA IS NULL 
                    AND NOT EXISTS (SELECT * FROM ENTREGA_0800INFO 
                                    WHERE CDFILIALORIGEM = C.CDEMPRESA 
                                    AND NRDOCTOFISCAL = C.NRDOCTOFISCAL 
                                    AND CDULTIMAOCORRENCIA  = 3 )
                    AND C.CDTPDOCTOFISCAL in (5,3)                " ;
                    
        $RetornoSoftran = $this->CI->db
                          ->query($query)
                          ->result();
        
        
        foreach ($RetornoSoftran as $Conhecimento ) {                
             //Verifica se tem alguma ocorrência lançada para este conhecimento no webapp   
             $querySituacao = " SELECT * 
                                FROM ENTREGA_0800INFO 
                                WHERE CDFILIALORIGEM = {$Conhecimento->EMPRESA} 
                                AND NRDOCTOFISCAL    = '{$Conhecimento->NRDOCTO}' ";


             $RetornoWebApp = $this->CI->db
                              ->query($querySituacao)
                              ->result(); 

             //Caso tenha ocorrência irá busca o utlimo e verificar qual é a ocorrência
             if(!empty($RetornoWebApp)){
                $ultimaDataMovimento = $this->retornaUltimaData($Conhecimento->EMPRESA, $Conhecimento->NRDOCTO);
                $Ocorrencia = $this->retornaUltimoMovimentoData($Conhecimento->NRDOCTO, $Conhecimento->EMPRESA, $ultimaDataMovimento);
                switch ($Ocorrencia) {
                    
                    //Entrega Iniciada
                    case 1:
                        $Conhecimento->OPCOES = '3,5';                       
                        break;
                    //Problema na Entrega
                    case 5:
                        $Conhecimento->OPCOES = '1';                       
                        break;
                 }
                 
             }else{
                 $Conhecimento->OPCOES = '1';
             }
            
                                       
             
             
             
         }
        return $RetornoSoftran;
    }
    
    function retornaUltimaData($cdEmpresa,$nrDoctoFiscal){
        
        return  $this->CI->db->query("SELECT 
                             MAX(to_char(to_date(DtIni,'YYYY/MM/DD HH24:MI::SS'),'DD/MM/YYYY HH24:MI:SS')) as DTINI
                             FROM ENTREGA_0800INFO
                             WHERE CDFILIALORIGEM = {$cdEmpresa}
                             AND NRDOCTOFISCAL = {$nrDoctoFiscal}")->result()[0]->DTINI;

    }
    
    function retornaUltimoMovimentoData($nrDoctoFiscal,$cdEmpresa,$Data){
        
        return  $this->CI->db->query("SELECT 
                                  CDULTIMAOCORRENCIA   
                                  FROM ENTREGA_0800INFO
                                  WHERE CDFILIALORIGEM = {$cdEmpresa}
                                  AND NRDOCTOFISCAL = {$nrDoctoFiscal}
                                  AND to_char(to_date(DTINI,'YYYY/MM/DD HH24:MI::SS'),'DD/MM/YYYY HH24:MI:SS') =  '{$Data}' ")->result()[0]->CDULTIMAOCORRENCIA;

    }
    
}
