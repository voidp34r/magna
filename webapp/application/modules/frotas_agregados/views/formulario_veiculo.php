<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?php
        $aberto = false;
        echo form_open_multipart('', 'class="form-horizontal"');
        if (set_value('ABERTO') == 1 || !set_value('ID')) {
            $aberto = true;
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Placa *</label>
                <div class="col-sm-4">   
                    <?= form_input('PLACA', set_value('PLACA'), 'class="mascara_placa_veiculo" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Nº do certificado *</label>
                <div class="col-sm-4">   
                    <?= form_input('CERTIFICADO', set_value('CERTIFICADO'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Nº do RENAVAM *</label>
                <div class="col-sm-4">   
                    <?= form_input('RENAVAM', set_value('RENAVAM'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Nº do chassis *</label>
                <div class="col-sm-4">   
                    <?= form_input('CHASSIS', set_value('CHASSIS'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Potência *</label>
                <div class="col-sm-4">   
                    <?= form_input('POTENCIA', set_value('POTENCIA'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Tipo do veículo *</label>
                <div class="col-sm-4">   
                    <?= form_input('TIPO_VEICULO', set_value('TIPO_VEICULO'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Modelo *</label>
                <div class="col-sm-4">   
                    <?= form_input('MODELO', set_value('MODELO'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Tara *</label>
                <div class="col-sm-4">   
                    <?= form_input('TARA', set_value('TARA'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Cor predominante *</label>
                <div class="col-sm-4">   
                    <?= form_input('COR_PREDOMINANTE', set_value('COR_PREDOMINANTE'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Ano do modelo *</label>
                <div class="col-sm-4">   
                    <?= form_input('ANO_MODELO', set_value('ANO_MODELO'), 'class="mascara_ano" required'); ?>
                </div>
                <label class="col-sm-2 control-label">Ano de fabricação *</label>
                <div class="col-sm-4">   
                    <?= form_input('ANO_FABRICACAO', set_value('ANO_FABRICACAO'), 'class="mascara_ano" required'); ?>
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
            <div class="form-group">
                <label class="col-sm-2 control-label">Combustível *</label>
                <div class="col-sm-4">   
                    <?= form_input('COMBUSTIVEL', set_value('COMBUSTIVEL'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Categoria *</label>
                <div class="col-sm-4">   
                    <?= form_input('CATEGORIA', set_value('CATEGORIA'), 'required'); ?>
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
                    <label class="col-sm-2 control-label"><?= $upload_nome; ?> *</label>
                    <div class="col-sm-6 form-control-static">   
                        <?= form_upload('UPLOAD_' . $upload_label, 'required'); ?>
                    </div>
                </div>
                <?php
            }
        }
        if ($aberto) {
            ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
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