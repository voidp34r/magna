<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <div class="col-sm-4">
            	<label for="CNPJ">CNPJ *</label>
                <?= form_input('CNPJ', set_value('CNPJ'), 'class="mascara_cnpj" required id="RG"'); ?>
            </div>
            <div class="col-sm-8">
            	<label for="DSNOME">Nome *</label>
                <?= form_input('DSNOME', set_value('DSNOME'), 'required id="DSNOME"'); ?>
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