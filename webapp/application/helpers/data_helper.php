<?php

/**
 * data_oracle_para_web
 * Converte a data em inteiro yyyymmddHHiiss no padrão dd/mm/yyyy HH:ii 
 * @param int $datahora
 * @param int $tamanho_horario
 * @return string
 */
function data_oracle_para_web ($datahora, $tamanho_horario = 5)
{
    $data = '';
    if ($datahora)
    {
        $ano = substr($datahora, 0, 4);
        $mes = substr($datahora, 4, 2);
        $dia = substr($datahora, 6, 2);
        $data = $dia . '/' . $mes . '/' . $ano;
        if (substr($datahora, 9))
        {
            $hora = substr($datahora, 8, 2);
            $minuto = substr($datahora, 10, 2);
            $segundo = substr($datahora, 12, 2);
            $horario = $hora . ':' . $minuto . ':' . $segundo;
            $data .= ' ' . substr($horario, 0, $tamanho_horario);
        }
    }
    return $data;
}

/** 
 * compareTwoDate
 * Compara diferença entre duas Data
 * @param string $DateOne  
 * @param string $DateTwo
 * @return string
 */
 function compareTwoDate($DateOne,$DateTwo){
   
    $Data1  = DateTime::createFromFormat('d/m/Y H:i:s', $DateOne);
    $Data2  = DateTime::createFromFormat('d/m/Y H:i:s', $DateTwo);
    $Result = $Data1->diff($Data2) ;
    $Date   = date_create($Result->format("%h:%i:%s"));
    return date_format($Date,"H:i:s"); 
 }


/*
 * data_web_para_oracle
 * Converte a data no padrão dd/mm/yyyy HH:ii em inteiro yyyymmddHHiiss
 * @param int $datahora
 * @return int
 */
function data_web_para_oracle ($datahora)
{
    if (!$datahora)
    {
        return NULL;
    }
    $data_hora = explode(' ', $datahora);
    $partes = explode('/', $data_hora[0]);
    $inverso = array_reverse($partes);
    $retorno = implode('', $inverso);
    if (!empty($data_hora[1]))
    {
        $horario = str_replace(':', '', $data_hora[1]);
        if (strlen($horario) == 4)
        {
            $horario .= '00';
        }
        $retorno .= $horario;
    }
    return $retorno;
}

/*
 * dias_entre_datas
 * Retorna o número de dias conforme as datas passadas por parâmetro
 * @param int $data_final
 * @param int $data_inicial
 * @return int
 */
function dias_entre_datas ($data_final, $data_inicial = null)
{
    if (!$data_final)
    {
        return null;
    }
    $time_inicial = timestamp($data_inicial);
    $time_final = timestamp($data_final);
    $diferenca = $time_final - $time_inicial;
    $dias = (int) floor($diferenca / (60 * 60 * 24));
    return $dias;
}

/*
 * timestamp
 * Retorna o timestamp de uma data formatada em dd/mm/yyyy
 * @param string $data
 * @return int
 */
function timestamp ($data = null)
{
    $partes = explode('/', ($data) ? $data : date('d/m/Y'));
    return mktime(0, 0, 0, $partes[1], $partes[0], $partes[2]);
}

/*
 * label_dias
 * Retorna um html label de acordo com os dias
 * @param int $dias
 * @param string $data
 * @return string
 */
function label_dias ($dias, $data = null)
{
    if ($dias == null)
    {
        return '';
    }
    if ($dias > 1)
    {
        $dias_texto = '+' . $dias . ' dias';
        $label = 'success';
    }
    if ($dias == 1)
    {
        $dias_texto = '1 dia';
        $label = 'success';
    }
    if ($dias == 0)
    {
        $dias_texto = 'Hoje';
        $label = 'info';
    }
    if ($dias < 0)
    {
        $dias_texto = $dias . ' dias';
        $label = 'danger';
    }
    $retorno = '<div class="label label-' . $label . '"';
    if ($data)
    {
        $retorno .= ' data-toggle="tooltip" title="' . $data . '"';
    }
    $retorno .= '>' . $dias_texto . '</div>';
    return $retorno;
}

/*
 * segundos_para_hora
 * Converte os segundos no formato de hora HH:ii
 * @param int $segundos
 * @return string
 */
function segundos_para_hora ($segundos)
{
    $horas = str_pad(floor($segundos / 3600), 2, '0', STR_PAD_LEFT);
    $minutos = str_pad(($segundos % 3600) / 60, 2, '0', STR_PAD_LEFT);
    return $horas . ':' . $minutos;
}

/*
 * hora_para_segundos
 * Converte uma hora no formato HH:ii em segundos
 * @param string $hora
 * @return int
 */
function hora_para_segundos ($hora)
{
    $parse = date_parse($hora);
    return ($parse['hour'] * 3600) + ($parse['minute'] * 60);
}

/**----------------------
 * ALISSON - UTILITARIOS
 *----------------------*/

/**
 * 
 */
/**
 * 
 * @param unknown $microtime
 * @return DateTime
 */
function getDateTimeMicro($microtime = null){
	return DateTime::createFromFormat('U.u', empty($microtime) ? microtime(true) : $microtime);
}







