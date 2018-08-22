<?php

function parte_do_texto ($texto, $parte = 0, $primeira_maiuscula = true)
{
    $partes = explode(' ', $texto);
    $retorno = (!empty($partes[$parte])) ? $partes[$parte] : $partes[0];
    if ($primeira_maiuscula)
    {
        $retorno = ucfirst(strtolower($retorno));
    }
    return $retorno;
}

function apenas_alfanumericos ($string)
{
    return preg_replace("/[^a-zA-Z0-9.\s]/", "", $string);
}

function existe_ou_vazio ($array, $chave)
{
    return (!empty($array[$chave])) ? $array[$chave] : '';
}

function link_amigavel ($link)
{
    $link = str_replace('_', ' ', $link);
    $link = str_replace('/', ' > ', $link);
    $link = strtoupper($link);
    return $link;
}

function converte_caracteres_word ($text)
{
    $search = array(
        "\xC2\xAB", // « (U+00AB) in UTF-8
        "\xC2\xBB", // » (U+00BB) in UTF-8
        "\xE2\x80\x98", // ‘ (U+2018) in UTF-8
        "\xE2\x80\x99", // ’ (U+2019) in UTF-8
        "\xE2\x80\x9A", // ‚ (U+201A) in UTF-8
        "\xE2\x80\x9B", // ‛ (U+201B) in UTF-8
        "\xE2\x80\x9C", // “ (U+201C) in UTF-8
        "\xE2\x80\x9D", // ” (U+201D) in UTF-8
        "\xE2\x80\x9E", // „ (U+201E) in UTF-8
        "\xE2\x80\x9F", // ‟ (U+201F) in UTF-8
        "\xE2\x80\xB9", // ‹ (U+2039) in UTF-8
        "\xE2\x80\xBA", // › (U+203A) in UTF-8
        "\xE2\x80\x93", // – (U+2013) in UTF-8
        "\xE2\x80\x94", // — (U+2014) in UTF-8
        "\xE2\x80\xA6"  // … (U+2026) in UTF-8
    );
    $replacements = array(
        "<<",
        ">>",
        "'",
        "'",
        "'",
        "'",
        '"',
        '"',
        '"',
        '"',
        "<",
        ">",
        "-",
        "-",
        "..."
    );
    return str_replace($search, $replacements, $text);
}

function remove_acento ($str)
{
    $table = array(
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z',
        'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
        'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
        'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
        'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
    );
    return strtr($str, $table);
}

function formata_cpf ($str)
{
    $cpf = substr($str, 0, 3) . '.';
    $cpf .= substr($str, 3, 3) . '.';
    $cpf .= substr($str, 6, 3) . '-';
    $cpf .= substr($str, 9, 2);
    return $cpf;
}

function formata_cpf_cnpj($str){
	if (strlen($str) == 11){
		$r  = substr($str, 0, 3) . '.';
		$r .= substr($str, 3, 3) . '.';
		$r .= substr($str, 6, 3) . '-';
		$r .= substr($str, 9, 2);
	} else if (strlen($str) == 14){ //cnpj
		$r  = substr($str, 0, 2) . '.';
		$r .= substr($str, 2, 3) . '.';
		$r .= substr($str, 5, 3) . '/';
		$r .= substr($str, 8, 4) . '-';
		$r .= substr($str, 12, 2);
	}
	
	return $r;
}

/**
 * Captaliza primeira letra de cada palavra
 * 
 * @param string $text
 * @return string
 */
function upperFirsts($text = ""){
	return ucwords(mb_strtolower($text));
}

function formata_telefone($str){
	$tel  = "(";
	$tel  = substr($str, 0, 3) . '.';
	$tel .= substr($str, 3, 3) . '.';
	$tel .= substr($str, 6, 3) . '-';
	$tel .= substr($str, 9, 2);
	return $cpf;
}

