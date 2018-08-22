<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Responder SMS
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Resposta</label>
            <div class="col-sm-10">    
                <p class="form-control-static">
                    <?= $item->RESPOSTA; ?>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Mensagem *</label>
            <div class="col-sm-10">                    
                <input type="text" name="MENSAGEM" class="form-control" value="" required>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>