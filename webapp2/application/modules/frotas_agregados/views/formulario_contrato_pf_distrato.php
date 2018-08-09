<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Gerar distrato - Pessoa Física
    </div>
    <div class="panel-body">
        <?php
        echo form_open('', 'class="form-horizontal"');
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF *</label>
            <div class="col-sm-4">
                <?= form_input('CPF', set_value('CPF'), 'class="mascara_cpf cpfcnpj" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">   
                <?= form_input('NOME', set_value('NOME'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">RG *</label>
            <div class="col-sm-4">
                <?= form_input('RG', set_value('RG'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Órgão expedidor *</label>
            <div class="col-sm-4">   
                <?= form_input('RG_UF', set_value('RG_UF'), 'required'); ?>
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
            <label class="col-sm-2 control-label">Nacionalidade *</label>
            <div class="col-sm-4">   
                <?= form_input('NACIONALIDADE', set_value('NACIONALIDADE', 'brasileiro'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Idade *</label>
            <div class="col-sm-4">   
                <?= form_input('IDADE', set_value('IDADE', 'maior'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Estado civil *</label>
            <div class="col-sm-4">   
                <?= form_input('ESTADO_CIVIL', set_value('ESTADO_CIVIL', 'casado'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Data da contratação *</label>
            <div class="col-sm-4">   
                <?= form_input('DATA_CONTRATACAO', set_value('DATA_CONTRATACAO'), 'class="mascara_data" required'); ?>
            </div>
        </div>
        <div class="form-group ">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gerar</button>
            </div>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>
<script>
    $(function () {
        $('.cpfcnpj').blur(function () {
            var cpf_cnpj = $(this).val();
            $('[name="DATA_CONTRATACAO"]').val('');
            if (tratar_cpfcnpj(cpf_cnpj)) {
                $.getJSON('frotas_agregados/ver_contrato_data_json/' + cpf_cnpj, '', function (data) {
                    $('[name="DATA_CONTRATACAO"]').val(data);
                });
            }
        });
    });
</script>