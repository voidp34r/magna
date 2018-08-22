<div class="panel panel-default">
    <div class="panel-heading">
        Editar Grupo de Empresa
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"','POST'); ?>
        <div class="form-group">
            <div class="col-sm-4">
            	<label for="CDGRUPOCLIENTE">Código</label>
                <?= form_input('CDGRUPOCLIENTE', $cli->CDGRUPOCLIENTE,'readonly="readonly"'); ?>
            </div>
            <div class="col-sm-8">
            	<label for="DSGRUPOCLIENTE">Descrição</label>
                <?= form_input('DSGRUPOCLIENTE', $cli->DSGRUPOCLIENTE,'readonly="readonly"'); ?>
            </div>
            <div class="col-sm-4" style="margin-top: 10px">
                <label>Desconsiderar no Gráfico</label>
                <br>
                <?= form_checkbox('FGATIVO', 1, $cli->FGATIVO); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>