<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of usuario_model
 *
 * @author Administrador
 */
class Geral_upload_model extends MY_Model
{

    public function __construct ()
    {
        parent::__construct();

        $this->table = 'GERAL_UPLOAD';
        $this->primary_key = 'ID';
    }

    function anexar ($tabela, $ID, $usuario_id, $upload_label, $config){
        $this->load->library('upload');

        if (!empty($_FILES['UPLOAD_' . $upload_label]['name']))
        {
            $upload_dados = array();
            $upload_dados['TABELA'] = $tabela;
            $upload_dados['TABELA_ID'] = $ID;
            $upload_dados['USUARIO_ID'] = $usuario_id;
            $upload_dados['LABEL'] = $upload_label;
            $upload_dados['NOME'] = apenas_alfanumericos($_FILES['UPLOAD_' . $upload_label]['name']);
            $upload_dados['DATAHORA'] = date('YmdHis');
            $upload_id = $this->insert($upload_dados);
            if ($upload_id)
            {
                $config['upload_path'] = 'uploads/';
                $config['file_name'] = $upload_id;
                $this->upload->initialize($config);
                $this->upload->do_upload('UPLOAD_' . $upload_label);
            }
        }
    }

    function criar ($tabela, $ID, $usuario_id, $label, $ext, $arquivo)
    {
        $this->excluir($tabela, $ID, $label);
        $upload_dados = array();
        $upload_dados['TABELA'] = $tabela;
        $upload_dados['TABELA_ID'] = $ID;
        $upload_dados['USUARIO_ID'] = $usuario_id;
        $upload_dados['LABEL'] = $label;
        $upload_dados['NOME'] = $label . '.' . $ext;
        $upload_dados['DATAHORA'] = date('YmdHis');
        $upload_id = $this->insert($upload_dados);
        if ($upload_id)
        {
            $diretorio = 'uploads/';
            $caminho = $diretorio . $upload_id . '.' . $ext;
            if (file_exists($caminho))
            {
                unlink($caminho);
            }
            $handle = fopen($caminho, "w");
            fwrite($handle, $arquivo);
            fclose($handle);
        }
    }

    function ler ($tabela, $ID, $label)
    {
        $where = array(
            'TABELA' => $tabela,
            'TABELA_ID' => $ID,
            'LABEL' => $label,
        );
        $get = $this->get($where);
        if (!empty($get))
        {
            $ext = pathinfo($get->NOME, PATHINFO_EXTENSION);
            $caminho = 'uploads/' . $get->ID . '.' . $ext;
            if (file_exists($caminho))
            {
                $handle = fopen($caminho, "r");
                $conteudo = fread($handle, filesize($caminho));
                fclose($handle);
                return $conteudo;
            }
        }
    }

    function excluir ($tabela, $ID, $label)
    {
        $where = array();
        $where['TABELA'] = $tabela;
        $where['TABELA_ID'] = $ID;
        $where['LABEL'] = $label;
        $this->where($where)->delete();
    }

}
