<?php


class Ti_noticia extends MY_Controller
{

    public function __construct ()
    {
        $this->load->model('sistema_noticias');
        $this->dados['modulo_nome'] = 'TI > Notícias';
        $this->dados['modulo_menu'] = array("Notícia" => "listar_noticia");
        parent::__construct();
    }
    
    function index()
    {
        $this->listar_noticia();
    }
    
    function listar_noticia($page = 1){
        $total = $this->sistema_noticias->count_rows();
        $lista = $this->sistema_noticias->order_by('DTNOTICIA','DESC')->paginate(10, $total, $page);
        $this->dados['lista'] = $lista;
        $this->dados['total'] = $total;
        $this->dados['paginacao'] = $this->sistema_noticias->all_pages;
        $this->render('listar_noticia');
    }
    
    private function formulario_noticia($id = null){
        
        $this->form_validation->set_rules('NOTICIA', 'Notícia', 'trim|required');
        $this->form_validation->set_rules('TITULO', 'Título', 'trim|required');
        if ($this->form_validation->run()){
            $noticia = array();
            $post = $this->input->post();
            
            //Workaround para setar em que tipo de device será mostrada a noticia
            if(isset($post['MOBILE']) || isset($post['WEBAPP'])){
                
                if(isset($post['MOBILE']) && isset($post['WEBAPP'])){
                    $Flmobile = 0;
                }else if (isset($post['MOBILE'])){
                    $Flmobile = 1;
                }else{
                    $Flmobile = 2;
                }
                
            }
            
            $noticia['DSTITULO']  = $post['TITULO'];
            $noticia['DSNOTICIA'] = $post['NOTICIA'];
            $noticia['FLMOBILE']  = !isset($Flmobile)? 0 : $Flmobile ;
            date_default_timezone_set("Brazil/East");
            $noticia['DTNOTICIA'] = date('YmdHis'); 
            $noticia['CDUSUARIO'] = $this->sessao['usuario_id'];
            
            if(!$id){
                $ret = $this->sistema_noticias->insert($noticia);
                $message = 'Notícia gravada com sucesso!';
            }else{
                $ret = $this->sistema_noticias->insert($noticia,$id);
                $message = 'Notícia editada com sucesso!';
            }
            
            if($ret){
                $this->redirect('ti_noticia/listar_noticia', 'sucesso',$message);
            }
            
        }
        $this->render('formulario_noticias');
    }
    
    function adicionar_noticia(){
        $this->formulario_noticia();
    }
    
    function editar_motorista($id){
        $this->dados['item'] = $this->sistema_noticias->get(array('ID' => $id));
        $this->formulario_noticia($id);
    }
    
    
    function excluir_noticia($id){
        $retorno = $this->sistema_noticias->delete(array('ID' => $id));
        if($retorno)
          $this->redirect('ti_noticia/listar_noticia', 'sucesso', 'Notícia deletada com sucesso!');
    }
    
}    