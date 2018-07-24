<?php

/**
 * Description of Geral_departamento
 *
 * @author Administrador
 */
class Geral_consulta extends MY_Controller
{

    public function __construct ()
    {
        $this->load->library('softran_oracle');
        $this->dados['modulo_nome'] = 'Geral > Consultas';
        $this->dados['modulo_menu'] = array(
            'Centros de custo' => 'listar_centro_custo',
            'Empresas' => 'listar_empresa',
            'Pessoas' => 'listar_pessoa',
        );
        $this->publico = array(
            'ver_pessoa_json',
        );
        parent::__construct();
    }

    function index ()
    {
        $this->redirect('geral_consulta/listar_centro_custo');
    }

    function listar_centro_custo ()
    {
        $this->dados['titulo'] = 'Centros de custo';
        $this->dados['lista'] = $this->softran_oracle->centros_custos();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->render('listar');
    }

    function listar_empresa ()
    {
        $this->dados['titulo'] = 'Empresas';
        $this->dados['lista'] = $this->softran_oracle->empresas();
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->render('listar');
    }

    function listar_pessoa ($pagina = 0)
    {
        $where = '';
        $get = $this->input->get();
        if (!empty($get['filtro']['like']))
        {
            foreach ($get['filtro']['like'] as $get_chave => $get_valor)
            {
                if ($get_valor)
                {
                    $valor = trim(strtoupper(remove_acento($get_valor)));
                    $where .= " AND $get_chave LIKE '%$valor%'";
                }
            }
        }
        $pagina = ($pagina > 0) ? $pagina : 0;
        $inicio = $pagina * 100;
        $fim = $inicio + 100;
        $this->dados['filtro'] = (!empty($get)) ? $get['filtro'] : array();
        $this->dados['anterior'] = ($pagina > 0) ? $pagina - 1 : 0;
        $this->dados['proximo'] = $pagina + 1;
        $this->dados['titulo'] = 'Pessoas';
        $this->dados['lista'] = $this->softran_oracle->pessoas($inicio, $fim, $where);
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->render('listar_pessoa');
    }

    function ver_pessoa_json ()
    {
        $retorno = array();
        $get = $this->input->get();
        if (!empty($get))
        {
            $where = "";
            foreach ($get as $get_id => $get_valor)
            {
                $where .= " AND $get_id = '$get_valor'";
            }
            $pessoas = $this->softran_oracle->pessoas(0, 1, $where);
            if (!empty($pessoas[0]))
            {
                foreach ($pessoas[0] as $pessoa_id => $pessoa_valor)
                {
                    $retorno[$pessoa_id] = trim($pessoa_valor);
                }
            }
        }
        echo json_encode($retorno);
    }

    function listar_motorista_documento ($celular = null)
    {
        $this->dados['titulo'] = 'Motoristas com documentos';
        $this->dados['lista'] = $this->softran_oracle->motoristas_com_documentos($celular);
        $this->dados['total'] = $this->dados['lista'] ? count($this->dados['lista']) : 0;
        $this->render('listar');
    }

}
