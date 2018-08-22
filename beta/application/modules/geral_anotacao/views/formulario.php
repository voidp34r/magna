<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Título *</label>
            <div class="col-sm-4">
                <?= form_input('TITULO', set_value('TITULO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Descrição *</label>
            <div class="col-sm-10">
                <?= form_textarea('DESCRICAO', set_value('DESCRICAO'), 'required'); ?>
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