<?php

function campo_filtro($filtro, $nome, $tipo, $extraClass = ""){
    $valor = (!empty($filtro[$tipo][$nome])) ? $filtro[$tipo][$nome] : '';
    
    return form_input('filtro[' . $tipo . '][' . $nome . ']', $valor, 'class="form-control input-sm '.$extraClass.'"');
}
