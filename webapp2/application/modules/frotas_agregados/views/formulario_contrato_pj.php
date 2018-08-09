<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Gerar contrato - Pessoa Jurídica
    </div>
    <div class="panel-body">
        <?php
        echo form_open('', 'class="form-horizontal"');
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">CNPJ *</label>
            <div class="col-sm-4">
                <?= form_input('CNPJ', set_value('CNPJ'), 'class="mascara_cnpj cpfcnpj" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">   
                <?= form_input('NOME', set_value('NOME'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Estado *</label>
            <div class="col-sm-4">   
                <?= form_input('ESTADO', set_value('ESTADO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Município *</label>
            <div class="col-sm-4">   
                <?= form_input('MUNICIPIO', set_value('MUNICIPIO'), 'required'); ?>
            </div>
        </div>      
        <div class="form-group">
            <label class="col-sm-2 control-label">Endereço *</label>
            <div class="col-sm-4">
                <?= form_input('ENDERECO', set_value('ENDERECO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Número *</label>
            <div class="col-sm-4">   
                <?= form_input('NUMERO', set_value('NUMERO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gerar</button>
            </div>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>