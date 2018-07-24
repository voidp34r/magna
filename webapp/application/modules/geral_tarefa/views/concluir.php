<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Concluir
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Data/Hora *</label>
            <div class="col-sm-4">
                <?= form_input('ENTREGA_DATAHORA', data_oracle_para_web(set_value('ENTREGA_DATAHORA', date('YmdHis'))), 'class="mascara_datahora" required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Descrição *</label>
            <div class="col-sm-10">
                <?= form_textarea('ENTREGA_DESCRICAO', set_value('ENTREGA_DESCRICAO'), 'required'); ?>
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