<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Editar SMS
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Número *</label>
            <div class="col-sm-10">
                <input type="text" name="CELULAR" class="form-control" value="<?= $item->CELULAR; ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Mensagem *</label>
            <div class="col-sm-10">                    
                <input type="text" name="MENSAGEM" class="form-control" value="<?= $item->MENSAGEM; ?>" required>
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