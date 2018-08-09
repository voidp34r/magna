<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Gerar contrato - Pessoa Física com nomeação
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
                <?= form_input('ESTADO_CIVIL', set_value('ESTADO_CIVIL', 'solteiro'), 'required'); ?>
            </div>
        </div> 
        <hr>
        <h4 class="col-sm-offset-2">Nomeado</h4>
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF *</label>
            <div class="col-sm-4">
                <?= form_input('NOMEADO_CPF', set_value('NOMEADO_CPF'), 'class="mascara_cpf" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_NOME', set_value('NOMEADO_NOME'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Estado civil *</label>
            <div class="col-sm-4">
                <?= form_input('NOMEADO_ESTADO_CIVIL', set_value('NOMEADO_ESTADO_CIVIL'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Profissão *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_PROFISSAO', set_value('NOMEADO_PROFISSAO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">RG *</label>
            <div class="col-sm-4">
                <?= form_input('NOMEADO_RG', set_value('NOMEADO_RG'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Órgão expedidor *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_RG_UF', set_value('NOMEADO_RG_UF'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Endereço *</label>
            <div class="col-sm-4">
                <?= form_input('NOMEADO_ENDERECO', set_value('NOMEADO_ENDERECO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Número *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_NUMERO', set_value('NOMEADO_NUMERO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Estado *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_ESTADO', set_value('NOMEADO_ESTADO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Município *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_MUNICIPIO', set_value('NOMEADO_MUNICIPIO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CNH *</label>
            <div class="col-sm-4">   
                <?= form_input('NOMEADO_CNH', set_value('NOMEADO_CNH'), 'required'); ?>
            </div>
        </div>
        <hr>
        <h4 class="col-sm-offset-2">Veículo</h4>
        <div class="form-group">
            <label class="col-sm-2 control-label">Marca *</label>
            <div class="col-sm-4">
                <?= form_input('VEICULO_MARCA', set_value('VEICULO_MARCA'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Modelo *</label>
            <div class="col-sm-4">   
                <?= form_input('VEICULO_MODELO', set_value('VEICULO_MODELO'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Placa *</label>
            <div class="col-sm-4">
                <?= form_input('VEICULO_PLACA', set_value('VEICULO_PLACA'), 'class="mascara_placa" required'); ?>
            </div>
            <label class="col-sm-2 control-label">Chassi *</label>
            <div class="col-sm-4">   
                <?= form_input('VEICULO_CHASSI', set_value('VEICULO_CHASSI'), 'required'); ?>
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