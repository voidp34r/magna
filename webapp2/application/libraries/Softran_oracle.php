<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Softran_oracle
 *
 * @author Administrador
 */
class Softran_oracle
{

    var $CI;

    public function __construct ()
    {
        $this->CI = & get_instance();
    }

    function getEnderecoFilial($id){
    	$query = $this->_query("
    			select DSLOCAL, DSUF
  				  from SOFTRAN_MAGNA.SISCEP CEP
  				  left join SOFTRAN_MAGNA.SISEMPRE EMP on CEP.NRCEP = EMP.NRCEP
 				 where EMP.CDEMPRESA = " . $id , FALSE, __FUNCTION__, '_row');
    	return $query;
    }
    
    function centros_custos ($id = NULL)
    {
        $query = $this->_query("
            SELECT CDCENTROCUSTO ID, 
              DSCENTROCUSTO || ' (' || CDCENTROCUSTO || ')' NOME
            FROM SOFTRAN_MAGNA.GFACENCU
            ORDER BY DSCENTROCUSTO", TRUE, __FUNCTION__, '_dropdown');
        return $id ? $query[$id] : $query;
    }

    function pessoas ($inicio, $fim, $where)
    {
        $query = $this->_query("
            SELECT *
            FROM
              (SELECT ROWNUM RNUM,
                PESSOAS.*
               FROM
                (SELECT
                  CASE CLI.INCADASTRO
                    WHEN 0
                    THEN 'CLIENTE'
                    WHEN 1
                    THEN 'FORNECEDOR'
                    ELSE 'CLIENTE/FORNECEDOR'
                  END AS TIPO,
                  CASE CLI.INATIVO
                    WHEN 1
                    THEN 0
                    ELSE 1
                  END AS ATIVO,
                  LPAD(CLI.CDINSCRICAO, 14, '0') CPFCNPJ,
                  CLI.DSENTIDADE NOME,
                  CLI.DSENDERECO
                  || ' '
                  || CLI.DSNUMERO ENDERECO,
                  CLI.DSBAIRRO BAIRRO,
                  CEP.DSLOCAL CIDADE,
                  CEP.DSUF UF,
                  CLI.NRCEP CEP,
                  CLI.NRTELEFONE TELEFONE,
                  CLI.DSEMAIL EMAIL
                FROM SOFTRAN_MAGNA.SISCLI CLI
                LEFT JOIN SOFTRAN_MAGNA.SISCEP CEP
                ON CEP.NRCEP = CLI.NRCEP
                UNION ALL
                SELECT 'FUNCIONARIO' TIPO,
                  CASE FUN.FGDEMITIDO
                    WHEN 1
                    THEN 0
                    ELSE 1
                  END AS ATIVO,
                  LPAD(FUN.NRCPF, 14, '0') CPFCNPJ,
                  FUN.DSNOME NOME,
                  FUN.DSENDERECO ENDERECO,
                  FUN.DSBAIRRO BAIRRO,
                  CEP.DSLOCAL CIDADE,
                  CEP.DSUF UF,
                  FUN.NRCEP CEP,
                  FUN.DSFONE TELEFONE,
                  FUN.DSEMAIL EMAIL
                FROM SOFTRAN_MAGNA.SISFUN FUN
                LEFT JOIN SOFTRAN_MAGNA.SISCEP CEP
                ON CEP.NRCEP = FUN.NRCEP
                UNION ALL
                SELECT 'FRETEIRO' TIPO,
                  CASE FRE.ININATIVO
                    WHEN 1
                    THEN 0
                    ELSE 1
                  END AS ATIVO,
                  LPAD(FRE.NRCNPJCPF, 14, '0') CPFCNPJ,
                  FRE.DSNOME NOME,
                  FRE.DSENDERECO ENDERECO,
                  FRE.DSBAIRRO BAIRRO,
                  CEP.DSLOCAL CIDADE,
                  CEP.DSUF UF,
                  FRE.NRCEP CEP,
                  FRE.NRTELEFONE TELEFONE,
                  FRE.DSEMAIL EMAIL
                FROM SOFTRAN_MAGNA.GTCFRETE FRE
                JOIN SOFTRAN_MAGNA.GTCFUNDP FUNDP
                ON FUNDP.NRCPF = LPAD(FRE.NRCNPJCPF, 14, '0') 
                AND FUNDP.INFUNCIONARIO = 0
                LEFT JOIN SOFTRAN_MAGNA.SISCEP CEP
                ON CEP.NRCEP = FRE.NRCEP
                ) PESSOAS
              WHERE ROWNUM <= $fim $where
              )
            WHERE RNUM > $inicio", FALSE, __FUNCTION__, '_result');
        return $query;
    }

    function empresas ($id = NULL)
    {
        $query = $this->_query("
            SELECT CDEMPRESA ID,
              DSEMPRESA || ' (' || CDEMPRESA || ')' NOME
            FROM SOFTRAN_MAGNA.SISEMPRE
            WHERE INATIVA = 1
            ORDER BY DSEMPRESA", TRUE, __FUNCTION__, '_dropdown');
        return $id ? $query[$id] : $query;
    }

    function empresas_vinculadas_romaneio_entrega ($usuario)
    {
        $dsapelido = $this->_formata_usuario($usuario);
        $query = $this->_query("
            SELECT ENTREGA_EMPRESAS.CDEMPRESA 
            FROM SOFTRAN_MAGNA.EXPPEREM ENTREGA_EMPRESAS
            LEFT JOIN SOFTRAN_MAGNA.SISUSUFU VINCULACAO 
            ON VINCULACAO.CDPERFIL = ENTREGA_EMPRESAS.CDPERFIL
            WHERE ENTREGA_EMPRESAS.INTIPOROTINA = 11
            AND VINCULACAO.DSAPELIDO = '$dsapelido'", FALSE, __FUNCTION__, '_result');
        return $query;
    }

    function dados_funcionario ($usuario)
    {
        $dsapelido = $this->_formata_usuario($usuario);
        $query = $this->_query("
            SELECT FUNCIONARIO.CDFUNCIONARIO ID,
              FUNCIONARIO.CDCENTROCUSTO CENTRO_CUSTO_ID,
              FUNCIONARIO.CDEMPRESA EMPRESA_ID,
              FUNCIONARIO.DSNOME NOME,
              FUNCIONARIO.DSEMAIL
            FROM SOFTRAN_MAGNA.SISFUN FUNCIONARIO
            LEFT JOIN SOFTRAN_MAGNA.SISUSUFU VINCULACAO
            ON VINCULACAO.CDFUNCIONARIO = FUNCIONARIO.CDFUNCIONARIO
            WHERE VINCULACAO.DSAPELIDO  = '$dsapelido'", FALSE, __FUNCTION__, '_row');
        return $query;
    }

    function documentos_motorista ($tipo, $cpf)
    {
        $query = $this->_query("
            SELECT A.NRCPF,
              A.INFUNCIONARIO,
              A.DSNOME,
              (SELECT TO_CHAR(WM_CONCAT(B.NRMANIFESTO))
              FROM SOFTRAN_MAGNA.GTCMAN B
              WHERE B.CDMOTORISTA    = A.NRCPF
              AND (B.DTSAIDA        IS NOT NULL)
              AND (B.DTCHEGADA      IS NULL)
              AND (B.DTEMISSAO      >= SYSDATE - 5)
              AND (B.INIMPRESSO      = 1)
              AND NVL(B.INSITUACAO,0)=0
              ) AS MANIFESTOS,
              (SELECT TO_CHAR(WM_CONCAT(C.CDEMPRESA
                ||'-'
                ||C.CDROTA
                ||'-'
                ||C.CDROMANEIO))
              FROM SOFTRAN_MAGNA.CCEROMAN C
              WHERE C.NRCPFMOTORISTA = A.NRCPF
              AND (C.DTSAIDA        IS NOT NULL)
              AND (C.DTCHEGADA      IS NULL)
              AND (C.DTROMANEIO     >= SYSDATE - 5)
              AND (C.INIMPRESSO      = 1)
              AND NVL(C.INSITUACAO,0)=0
              ) AS ROMANEIOS,
              (SELECT TO_CHAR(WM_CONCAT(D.CDEMPRESA
                ||'-'
                ||D.NRCOLETA))
              FROM SOFTRAN_MAGNA.CCECOLET D
              WHERE A.NRCPF          = D.NRCPF
              AND D.INIMPRESSO       = 1
              AND (D.DTEMISSAO      >= SYSDATE-5)
              AND NVL(D.INSITUACAO,0)=0
              ) AS COLETAS
            FROM SOFTRAN_MAGNA.GTCFUNDP A
            WHERE ROWNUM < 10
            AND A.INFUNCIONARIO = '$tipo'
            AND A.NRCPF  = '$cpf'", FALSE, __FUNCTION__, '_row');
        return $query;
    }

    function _query ($sql, $session, $function, $retorno)
    {
        if ($session)
        {
            $session_query = $this->CI->session->userdata('query_' . $function);
            if (!empty($session_query[$function]))
            {
                $saida = $session_query[$function];
            }
            else
            {
                $query = $this->CI->db->query($sql);
                $saida = $this->$retorno($query);
                $this->CI->session->set_userdata('query_' . $function, $saida);
            }
        }
        else
        {
            $query = $this->CI->db->query($sql);
            $saida = $this->$retorno($query);
        }
        return $saida;
    }

    function _formata_usuario ($usuario)
    {
        $partes = explode('\\', $usuario);
        return strtoupper($partes[1]);
    }

    function _result ($query)
    {
        $result = array();
        if (!empty($query->result()))
        {
            $result = $query->result();
        }
        return $result;
    }

    function _dropdown ($query)
    {
        $dropdown = array();
        $result = $this->_result($query);
        foreach ($result as $item)
        {
            $dropdown[$item->ID] = $item->NOME;
        }
        return $dropdown;
    }

    function _row ($query)
    {
        $row = NULL;
        if (!empty($query->row()))
        {
            $row = $query->row();
        }
        return $row;
    }

}
