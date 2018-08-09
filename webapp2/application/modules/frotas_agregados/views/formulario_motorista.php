<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
        &nbsp
        &nbsp
        <small style="color: black">
        	<input type="checkbox" 
        	   	   id="motorista_proprietario"
                   data-cadastro="<?= $id; ?>" 
                   title="<?= strlen($proprietario->CPF_CNPJ) > 14 ? 'O motorista precisa ser uma pessoa física.' : '' ?>"
                   <?= strlen($proprietario->CPF_CNPJ) > 14 ? 'disabled' : ''?>>
       			Motorista é o proprietário
       		</input>
       	</small>
    </div>
    <div class="panel-body">
        <?php
        $aberto = false;
        echo form_open_multipart('', 'class="form-horizontal"');
        if (set_value('ABERTO') == 1 || !set_value('ID')) {
            $aberto = true;
            ?>
            <div class="form-group">
                <div class="col-sm-3"> 
                	<label for="CPF">CPF *</label>
                    <?= form_input('CPF', set_value('CPF'), 'class="mascara_cpf cpfcnpj" required'); ?>
                </div>
            	<div class="col-sm-6">  
                	<label for="NOME">Nome *</label>
                    <?= form_input('NOME', set_value('NOME'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="RG">RG *</label>
                    <?= form_input('RG', set_value('RG'), 'class="numbersOnly" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">  
                	<label for="estado_principal">Estado *</label> 
                    <?= form_dropdown('', array('') + array_estados(), '', 'class="estado" id="estado_principal" data-destino="GERAL_MUNICIPIO_ID" data-municipio="' . set_value('GERAL_MUNICIPIO_ID') . '" required'); ?>
                </div>
                <div class="col-sm-5">
                	<label for="municipio_princial">Município *</label>
                    <?= form_dropdown('GERAL_MUNICIPIO_ID', array(), set_select('GERAL_MUNICIPIO_ID'), 'id="municipio_princial" required'); ?>
                </div>
                <div class="col-sm-4">
					<label for="BAIRRO">Bairro *</label>
                    <?= form_input('BAIRRO', set_value('BAIRRO'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                	<label for="ENDERECO">Endereço *</label>
                    <?= form_input('ENDERECO', set_value('ENDERECO'), 'required'); ?>
                </div>
                <div class="col-sm-6">
                	<label for="NUMERO">Nº/Complemento *</label>
                    <?= form_input('NUMERO', set_value('NUMERO'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4">
                	<label for="EMAIL">E-mail</label>
                    <?= form_input('EMAIL', set_value('EMAIL')); ?>
                </div>
                <div class="col-sm-4">
                	<label for="TELEFONE">Telefone *</label> 
                    <?= form_input('TELEFONE', set_value('TELEFONE'), 'class="numbersOnly" required'); ?>
                </div>
                <div class="col-sm-4">
                	<label for="CELULAR">Celular *</label>
                    <?= form_input('CELULAR', set_value('CELULAR'), 'class="numbersOnly" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="DATA_NASCIMENTO">Data de nascimento *</label>
                    <?= form_input('DATA_NASCIMENTO', set_value('ID') ? data_oracle_para_web(set_value('DATA_NASCIMENTO')) : set_value('DATA_NASCIMENTO'), 'class="mascara_data" required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="PIS">PIS *</label>
                    <?= form_input('PIS', set_value('PIS'), 'class="numbersOnly" required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="ANTT">ANTT</label>
                    <?= form_input('ANTT', set_value('ANTT')); ?>
                </div>
                <div class="col-sm-3">
                	<label for="ANTT_VALIDADE">Validade ANTT</label>  
                    <?= form_input('ANTT_VALIDADE', set_value('ID') ? data_oracle_para_web(set_value('ANTT_VALIDADE')) : set_value('ANTT_VALIDADE'), 'class="mascara_data"'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                	<label for="FILIACAO_PAI">Nome do pai</label>
                    <?= form_input('FILIACAO_PAI', set_value('FILIACAO_PAI')); ?>
                </div>
                <div class="col-sm-6">
                	<label for="FILIACAO_MAE">Nome da mãe</label>
                    <?= form_input('FILIACAO_MAE', set_value('FILIACAO_MAE')); ?>
                </div>
            </div>
            <hr>
            <h4 class="col-sm-offset-0">CNH</h4>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="CNH_CATEGORIA">Categoria *</label>
                    <?= form_input('CNH_CATEGORIA', set_value('CNH_CATEGORIA'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="CNH_NUMERO">Número *</label>
                    <?= form_input('CNH_NUMERO', set_value('CNH_NUMERO'), 'class="numbersOnly" required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="CNH_PRONTUARIO">Prontuário *</label>
                    <?= form_input('CNH_PRONTUARIO', set_value('CNH_PRONTUARIO'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="CNH_EMISSAO">Emissão *</label> 
                    <?= form_input('CNH_EMISSAO', set_value('ID') ? data_oracle_para_web(set_value('CNH_EMISSAO')) : set_value('CNH_EMISSAO'), 'class="mascara_data" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                	<label for="CNH_1HABILITACAO">Data 1ª hab. *</label>
                    <?= form_input('CNH_1HABILITACAO', set_value('ID') ? data_oracle_para_web(set_value('CNH_1HABILITACAO')) : set_value('CNH_1HABILITACAO'), 'class="mascara_data" required'); ?>
                </div>
                <div class="col-sm-2">
                	<label for="CNH_VENCIMENTO">Vencimento *</label>
                    <?= form_input('CNH_VENCIMENTO', set_value('ID') ? data_oracle_para_web(set_value('CNH_VENCIMENTO')) : set_value('CNH_VENCIMENTO'), 'class="mascara_data" required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="estado_cnh">Estado *</label>
                    <?= form_dropdown('', array('') + array_estados(), '', 'id="estado_cnh" class="estado" data-destino="CNH_GERAL_MUNICIPIO_ID" data-municipio="' . set_value('CNH_GERAL_MUNICIPIO_ID') . '" required'); ?>
                </div>
                <div class="col-sm-5">
                	<label for="CNH_GERAL_MUNICIPIO_ID">Município *</label>
                    <?= form_dropdown('CNH_GERAL_MUNICIPIO_ID', array(), set_select('CNH_GERAL_MUNICIPIO_ID'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="CNH_ORGAO">Orgão *</label>
                    <?= form_input('CNH_ORGAO', set_value('CNH_ORGAO'), 'required'); ?>
                </div>
            </div>
            <hr>
            <?php
        }
        foreach ($upload_campos as $upload_label => $upload_nome) {
            if (set_value('ABERTO_' . $upload_label) == 1 || !set_value('ID')) {
                $aberto = true;
                ?>
                <div class="form-group">
                    <label class="col-sm-5 control-label"><?= $upload_nome; ?> *</label>
                    <div class="col-sm-6 form-control-static">   
                        <?= form_upload('UPLOAD_' . $upload_label, 'required'); ?>
                    </div>
                </div>    
                <?php
            }
        }
        if (strlen($proprietario->CPF_CNPJ) == 14) {
            ?>            
            <div class="form-group" id="upload_nomeacao">
                <label class="col-sm-5 control-label">Termo de nomeação *</label>
                <div class="col-sm-6 form-control-static">   
                    <?= form_upload('UPLOAD_NOMEACAO', 'required'); ?>
                </div>
            </div>  
            <?php
        }
        if ($aberto) {
            ?>
            <div class="form-group">
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success">Gravar</button>
                </div>
            </div>
            <?php
        } else {
            echo 'Nenhuma informação em aberto';
        }
        echo form_close();
        ?>
    </div>
</div>
<script>
    $(function () {
        $('#motorista_proprietario').change(function () {
            var checado = $(this).is(':checked');
            var cadastro_id = $(this).data('cadastro');
            if (checado) {
                $.getJSON('frotas_agregados/ver_solicitacao_json/' + cadastro_id, '', function (data) {
                    var municipio_id;
                    $.each(data['proprietario'], function (key, value) {
                        if (key == 'CPF_CNPJ') {
                            value = (value.length == 14) ? value : '';
                            $('[name=CPF]').val(value);
                            $('[name=CPF]').attr('readonly', 'readonly');
                        }
                        if (key == 'GERAL_MUNICIPIO_ID') {
                            municipio_id = value;
                            $('#estado_principal').attr('readonly', 'readonly');
                        }
                        if (value) {
                            $('[name=' + key + ']').val(value);
                            $('[name=' + key + ']').attr('readonly', 'readonly');
                        }
                    });
                    $('#estado_principal').attr('data-municipio', municipio_id);
                    atualizar_estado($('#estado_principal'));
                });
                $('#upload_nomeacao').hide();
            } else {
                $('input, select, textarea').each(function (index) {
                    $(this).val('');
                    $(this).removeAttr('readonly');
                });
                atualizar_estado($('#estado_principal'));
                $('#upload_nomeacao').show();
            }
        });
    });
</script>