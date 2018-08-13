<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class MobileLib {

    var $CI;
     
    const DIRETORIOARQUIVOS = '/var/www/webapp/uploads/checklist'; 

    public function __construct ()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('My_PHPMailer');
        $this->CI->load->library('SoftranMobileLibrary');
        $this->CI->load->model('frotas_portaria/Portaria_checklist_model');
        $this->CI->load->model('frotas_portaria/Portaria_checklist_foto_model');
        $this->CI->load->model('frotas_portaria/Portaria_checklist_documento_model');
    }   

    public function insertNewChecklist($checklistParam,$documentos)
    {     
        $statusOk = true;
        $objeto =  json_decode(json_encode($checklistParam));
        date_default_timezone_set("Brazil/East");
        
        $checkList = new stdClass();
        $checkList->TPACAO = $objeto->FGACAO;
        $checkList->DSOBSERVACAO =  isset($objeto->comentario) ? $objeto->comentario : '';
        $checkList->USUARIO_ID = $objeto->userID;
        $checkList->INQRCODE = $objeto->leuQrCode == 'true' ? 1 : 0;
        $checkList->DTINCLUSAO = date('YmdHis');     
        
        //Ação 1 - Envia Email com problemas no checklist 2- Atualiza Hodometro do Veiculo             
        switch (intval($objeto->FGACAO))
        {
            
            case  1 : 
            {
                $statusOk = $this->enviarEmail($objeto->EMAIL->nrPlaca,
                                             $objeto->EMAIL->cdVeiculo,
                                             $objeto->EMAIL->kmAtual,
                                             $objeto->EMAIL->kmInformado,
                                             $objeto->EMAIL->dtAtualizacao,
                                             $objeto->EMAIL->usuario);
                
                $checkList->CDVEICULO = $objeto->EMAIL->cdVeiculo;
                break;
            } 
        
            case  2 : 
            {
                $statusOk = $this->atualizaHodometroVeiculo($objeto->HODOMETRO->cdVeiculo, 
                                                          $objeto->HODOMETRO->kmAtual, 
                                                          $objeto->HODOMETRO->novoKm, 
                                                          $objeto->HODOMETRO->cdSequencia, 
                                                          $objeto->HODOMETRO->usuario);
                
                //Se Atualizou o hodometro, passa esse valor pro checklist
                if($statusOk)
                {
                    $checkList->KMATUAL = $objeto->HODOMETRO->novoKm;
                    $checkList->KMANTERIOR = $objeto->HODOMETRO->kmAtual;
                }
                
                $checkList->CDVEICULO = $objeto->HODOMETRO->cdVeiculo;
                break;
            }
            
        }
        
        $checkList->ID = $this->CI->portaria_checklist_model->insert($checkList);
        
        $statusOk = $checkList->ID ? true : false;
        
        $diretorio = self::DIRETORIOARQUIVOS.date('d-m-Y');
        
        //Caso Tenha Foto Salva Elas
        if(isset($objeto->fotos))
        {
            
            //Verifica se não existe o diretório, cria ele
            if(!file_exists($diretorio)){
                mkdir($diretorio);
            }
            
            //Percorre o Array de Imagens, e salva no diretório
            foreach ($objeto->fotos as $id => $imagem) 
            {
                
                //Pega as Informações da Imagem para inserir no BD
                $imagemPost = new stdClass();
                $imagemPost->CHECKLIST_ID = $checkList->ID;
                $imagemPost->FOTO_CONFIG_ID = $imagem->ID; 
                $imagemPost->FGVISIVEL = 1;

                //Seta o nome do Arquivo com as Informações da Foto
                $arquivoNome = "{$checkList->ID}-{$checkList->CDVEICULO}-{$imagem->DSDESCRICAO}.jpg";
                $caminhoImagem = "{$diretorio}/{$arquivoNome}";
                
                //Seta o Caminho para inserir no Bnaco
                $imagemPost->CAMINHO = $caminhoImagem;
                
                //Salva a Fo
                file_put_contents($caminhoImagem,base64_decode($imagem->IMG));
                
                $statusOk = $this->CI->portaria_checklist_foto_model->insert($imagemPost);
            }              
        }

        if($checkList->ID)
        {   
            if(isset($documentos->ROMANEIOS))
            {
                foreach($documentos->ROMANEIOS as $romaneio)
                {                    
                    $arrInsert = ["ID_CHECKLIST" =>  $checkList->ID,
                                  "CDROTA" => $romaneio->CDROTA,
                                  "CDEMPRESA" => $romaneio->CDEMPRESA,
                                  "CDROMANEIO" => $romaneio->CDROMANEIO,
                                  "TPDOCUMENTO" => 1];
                    $statusRomaneio = $this->atualizaSaidaChegadaRomaneio($romaneio->CDROTA,
                                                                          $romaneio->CDROMANEIO,
                                                                          $romaneio->CDEMPRESA);
                    $statusOk = $this->CI->Portaria_checklist_documento_model->insert($arrInsert);
                    
                } 
            }

            if(isset($documentos->MANIFESTOS))
            {
                foreach($documentos->MANIFESTOS as $manifesto)
                {       
                    $arrInsert = ["ID_CHECKLIST" =>  $checkList->ID,
                                  "TPDOCUMENTO" => 2,
                                  "NRMANIFESTO"=> $manifesto->NRMANIFESTO];
    
                    $statusOk = $this->CI->Portaria_checklist_documento_model->insert($arrInsert);
                }
            }
            
        }    
        return $statusRomaneio ? $statusOk : false;

    }

    /**
        *  Atualiza Hodometro do veiculo, já passando a sequencia 
        *  o novo KM, e o Km Atual e o veiculo a ser atualizado
        *  @param int cdVeiculo 
        *  @param int kmAtual
        *  @param int novoKm
        *  @param int cdSequencia
        * 
    */
    private function atualizaHodometroVeiculo($cdVeiculo,$kmAtual,$novoKm,$cdSequencia,$usuario)
    {   
        return $this->CI->softranmobilelibrary->atualizaHodometro($cdVeiculo,$kmAtual,$novoKm,$cdSequencia,$usuario) ? true : false;   
    }

    /**
     * Atualiza a data e hora da saida ou chegada do romaneio apos o checklist 
     * @param cdRota Codigo da rota
     * @param cdRomaneio Codigo do romaneio
     * @param empresa Codigo da empresa
     */
    private function atualizaSaidaChegadaRomaneio($cdRota,$cdRomaneio,$empresa){
        return $this->CI->softranmobilelibrary->atualizaSaidaChegada($cdRota,$cdRomaneio,$empresa) ? true : false;   
    }
    
    private function enviarEmail($nrPlaca,$cdVeiculo,$kmAtual,$kmInformado,$dtAtualizacao,$usuario)
    {

        $conteudo = "<label style='font-size : 18px;'>
                        Falha ao tentar atualizar o hodômetro do veículo <br><br> 
                        Placa: <b>{$nrPlaca}</b><br> 
                        Código do Veículo: <b>{$cdVeiculo}</b><br>
                        KM Atual: <b>{$kmAtual}</b><br>    
                        KM informado pelo usuário: <b>{$kmInformado}</b><br>
                        Data da última atualização : <b>{$dtAtualizacao}</b><br>    
                        Usuário : <b>{$usuario}</b><br><br> 
                        <small><b>Alerta gerado via o Aplicativo da Transmagna, não responder.</b><small>    
                    </label>";
        
        $assunto = "Falha ao tentar atualizar o hodômetro do veículo";                
        
        return $this->CI->my_phpmailer->send_mail('portaria_mobile@transmagna.com.br',$conteudo,$assunto) ? true : false;
        
        
    }
}    