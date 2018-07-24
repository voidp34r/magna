<?php

/**
 * Description of inicio_controller
 *
 * @author Administrador
 */
class Principal extends MY_Controller
{

    public function __construct ()
    {
        parent::__construct();
    }

    function index ()
    {
        if (!empty($this->sessao['logado']))
        {
            
            $this->privado();
        }
        else
        {
            $this->redirect('autenticacao/login');
        }
    }

    function privado ()
    {
        $this->load->model('ti_noticia/sistema_noticias');
        $where = "FLMOBILE = 0 OR FLMOBILE = 2";
        $this->dados['noticias'] = $this->sistema_noticias
                                        ->where($where, NULL, NULL,FALSE, FALSE,TRUE)
                                        ->order_by(array('DTNOTICIA' => 'DESC'))
                                        ->get_all(); 
         
        $this->render('privado');
    }

}
