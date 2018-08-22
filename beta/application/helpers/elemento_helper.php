<?php

function botao_voltar (){
    $botao = '<a class="btn btn-default" href="javascript:history.back()">';
    $botao .= '<i class="fa fa-fw fa-arrow-left"></i>';
    $botao .= 'Voltar';
    $botao .= '</a>';
    
    return $botao;
}

function tag_label ($texto, $tipo = 'default'){
    return '<div class="label label-' . $tipo . '">' . $texto . '</div>';
}

function tag_ativo ($ativo){
    $texto = ($ativo) ? 'Sim' : 'Não';
    $tipo = ($ativo) ? 'success' : 'danger';
    
    return tag_label($texto, $tipo);
}
