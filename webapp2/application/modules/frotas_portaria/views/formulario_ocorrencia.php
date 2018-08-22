
<div class="panel panel-default">
    <div class="panel-heading">
        Cód Checklist: <?= $id_checklist ?>  
    </div>
    <div class="panel-body">

        <!-- Alert de Informação para o usário-->
        <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4>
                <b>Atenção</b>
            </h4>
            <b>
                <p>
                Atenção! Ao validar uma ocorrência o sistema remove a pendência, porém se ao tentar sair novamente na portaria e o 
                problema persistir, será gerada outra ocorrência até que o problema seja efetivamente revolvido.
                </p>
            </b>
        </div>
        <div class="row">
            <div class="col-md-4">
                <h3>Data da Ocorrência</h3> 
                <label><?= data_oracle_para_web($checklist->DTCRIACAO)?></label> 
            </div>

            <div class="col-md-4">
                <h3>CPF Motorista</h3>
                <label><?= substr($checklist->CPFMOTORISTA,3); ?></label> 
            </div> 

            <div class="col-md-4">
                <h3>Motorista</h3>
                <label><?= $checklist->DSNOME ?></label>
            </div> 

            <div class="col-md-4">
                <h3>Usuário Portária</h3>
                <label><?= $checklist->NOMEPORT ?></label>
            </div> 

            <div class="col-md-8">
                <h3>Ocorrência</h3>
                <label><?= $checklist->DSOCORRENCIA ?></label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            <?= form_open('', 'class="form-horizontal"'); ?>    
                <h3>Parecer sobre a ocorrência</h3>
                <textarea name="COMENTARIO" required cols="50" placeholder="Digite aqui a causa da ocorrência e como foi resolvida.." maxlength="4000"></textarea>
                <br>
                <div style="margin-bottom: 10px">
                    <h3>Selecione abaixo a decisão que o sistema deve tomar</h3>
                    <input type="radio" name="FGRESOLVIDO" value="1"> Refazer checklist completo<br>
                    <input type="radio" name="FGRESOLVIDO" value="2"> Liberar esta etapa do checklist<br>
                </div>
                <button type="submit" class="btn btn-success btn-block">Concluir Ocorrência</button>
            <?= form_close(); ?>
            </div>
        </div>
        
    </div>
</div>


