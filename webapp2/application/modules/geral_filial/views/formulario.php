<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <?= form_input('NOME', set_value('NOME'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Endereço *</label>
            <div class="col-sm-4">
                <?= form_input('ENDERECO', set_value('ENDERECO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">CEP *</label>
            <div class="col-sm-4">
                <?= form_input('CEP', set_value('CEP'), 'class="mascara_cep" required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Telefone *</label>
            <div class="col-sm-4">
                <?= form_input('TELEFONE', set_value('TELEFONE'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">IP *</label>
            <div class="col-sm-4">
                <?= form_input('IP', set_value('IP'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Estado *</label>
            <div class="col-sm-4">   
                <?= form_dropdown('', array('') + array_estados(), '', 'class="estado" data-destino="GERAL_MUNICIPIO_ID" data-municipio="' . set_value('GERAL_MUNICIPIO_ID') . '" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Município *</label>
            <div class="col-sm-4">   
                <?= form_dropdown('GERAL_MUNICIPIO_ID', array(), set_select('GERAL_MUNICIPIO_ID'), 'required'); ?>
            </div>
        </div>
        <?php
        if (set_value('NOME')) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10 form-control-static">
                    <div class="checkbox">
                        <label>
                            <?= form_checkbox('ATIVO', 1, set_value('ATIVO')); ?>
                            Ativo  
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>