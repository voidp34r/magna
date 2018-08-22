<?php

Class GraficoCentral{

    var $CI;

    public function __construct (){
            $this->CI = & get_instance();
    }

    public function returnGrafico($dtIni,$dtFim){
            
        $sql = "SELECT 
                    SUM(NVL(QUANTIDADECENTRAL,0)) AS QUANTIDADECENTRAL ,    
                    SUM(NVL(QUANTIDADESOFTRAN,0)) AS QUANTIDADESOFTRAN,
                    CDEMPRESA,
                    DSEMPRESA
                    FROM(".
                        $this->returnFieldSelect(true).$this->returnBodySql($dtIni,$dtFim).$this->returnWhere(true)
                        ." UNION ALL ".
                        $this->returnFieldSelect(false).$this->returnBodySql($dtIni,$dtFim).$this->returnWhere(false)
                    ."  )GROUP BY CDEMPRESA,DSEMPRESA order by quantidadeCentral desc ";

        $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");

       

       return $this->CI->db->query($sql)->result();

    }

    private function returnFieldSelect($softran){
            
        $sql = $softran ? " COUNT(*) AS QUANTIDADESOFTRAN, NULL AS QUANTIDADECENTRAL, " 
                        : " NULL AS QUANTIDADESOFTRAN , COUNT(*) AS QUANTIDADECENTRAL, ";
                
        return "SELECT ".$sql;

    }

    private function returnWhere($softran){

        $command = " (SELECT 
                    * 
                    FROM ENTREGA_0800INFO CENTRAL
                    WHERE CENTRAL.NRDOCTOFISCAL = A.NRDOCTOFISCAL
                    AND CENTRAL.CDFILIALORIGEM = A.CDEMPRESA
                    AND CENTRAL.CDULTIMAOCORRENCIA = 3) ";	
        
        
        
        /*Adiciona se existe ou não*/  
        $exist = $softran ? " AND NOT EXISTS " : " AND EXISTS ";
        
        /*CONCATENA*/	
        $exist .= $command ;	

        /*SE FOR SOFTRAN VALIDA BAIXA */
        $exist .=  $softran ? " AND B.CDOCORRENCIA = 1" : "";
        
        $exist .= "GROUP BY C.CDEMPRESA,C.DSEMPRESA";
        
        return $exist; 
        
    }

    private function returnBodySql($dtIni,$dtFim){

                
        return	" C.CDEMPRESA,
                  C.DSEMPRESA

                FROM SOFTRAN_MAGNA.GTCCONHE A 

                LEFT JOIN SOFTRAN_MAGNA.GTCMOVEN B 
                            ON B.NRSEQCONTROLE = A.NRSEQCONTROLE
                                AND B.CDEMPRESA = A.CDEMPRESA
                            
                LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C 
                            ON C.CDEMPRESA = A.CDEMPRESADESTINO
                            
                WHERE B.CDSEQUENCIA = (SELECT MAX(Z.CDSEQUENCIA) 
                                       FROM SOFTRAN_MAGNA.GTCMOVEN Z
                                       WHERE  Z.NRSEQCONTROLE= B.NRSEQCONTROLE
                                       AND   Z.CDEMPRESA = B.CDEMPRESA)

                                                                        
                AND TRUNC(B.DTMOVIMENTO) BETWEEN {$dtIni} AND {$dtFim}   
                
                ";

    }


    public function detalhamentoFilial($dtIni,$dtFim,$cdEmpresa){

        $sql = "SELECT MAX(NVL(QUANTIDADECENTRAL,0)) AS QUANTIDADECENTRAL,
                    MAX(NVL(QUANTIADESOFTRAN,0)) AS QUANTIADESOFTRAN,
                    MOTORISTA,
                    NOME
                FROM
                (SELECT NULL AS QUANTIDADECENTRAL,
                        COUNT(*) AS QUANTIADESOFTRAN,
                        MOTORISTA,
                        FUN.DSNOME AS NOME
                FROM
                    (SELECT SOFTRAN_MAGNA.ULTIMO_MOT_CTE(A.CDEMPRESA,A.NRSEQCONTROLE) AS MOTORISTA
                    FROM SOFTRAN_MAGNA.GTCCONHE A
                    LEFT JOIN SOFTRAN_MAGNA.GTCMOVEN B ON B.NRSEQCONTROLE = A.NRSEQCONTROLE
                    AND B.CDEMPRESA = A.CDEMPRESA
                    LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C ON C.CDEMPRESA = A.CDEMPRESADESTINO
                    WHERE B.CDSEQUENCIA =
                        (SELECT MAX(Z.CDSEQUENCIA)
                        FROM SOFTRAN_MAGNA.GTCMOVEN Z
                        WHERE Z.NRSEQCONTROLE= B.NRSEQCONTROLE
                            AND Z.CDEMPRESA = B.CDEMPRESA)
                        AND TRUNC(B.DTMOVIMENTO) BETWEEN {$dtIni} AND {$dtFim}
                        AND NOT EXISTS
                        (SELECT *
                        FROM ENTREGA_0800INFO CENTRAL
                        WHERE CENTRAL.NRDOCTOFISCAL = A.NRDOCTOFISCAL
                            AND CENTRAL.CDFILIALORIGEM = A.CDEMPRESA
                            AND CENTRAL.CDULTIMAOCORRENCIA = 3)
                        AND B.CDOCORRENCIA = 1
                        AND A.CDEMPRESADESTINO = {$cdEmpresa})
                LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP FUN ON FUN.NRCPF = MOTORISTA
                GROUP BY MOTORISTA,
                            FUN.DSNOME
                UNION ALL 
                SELECT COUNT(*) AS QUANTIDADECENTRAL,
                                    NULL AS QUANTIADESOFTRAN,
                                    MOTORISTA,
                                    FUN.DSNOME AS NOME
                FROM
                    (SELECT SOFTRAN_MAGNA.ULTIMO_MOT_CTE(A.CDEMPRESA,A.NRSEQCONTROLE) AS MOTORISTA
                    FROM SOFTRAN_MAGNA.GTCCONHE A
                    LEFT JOIN SOFTRAN_MAGNA.GTCMOVEN B ON B.NRSEQCONTROLE = A.NRSEQCONTROLE
                    AND B.CDEMPRESA = A.CDEMPRESA
                    LEFT JOIN SOFTRAN_MAGNA.SISEMPRE C ON C.CDEMPRESA = A.CDEMPRESADESTINO
                    WHERE B.CDSEQUENCIA =
                        (SELECT MAX(Z.CDSEQUENCIA)
                        FROM SOFTRAN_MAGNA.GTCMOVEN Z
                        WHERE Z.NRSEQCONTROLE= B.NRSEQCONTROLE
                            AND Z.CDEMPRESA = B.CDEMPRESA)
                        AND TRUNC(B.DTMOVIMENTO) BETWEEN {$dtIni} AND {$dtFim}
                        AND EXISTS
                        (SELECT *
                        FROM ENTREGA_0800INFO CENTRAL
                        WHERE CENTRAL.NRDOCTOFISCAL = A.NRDOCTOFISCAL
                            AND CENTRAL.CDFILIALORIGEM = A.CDEMPRESA
                            AND CENTRAL.CDULTIMAOCORRENCIA = 3)
                        AND A.CDEMPRESADESTINO = {$cdEmpresa} )
                LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP FUN ON FUN.NRCPF = MOTORISTA
                GROUP BY MOTORISTA,
                            FUN.DSNOME) 
                GROUP BY MOTORISTA,NOME ORDER BY QUANTIDADECENTRAL DESC";

                 $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
                 $retorno = $this->CI->db->query($sql)->result();

                 $clearResult = [];

                 foreach($retorno as $chave => $valor){
                    if($valor->MOTORISTA ){
                        if(!$valor->NOME){
                            $valor->NOME = "-";
                        }
                        array_push($clearResult,$valor);
                    }
                 }

                 return  $clearResult;

    }



    function getDesempenhoFiliais($cdEmpresa, $dtIni, $dtFim){
        
        $arrayInfo = ["central","softran"];
        
        //Pega as duas, tantos as entregas feitas pela softran, tanto as feitas pela central
        $arrayInfo["central"] = $this->geraIndicador(true,$cdEmpresa, $dtIni, $dtFim);
        $arrayInfo["softran"] = $this->geraIndicador(false,$cdEmpresa, $dtIni, $dtFim);
        
        return $arrayInfo;
    
    }

    private function geraIndicador($isCentral = false,$cdEmpresa, $dtIni, $dtFim){
        $dtIni = "$dtIni 00:00:00";
        $dtFim = "$dtFim 23:59:59" ;
        $type = !$isCentral ? 'NOT' : ''; 

        $sql = "SELECT 
                    CDEMPRESAFINAL,
                    DOCFINAL,
                    MOT,
                    CTE,
                    CDEMPRESACTE,
                    Z.DSNOME
                FROM  
                (
                    SELECT 
                    DESENV_TESTES.ULTIMO_EMP_DOC_CTE(A.CDEMPRESA, A.NRSEQCONTROLE) AS CDEMPRESAFINAL, 
                        SOFTRAN_MAGNA.ULTIMO_DOC_CTE(A.CDEMPRESA, A.NRSEQCONTROLE) AS DOCFINAL, 
                        SOFTRAN_MAGNA.ULTIMO_MOT_CTE(A.CDEMPRESA, A.NRSEQCONTROLE) AS MOT, 
                        A.NRDOCTOFISCAL AS CTE, 
                        A.CDEMPRESA AS CDEMPRESACTE
                FROM   SOFTRAN_MAGNA.GTCCONHE A 
                    LEFT JOIN SOFTRAN_MAGNA.GTCMOVEN B 
                            ON B.NRSEQCONTROLE = A.NRSEQCONTROLE 
                                AND B.CDEMPRESA = A.CDEMPRESA 
                WHERE  A.DTCANCELAMENTO IS NULL 
                    AND A.CDTPDOCTOFISCAL IN ( 3, 5 ) 
                    AND B.CDOCORRENCIA = 1 
                    AND B.DTMOVIMENTO BETWEEN '$dtIni' AND '$dtFim' 
                    AND $type EXISTS (SELECT * 
                                        FROM   DESENV_TESTES.ENTREGA_0800INFO ZZ 
                                        WHERE  ZZ.CDFILIALORIGEM = A.CDEMPRESA 
                                            AND ZZ.NRDOCTOFISCAL = A.NRDOCTOFISCAL 
                                            AND ZZ.CDULTIMAOCORRENCIA = 3)

                ".$this->adicionar_grupos_desconsideraveis($isCentral)." ) 
                LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP Z ON Z.NRCPF = MOT
                WHERE CDEMPRESAFINAL = $cdEmpresa  ORDER BY Z.DSNOME DESC";     

        $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
        return $this->CI->db->query($sql)->result();
    }

    function adicionar_grupos_desconsideraveis($isCentral){
       
        $where = "";

        //Pega todos os grupos de empresas
        $arrRes = $this->CI->db->query('SELECT CDGRUPOCLIENTE FROM GRUPO_EMPRESA_GRAFICO WHERE FGATIVO = 1')->result();

        foreach ($arrRes as $grupo) {
            //Pega todas as inscrições
            $arrEmp = $this->CI->db->query("SELECT CDINSCRICAO FROM SOFTRAN_MAGNA.SISCLIFA WHERE CDGRUPOCLIENTE = $grupo->CDGRUPOCLIENTE ")->result();
            
            //Adiciona todos os Cnpjs
            foreach ($arrEmp as $key => $obj) {
                if($key == 0 ){
                    $grupo = " '$obj->CDINSCRICAO' ";
                }else{
                    $grupo .= " ,'$obj->CDINSCRICAO'";
                }
            }
            //Seta o where
            $where .= " AND  ( A.CDINSCRICAO NOT IN ( $grupo )  AND A.CDREMETENTE NOT IN ( $grupo ) ) ";
        }
        
        //Caso não for central adicionar os grupos que é para desconsiderar, caso contrario retorna o where
        return !$isCentral ? $where : '';  
    }
 




}