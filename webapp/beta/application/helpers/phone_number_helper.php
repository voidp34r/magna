<?php 

function removeMaskPhone($Telefone){
    return  str_replace(' ', '',str_replace('-', '', str_replace("(","",(str_replace(")","",$Telefone))))) ;
}

function buildCellObj($numero, $op, $principal = true){
    $tel = new StdClass();
    $tel->telefone = $numero;
    $tel->operadora = $op;
    $tel->principal  = $principal;
    return $tel;
}

function opValida($op){
    return $op != 0 ? true : false;
}

function nextelInvalido($telefone,$operadora,$existsOP){
    
    if(strlen($telefone) == 13 && $operadora != 99 && $existsOP ){
        return true;
    }
    return false;
}

function tamanhoInvalido($celular,$op){
    
    if(strlen($celular) < 13 && $op){
        return true;
    }

    return false;
}