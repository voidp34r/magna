<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Adicionar usuário
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Usuário *</label>
            <div class="col-sm-10">
                <?= form_input('USUARIO', set_value('USUARIO'), 'class="form-control" required'); ?>
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