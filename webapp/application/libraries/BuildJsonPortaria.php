<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class BuildJsonPortaria
{

    private $instanciaIdClass;

    public function __construct()
    {
        $this->CI = &get_instance();
        
        date_default_timezone_set("Brazil/East");    

    }

    public function setInstancia($equipInstancia){
        $this->instancia = $equipInstancia;
    }

    public function buildJson($empUsuario)
    {   
        $dtRegistro = date("d/m/Y H:i", strtotime("-30 min")); 

        //Pega todas as batidas, de hoje e de ontem
        $arrUltimasBatidas = $this->getQueryRegistro($dtRegistro);
        
        $objRetorno = new StdClass();
        $objRetorno->arrOcorrencias = [];
        $objRetorno->arrCheckLists = [];

        foreach($arrUltimasBatidas as $batidas)
        {   

            //Preenche o objeto que será enviado para o mobile
            $motorista  = new StdClass();
            $motorista->DTBATIDA = $batidas->DATA;
            $motorista->CPFMOT   = formata_cpf(substr($batidas->USUARIOCPF,3));
            $motorista->NOMEMOT  = $batidas->DSNOME; 

            //Verifica se esse motorista tem alguma ocorrência pendente
            $ocorrenciaMot = $this->getOcorrenciaMotorista($batidas->USUARIOCPF);

            //Caso não tenha nenhuma ocorrência pendente, carrega os documentos em aberto que não tiverem checklist
            if(empty($ocorrenciaMot))
            {   
                //Verifica se o registro do ponto é maior que a ultima ocorrência.
                if($this->maiorQueUltimaOcorrencia($batidas->USUARIOCPF,$motorista->DTBATIDA))
                {
                    //Carrega todos os documentos aberto para aquele motorista emitidos até dois dias antes da batida
                    $motorista->DOCS  = $this->retornaDocumentosMotorista($batidas->USUARIOCPF,$batidas->DATA,$empUsuario);
                    $motorista->VEICULOS = $this->retornaVeiculosDocs($motorista->DOCS);  

                    if(!empty($ocorrLiberada = $this->retorna_ocorrencia_ignorada($batidas->USUARIOCPF))){
                         
                        if(count($ocorrLiberada) > 0){
                            $motorista->skipStep = [];
                            foreach($ocorrLiberada as $data){
                                array_push($motorista->skipStep,$data->STEP_CHECKLIST);
                            }
                        }else{
                            $motorista->skipStep = $ocorrLiberada[0]->STEP_CHECKLIST;
                        }
                    }
                
                    if(empty($motorista->DOCS))
                    {
                        $motorista->ABRIROCORR = 1;
                        $motorista->OCORRRENCIA = "Motorista sem documento Romaneio/Manifesto";
                    }else{
                        //Calcula o peso total dos manifestos
                        if(!empty($motorista->DOCS['MANIFESTOS'])){
                            $qtPeso = 0;
                            foreach ($motorista->DOCS['MANIFESTOS'] as $manif) {
                                $qtPeso = $qtPeso + floatval($manif->PESO);
                            }
                        }
                        $motorista->EIXOABAIXADO = $qtPeso > 6000;
                        $motorista->PESOTOTALMANIF = $qtPeso;
                    }                

                    array_push($objRetorno->arrCheckLists,$motorista);
                }


            }else{
                
                array_push($objRetorno->arrOcorrencias,$motorista);
            }

        }            
        return $objRetorno;
    }   

    /** 
     * Este método retorna se existe alguma permissão de liberação dada no encerramento da ocorrência
     * @param $cpf - Cpf do motorista 
     */
    private function retorna_ocorrencia_ignorada($cpf){
        date_default_timezone_set("Brazil/East");
        $dtIni = date("d/m/Y H:i:s", strtotime("-60 min")); 
        $dtAtual = date("d/m/Y H:i:s");
        $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
        $rs = $this->CI->db->query("SELECT 
                                    STEP_CHECKLIST
                                    FROM     
                                        PORTARIA_CHECKLIST_OCORRENCIA 
                                    WHERE CPFMOTORISTA = '$cpf'
                                    AND to_date(DTRESOLUCAO,'YYYYMMDDHH24MISS') between '$dtIni' AND '$dtAtual'
                                    AND FGRESOLVIDO = 2 -- 2 é flag que é parar desconsiderar o problema da ocorrência no checklist seguinte para o motoritsa");

        return $rs->result();
    }

    private function retornaVeiculosDocs($arrInfo)
    {       
        $veiculos = [];

        //Como está num loop é obrigado validar se a função já esta criada para não criar novamente
        if (!function_exists("veicInArray")) 
        {   
            //Esta função valida se o veículo passado como parametro já esta no array
            function veicInArray($veiculos,$placa)
            {   
                $inArr = false;

                foreach($veiculos as $veiculo)
                {
                    if($veiculo->placa == $placa)
                    {
                        $inArr = true;
                    }
                }   

                return $inArr;
            }    
        }


        foreach($arrInfo as $chave => $objs)
        {   
            if($chave != "QTDOCUMENTOS" )
            {
                foreach($objs as $obj)
                {
                    if(isset($obj->NRPLACA))
                    {
                        if(!veicInArray($veiculos,$obj->NRPLACA))
                        {   
                            $veiculo = new StdClass();
                            $veiculo->placa =  $obj->NRPLACA;
                            $veiculo->renavam =   $obj->NRPLACA_RENAVAN;
                            $veiculo->hodometro = $obj->NRPLACA_HODOMETRO;
                            array_push($veiculos,$veiculo);
                        }
                    }

                    if(isset($obj->NRPLACACARRETA))
                    {   
                        if(!veicInArray($veiculos,$obj->NRPLACACARRETA))
                        {   
                            $veiculo = new StdClass();
                            $veiculo->placa =  $obj->NRPLACACARRETA;
                            $veiculo->renavam =   $obj->NRPLACACARRETA_RENAVAN;
                            $veiculo->hodometro = $obj->NRPLACACARRETA_HODOMETRO;
                            array_push($veiculos,$veiculo);
                        }    
                    }

                    if(isset($obj->NRPLACAREBOQUE1))
                    {
                        if(!veicInArray($veiculos,$obj->NRPLACAREBOQUE1))
                        {  
                            $veiculo = new StdClass();
                            $veiculo->placa =  $obj->NRPLACAREBOQUE1;
                            $veiculo->renavam =   $obj->NRPLACAREBOQUE1_RENAVAN;
                            $veiculo->hodometro = $obj->NRPLACAREBOQUE1_HODOMETRO;
                            array_push($veiculos,$veiculo);   
                        }
                    }

                    if(isset($obj->NRPLACAREBOQUE2))
                    {
                        if(!veicInArray($veiculos,$obj->NRPLACAREBOQUE2))
                        { 
                            $veiculo = new StdClass();
                            $veiculo->placa =  $obj->NRPLACAREBOQUE2;
                            $veiculo->renavam =   $obj->NRPLACAREBOQUE2_RENAVAN;
                            $veiculo->hodometro = $obj->NRPLACAREBOQUE2_HODOMETRO;
                            array_push($veiculos,$veiculo);  
                        }
                    }

                    if(isset($obj->NRPLACAREBOQUE3))
                    {
                        if(!veicInArray($veiculos,$obj->NRPLACAREBOQUE3))
                        {    
                            $veiculo = new StdClass();
                            $veiculo->placa =  $obj->NRPLACAREBOQUE3;
                            $veiculo->renavam =   $obj->NRPLACAREBOQUE3_RENAVAN;
                            $veiculo->hodometro = $obj->NRPLACAREBOQUE3_HODOMETRO;
                            array_push($veiculos,$veiculo);
                        }
                    }
                }
                  
            }          
        }           
        
        return $veiculos;
    }




    private function getQueryRegistro($dtRegistro) 
    {   
       $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");
       
        /*
            O Comando tem o distinct pois pode acontencer do mesmo usuário ter "batido" o ponto no mesmo minuto e o relogio ponto não passa os segundos
            pela api, sendo assim não é salvo no banco.
        */
       return $this->CI->db->query("SELECT 
                                    DISTINCT
                                    TO_DATE(A.DTREGISTRO,'YYYYMMDDHH24MISS') AS DATA,
                                    B.DSNOME,
                                    A.USUARIOCPF
                                    FROM BIOMETRIA_REGISTRO  A
                                    LEFT JOIN SOFTRAN_MAGNA.GTCFUNDP B ON B.NRCPF = A.USUARIOCPF
                                    WHERE A.DTREGISTRO = (SELECT MAX(ZZ.DTREGISTRO) FROM BIOMETRIA_REGISTRO ZZ WHERE ZZ.USUARIOCPF = A.USUARIOCPF)
                                    AND TO_DATE(A.DTREGISTRO,'YYYYMMDDHH24MISS') >  '$dtRegistro'
                                    order by TO_DATE(A.DTREGISTRO,'YYYYMMDDHH24MISS') desc")->result();
    }


    private function getOcorrenciaMotorista($cpfMotorista)
    {
        //Carrega a instancia do model de ocorrencias
        $this->CI->load->model('frotas_portaria/portaria_checklist_ocorrencia_model');

        //Verifica se tem alguma ocorrência aberta para aquele motorista
        return $this->CI->portaria_checklist_ocorrencia_model->get_all(["CPFMOTORISTA" => $cpfMotorista , "FGRESOLVIDO " => 0]);
    }


    private function retornaDocumentosMotorista($cpfMotorista,$data,$cdEmpresaUsu)
    {  

       //Intervalo de data de 2 dias anterior a batida    
       $dt = explode('/',substr($data,0,10));
       $dtIni = date('d/m/Y', strtotime('-10 day',mktime(0,0,0,$dt[1],$dt[0],$dt[2])));
       $dtFim = date('d/m/Y', mktime(0,0,0,$dt[1],$dt[0],$dt[2]));
       //Fuso horario
       date_default_timezone_set("Brazil/East");
       $horaAtual = date('H:i:s');   

       $manifestos =  $this->CI->db->query("SELECT  
                                            A.NRMANIFESTO,
                                            TRUNC(A.DTEMISSAO) AS DTEMISSAO,
                                            A.NRPLACA,
                                            A.NRPLACACARRETA,
                                            A.NRPLACAREBOQUE2,
                                            A.NRPLACAREBOQUE3, 
                                            SOFTRAN_MAGNA.PESO_MANIFESTO(A.NRMANIFESTO) AS PESO
                                            FROM SOFTRAN_MAGNA.GTCMAN A
                                            LEFT JOIN SOFTRAN_MAGNA.SISROTA B ON B.CDROTA = A.CDROTA
                                            WHERE A.CDMOTORISTA = '{$cpfMotorista}'
                                            AND A.DTCANCELAMENTO IS NULL
                                            AND (A.DTCHEGADA IS NULL OR ((TO_DATE(TO_CHAR(SYSDATE, 'DD/MM/YYYY')||' '||'{$horaAtual}', 'DD/MM/YYYY HH24:MI:SS')	-
			                                                              TO_DATE(TO_CHAR(A.DTCHEGADA, 'DD/MM/YYYY')||' '||TO_CHAR(A.HRCHEGADA, 'HH24:MM:SS'), 'DD/MM/YYYY HH24:MI:SS')) * 1440 > 0 AND 
			                                                             (TO_DATE(TO_CHAR(SYSDATE, 'DD/MM/YYYY')||' '||'{$horaAtual}', 'DD/MM/YYYY HH24:MI:SS')	-
			                                                              TO_DATE(TO_CHAR(A.DTCHEGADA, 'DD/MM/YYYY')||' '||TO_CHAR(A.HRCHEGADA, 'HH24:MM:SS'), 'DD/MM/YYYY HH24:MI:SS')) * 1440 <= 30 ))
                                            AND A.INIMPRESSO = 1
                                            AND NOT EXISTS (SELECT * FROM 
                                                                 PORTARIA_CHECKLIST_DOCUMENTO ZZ
                                                                 LEFT JOIN PORTARIA_CHECKLIST XX 
                                                                                    ON XX.ID = ZZ.ID_CHECKLIST  
                                                                 LEFT JOIN USUARIO XZ 
                                                                                    ON XZ.ID = XX.USUARIO_ID                       
                                                                 WHERE ZZ.NRMANIFESTO = A.NRMANIFESTO
                                                                 AND XZ.CDEMPRESA = $cdEmpresaUsu
                                                                 )
                                            AND TRUNC(A.DTEMISSAO) BETWEEN '{$dtIni}' AND '{$dtFim}' 
                                            ORDER BY A.DTEMISSAO DESC")->result();
      
       $romaneios = $this->CI->db->query("SELECT 
                                            A.CDEMPRESA,
                                            A.CDROTA,
                                            A.CDROMANEIO,
                                            TRUNC(A.DTROMANEIO) AS DTROMANEIO,
                                            A.NRPLACA,
                                            A.NRPLACACARRETA,
                                            A.NRPLACAREBOQUE1,
                                            A.NRPLACAREBOQUE2,
                                            A.NRPLACAREBOQUE3
                                            FROM SOFTRAN_MAGNA.CCEROMAN A
                                            WHERE A.NRCPFMOTORISTA = '{$cpfMotorista}'
                                            AND EXISTS
                                                (SELECT *
                                                FROM SOFTRAN_MAGNA.CCERomIt RI
                                                WHERE RI.CdEmpresa = A.CDEMPRESA
                                                AND RI.CdRota = A.CDROTA
                                                AND RI.CdRomaneio = A.CDROMANEIO
                                                AND (ri.insituacao IS NULL OR ri.insituacao = 0) )
                                            AND A.DTBAIXA IS NULL
                                            AND A.DTCANCELAMENTO IS NULL
                                            AND A.DTCHEGADA IS NULL
                                            AND TRUNC(A.DTROMANEIO) BETWEEN '{$dtIni}' AND '{$dtFim}'
                                            ORDER BY A.DTROMANEIO DESC")->result(); 

        if(!empty($manifestos) || !empty($romaneios))
        {
            $documentos = ["ROMANEIOS" => [] , "MANIFESTOS" => [] ];
            
            if(!empty($manifestos))
            {   
                foreach($manifestos as $manifesto)
                {   

                    $manifesto =  $this->addRenavamRemoveTerceiro($manifesto,2); 
                    $qrCode = "2§".$manifesto->NRMANIFESTO;    
                    $manifesto->QRCODE = $qrCode; 
                
                }

                $documentos["MANIFESTOS"] = $manifestos;
            }
            

            if(!empty($romaneios))
            {      
                foreach($romaneios as $romaneio)
                {
                    $romaneio =  $this->addRenavamRemoveTerceiro($romaneio,1);

                    $qrCode = "1?".$romaneio->CDEMPRESA.'?'.$romaneio->CDROTA.'?'.$romaneio->CDROMANEIO;
                    $romaneio->QRCODE = $qrCode;
                }

                $documentos["ROMANEIOS"] = $romaneios;
            }
            
            $documentos['QTDOCUMENTOS'] = count($documentos["MANIFESTOS"]) +  count($documentos["ROMANEIOS"]);

            return $documentos;

        }else{
            return [];
        }                                    

    }

    /***
    *   Este método recebe o documento como parametro, verifica quais veículos que estão no documento, caso for terceiro remove o veículo, 
    *   pois não é necessário fazer checklist de veículos de terceiros, caso for veículo da "CASA", adiciona também ao 
    *   documento o renavam do veiculo, que também é validado no Mobile. Também deleta os veículo que não tem valor, ou seja vem "null" do BD
    */
    private function addRenavamRemoveTerceiro($doc,$tpDocto)
    {   
        $this->CI->load->library('SoftranMobileLibrary');

        if($doc->NRPLACA)
        {
            $retorno = $this->CI->db->query("SELECT INVEICULO FROM SOFTRAN_MAGNA.GTCVEIDP WHERE NRPLACA = '$doc->NRPLACA' ")->result()[0];

            if($retorno->INVEICULO == 1)
            {
                unset($doc->NRPLACA);
            }else
            {
                $retorno = $this->CI->db->query("SELECT NRRENAVAN FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '$doc->NRPLACA' ")->result()[0];
                $doc->NRPLACA_RENAVAN = $retorno->NRRENAVAN;
                $doc->NRPLACA_HODOMETRO = $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACA)->ok ? 
                                          $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACA)->obj : 
                                          null;            
            }
        }else{
            unset($doc->NRPLACA);
        }

        if($doc->NRPLACACARRETA)
        {
            $retorno = $this->CI->db->query("SELECT INVEICULO FROM SOFTRAN_MAGNA.GTCVEIDP WHERE NRPLACA = '$doc->NRPLACACARRETA' ")->result()[0];    

            if($retorno->INVEICULO  == 1)
            {
                unset($doc->NRPLACACARRETA);
            }else
            {
                $retorno = $this->CI->db->query("SELECT NRRENAVAN FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '$doc->NRPLACACARRETA' ")->result()[0];    

                $doc->NRPLACACARRETA_RENAVAN = $retorno->NRRENAVAN;
                $doc->NRPLACACARRETA_HODOMETRO = $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACACARRETA)->ok ? 
                                                $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACACARRETA)->obj : 
                                                null;
            }
        }else{
            unset($doc->NRPLACACARRETA);
        } 

        //Tipos de Documentos 1 - Romaneio | 2 - Manifestos
        //Se for romaneio, tem o campo NRPLACAREBOQUE1 a mais do que no manifesto
        if($tpDocto == 1)
        {
            if($doc->NRPLACAREBOQUE1)
            {
                $retorno = $this->CI->db->query("SELECT INVEICULO FROM SOFTRAN_MAGNA.GTCVEIDP WHERE NRPLACA = '$doc->NRPLACAREBOQUE1' ")->result()[0];    

                if($retorno->INVEICULO  == 1)
                {
                    unset($doc->NRPLACAREBOQUE1);
                }else
                {
                    $retorno = $this->CI->db->query("SELECT NRRENAVAN FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '$doc->NRPLACAREBOQUE1' ")->result()[0];    

                    $doc->NRPLACAREBOQUE1_RENAVAN = $retorno->NRRENAVAN;
                    $doc->NRPLACAREBOQUE1_HODOMETRO = $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE1)->ok ? 
                                                      $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE1)->obj : 
                                                      null;
                }
            }else{
                unset($doc->NRPLACAREBOQUE1);
            } 
        }


        if($doc->NRPLACAREBOQUE2)
        {
            $retorno = $this->CI->db->query("SELECT INVEICULO FROM SOFTRAN_MAGNA.GTCVEIDP WHERE NRPLACA = '$doc->NRPLACAREBOQUE2' ")->result()[0];    

            if($retorno->INVEICULO  == 1)
            {
                unset($doc->NRPLACAREBOQUE2);
            }else
            {
                $retorno = $this->CI->db->query("SELECT NRRENAVAN FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '$doc->NRPLACAREBOQUE2' ")->result()[0];    

                $doc->NRPLACAREBOQUE2_RENAVAN = $retorno->NRRENAVAN;
                $doc->NRPLACAREBOQUE2_HODOMETRO  = $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE2)->ok ? 
                                                   $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE2)->obj : 
                                                   null;
            
            }
        }else{
            unset($doc->NRPLACAREBOQUE2);
        } 

        if($doc->NRPLACAREBOQUE3)
        {
            $retorno = $this->CI->db->query("SELECT INVEICULO FROM SOFTRAN_MAGNA.GTCVEIDP WHERE NRPLACA = '$doc->NRPLACAREBOQUE3' ")->result()[0];    

            if($retorno->INVEICULO  == 1)
            {
                unset($doc->NRPLACAREBOQUE3);
            }else
            {
                $retorno = $this->CI->db->query("SELECT NRRENAVAN FROM SOFTRAN_MAGNA.SISVEICU WHERE NRPLACA = '$doc->NRPLACAREBOQUE3' ")->result()[0];    

                $doc->NRPLACAREBOQUE3_RENAVAN = $retorno->NRRENAVAN;
                $doc->NRPLACAREBOQUE3_HODOMETRO  = $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE3)->ok ? 
                                                   $this->CI->softranmobilelibrary->retornaHodometroVeiculo($doc->NRPLACAREBOQUE3)->obj : 
                                                   null;
            }
                
        }else{
            unset($doc->NRPLACAREBOQUE3);
        }

        return $doc;
    }

    /* Esté método retorna se o registro atual é maior que as ultimas ocorrências, ou checklist que o motorista teve no Banco */
    private function maiorQueUltimaOcorrencia($cpfMotorista,$dtBatida)
    {
        
        $isLast = false;

        $this->CI->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'");        
        //Verifica na tabela se existiu/existe alguma ocorrência maior que o ultimo registro do BD
        $isLast = $this->CI->db->query("SELECT * FROM PORTARIA_CHECKLIST_OCORRENCIA WHERE CPFMOTORISTA = '$cpfMotorista' 
                                            AND TO_DATE(DTCRIACAO,'YYYYMMDDHH24MISS') > '$dtBatida' ")->num_rows() > 0 ? false : true;

        return $isLast;

    }



}