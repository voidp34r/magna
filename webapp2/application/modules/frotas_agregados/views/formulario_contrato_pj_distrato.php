<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Gerar distrato - Pessoa Jurídica
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
            <label class="col-sm-2 control-label">CEP *</label>
            <div class="col-sm-4">
                <?= form_input('CEP', set_value('CEP'), 'class="mascara_cep" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Estado *</label>
            <div class="col-sm-4">   
                <?= form_input('ESTADO', set_value('ESTADO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Município *</label>
            <div class="col-sm-4">   
                <?= form_input('MUNICIPIO', set_value('MUNICIPIO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Bairro *</label>
            <div class="col-sm-4">   
                <?= form_input('BAIRRO', set_value('BAIRRO'), 'required'); ?>
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
            <label class="col-sm-2 control-label">Inscrição Estadual *</label>
            <div class="col-sm-4">
                <?= form_input('INSCRICAO_ESTADUAL', set_value('INSCRICAO_ESTADUAL'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Sócio-administrador *</label>
            <div class="col-sm-4">   
                <?= form_input('SOCIO_ADMINISTRADOR', set_value('SOCIO_ADMINISTRADOR'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF *</label>
            <div class="col-sm-4">
                <?= form_input('SOCIO_CPF', set_value('SOCIO_CPF'), 'class="mascara_cpf" required'); ?>
            </div>
            <label class="col-sm-2 control-label">RG *</label>
            <div class="col-sm-4">   
                <?= form_input('SOCIO_RG', set_value('SOCIO_RG'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Data da contratação *</label>
            <div class="col-sm-4">
                <?= form_input('DATA_CONTRATACAO', set_value('DATA_CONTRATACAO'), 'class="mascara_data" required'); ?>
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