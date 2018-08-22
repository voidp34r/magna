<?php


class Portaria_checklist_ocorrencia_model extends MY_Model {

    public function __construct() 
    {
        parent::__construct();     
        $this->table = 'PORTARIA_CHECKLIST_OCORRENCIA';
        $this->primary_key = 'ID';
    }

    //Retorna ID já que esta tabela não tem sequencia por ter nome muito grande - WORKAROUND
    private function getLastId()
    {
       return  1 + intVal($this->db->query("SELECT MAX(ID) AS ID FROM PORTARIA_CHECKLIST_OCORRENCIA")->result()[0]->ID);
    }

    //Insere já que o framework procura a sequencia e esta tabela não tem sequencia por exceder o tamanho de caracteres
    public function insertOcorr($data)
    {
         $ID = $this->getLastId();
         $CPFMOTORISTA = $data["CPFMOTORISTA"];
         $DSOCORRENCIA = $data["DSOCORRENCIA"];
         $DTCRIACAO = $data["DTCRIACAO"];
         $USUARIOPORTARIA = $data["USUARIOPORTARIA"];
         $CDEMPRESA = $data["CDEMPRESA"];
         $STEP_CHECKLIST = $data["STEP_CHECKLIST"];
         $FGRESOLVIDO = 0;

        return $this->db->query("INSERT INTO PORTARIA_CHECKLIST_OCORRENCIA (ID,CPFMOTORISTA,DSOCORRENCIA,DTCRIACAO,USUARIOPORTARIA,CDEMPRESA,FGRESOLVIDO,STEP_CHECKLIST)
                                 VALUES ($ID , '$CPFMOTORISTA' , '$DSOCORRENCIA' , $DTCRIACAO , $USUARIOPORTARIA , $CDEMPRESA,$FGRESOLVIDO,$STEP_CHECKLIST) ");

        
    }
    
}