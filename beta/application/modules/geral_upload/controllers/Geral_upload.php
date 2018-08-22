<?php

/**
 * Description of Geral_upload
 *
 * @author Administrador
 */
class Geral_upload extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('geral_upload_model');

        $this->publico = array(
            'abrir',
        );

        parent::__construct();
    }

    function abrir ($id, $nome)
    {
        $where = array(
            'ID' => $id,
            'NOME' => urldecode($nome),
        );
        $upload = $this->geral_upload_model->get_all($where);
        if (!empty($upload))
        {
            $ext = pathinfo($nome, PATHINFO_EXTENSION);
            $caminho = 'uploads/' . $id . '.' . $ext;
            $mime = mime_content_type($caminho);
            header('Content-type: ' . $mime);
            readfile($caminho);
        }
        else
        {
            echo 'Arquivo não encontrado';
        }
    }

}
