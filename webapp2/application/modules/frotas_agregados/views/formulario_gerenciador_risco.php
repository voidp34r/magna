<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?php
        $aberto = false;
        echo form_open_multipart('', 'class="form-horizontal"');
        if (set_value('ABERTO') || !set_value('ID')) {
            $aberto = true;
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Gerenciador de risco *</label>
                <div class="col-sm-4">   
                    <?= form_dropdown('GERAL_GERENCIADOR_RISCO_ID', $gerenciadores_risco, '', 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Motorista *</label>
                <div class="col-sm-4">
                    <?= form_dropdown('AGREGADO_MOTORISTA_ID', $motoristas, set_select('GERAL_MUNICIPIO_ID'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Data da consulta *</label>
                <div class="col-sm-4">   
                    <?= form_input('DATA_CONSULTA', data_oracle_para_web(set_value('DATA_CONSULTA')), 'class="mascara_data" required'); ?>
                </div>
                <label class="col-sm-2 control-label">Data de validade *</label>
                <div class="col-sm-4">   
                    <?= form_input('DATA_VALIDADE', data_oracle_para_web(set_value('DATA_VALIDADE')), 'class="mascara_data" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Protocolo *</label>
                <div class="col-sm-4">   
                    <?= form_input('PROTOCOLO', set_value('PROTOCOLO'), 'required'); ?>
                </div>
                <label class="col-sm-2 control-label">Operador *</label>
                <div class="col-sm-4">   
                    <?= form_input('OPERADOR', set_value('OPERADOR'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Observações</label>
                <div class="col-sm-4">   
                    <?= form_textarea('OBSERVACAO', set_value('OBSERVACAO')); ?>
                </div>
            </div>
            <?php
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